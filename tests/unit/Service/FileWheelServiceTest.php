<?php

declare(strict_types=1);

namespace Service;

use OCA\ChaoticFileCleaner\Service\FileWheelService;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FileWheelServiceTest extends TestCase {
	private IRootFolder&MockObject $rootFolder;
	private Folder&MockObject $userFolder;
	private FileWheelService $service;

	protected function setUp(): void {
		parent::setUp();

		$this->rootFolder = $this->createMock(IRootFolder::class);
		$this->userFolder = $this->createMock(Folder::class);
		$this->rootFolder->method('getUserFolder')->with('alice')->willReturn($this->userFolder);

		$this->userFolder->method('getRelativePath')
			->willReturnCallback(static fn (string $path): string => ltrim(str_replace('/alice/files', '', $path), '/'));

		$this->service = new FileWheelService($this->rootFolder);
	}

	private function mockFile(int $id, string $name, bool $deletable = true): File&MockObject {
		$file = $this->createMock(File::class);
		$file->method('getId')->willReturn($id);
		$file->method('getName')->willReturn($name);
		$file->method('getPath')->willReturn('/alice/files/' . $name);
		$file->method('getSize')->willReturn(1024);
		$file->method('isDeletable')->willReturn($deletable);

		return $file;
	}

	public function testItCollectsFilesFromSubfolders(): void {
		$nested = $this->createMock(Folder::class);
		$nested->method('getDirectoryListing')->willReturn([$this->mockFile(2, 'nested.txt')]);
		$this->userFolder->method('getDirectoryListing')
			->willReturn([$this->mockFile(1, 'top.txt'), $nested]);

		$result = $this->service->pickCandidates('alice', 10);

		$this->assertSame(2, $result['total']);
		$this->assertEqualsCanonicalizing([1, 2], array_column($result['files'], 'id'));
	}

	public function testItSkipsFilesThatMayNotBeDeleted(): void {
		$this->userFolder->method('getDirectoryListing')->willReturn([
			$this->mockFile(1, 'yours.txt'),
			$this->mockFile(2, 'readonly.txt', false),
		]);

		$result = $this->service->pickCandidates('alice', 10);

		$this->assertSame(1, $result['total']);
		$this->assertSame('yours.txt', $result['files'][0]['name']);
	}

	public function testItReportsTheTotalButReturnsAtMostTheLimit(): void {
		$this->userFolder->method('getDirectoryListing')->willReturn([
			$this->mockFile(1, 'a.txt'),
			$this->mockFile(2, 'b.txt'),
			$this->mockFile(3, 'c.txt'),
		]);

		$result = $this->service->pickCandidates('alice', 2);

		$this->assertCount(2, $result['files']);
		$this->assertSame(3, $result['total']);
	}

	public function testItSurvivesAnUnreadableFolder(): void {
		$unreadable = $this->createMock(Folder::class);
		$unreadable->method('getDirectoryListing')->willThrowException(new NotPermittedException());
		$this->userFolder->method('getDirectoryListing')
			->willReturn([$this->mockFile(1, 'reachable.txt'), $unreadable]);

		$result = $this->service->pickCandidates('alice', 10);

		$this->assertSame(1, $result['total']);
	}

	public function testItReturnsRelativePaths(): void {
		$this->userFolder->method('getDirectoryListing')->willReturn([$this->mockFile(1, 'cat.jpg')]);

		$this->assertSame('cat.jpg', $this->service->pickCandidates('alice', 10)['files'][0]['path']);
	}

	public function testDeleteRemovesTheFileAndDescribesIt(): void {
		$file = $this->mockFile(7, 'doomed.txt');
		$file->expects($this->once())->method('delete');
		$this->userFolder->method('getFirstNodeById')->with(7)->willReturn($file);

		$deleted = $this->service->delete('alice', 7);

		$this->assertSame(7, $deleted['id']);
		$this->assertSame('doomed.txt', $deleted['name']);
	}

	public function testDeleteRejectsAnUnknownFile(): void {
		$this->userFolder->method('getFirstNodeById')->willReturn(null);

		$this->expectException(NotFoundException::class);

		$this->service->delete('alice', 7);
	}

	public function testDeleteRejectsAFolder(): void {
		$this->userFolder->method('getFirstNodeById')->willReturn($this->createMock(Folder::class));

		$this->expectException(NotFoundException::class);

		$this->service->delete('alice', 7);
	}

	public function testDeleteRefusesAProtectedFile(): void {
		$file = $this->mockFile(7, 'precious.txt', false);
		$file->expects($this->never())->method('delete');
		$this->userFolder->method('getFirstNodeById')->willReturn($file);

		$this->expectException(NotPermittedException::class);

		$this->service->delete('alice', 7);
	}
}
