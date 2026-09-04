<?php

declare(strict_types=1);

namespace Service;

use OCA\ChaoticFileCleaner\Exception\RestoreUnavailableException;
use OCA\ChaoticFileCleaner\Service\RestoreService;
use OCA\Files_Trashbin\Trash\ITrashItem;
use OCA\Files_Trashbin\Trash\ITrashManager;
use OCP\Files\NotFoundException;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RestoreServiceTest extends TestCase {
	private ITrashManager&MockObject $trashManager;
	private IUser&MockObject $user;
	private RestoreService $service;

	protected function setUp(): void {
		parent::setUp();

		$this->trashManager = $this->createMock(ITrashManager::class);
		$this->user = $this->createMock(IUser::class);
		$this->service = new RestoreService($this->trashManager);
	}

	private function trashItem(string $originalLocation, int $deletedAt): ITrashItem&MockObject {
		$item = $this->createMock(ITrashItem::class);
		$item->method('getOriginalLocation')->willReturn($originalLocation);
		$item->method('getDeletedTime')->willReturn($deletedAt);

		return $item;
	}

	public function testItIsUnavailableWithoutTheTrashbinApp(): void {
		$service = new RestoreService(null);

		$this->assertFalse($service->isAvailable());
	}

	public function testItIsAvailableWithTheTrashbinApp(): void {
		$this->assertTrue($this->service->isAvailable());
	}

	public function testRestoringWithoutTheTrashbinAppIsRefused(): void {
		$this->expectException(RestoreUnavailableException::class);

		(new RestoreService(null))->restore($this->user, 'Photos/cat.jpg');
	}

	public function testItRestoresTheMatchingItem(): void {
		$wanted = $this->trashItem('Photos/cat.jpg', 500);
		$this->trashManager->method('listTrashRoot')->willReturn([
			$this->trashItem('Documents/other.txt', 400),
			$wanted,
		]);

		$this->trashManager->expects($this->once())->method('restoreItem')->with($wanted);

		$this->service->restore($this->user, 'Photos/cat.jpg');
	}

	public function testItPrefersTheMostRecentDeletionOfTheSamePath(): void {
		$newest = $this->trashItem('notes.md', 900);
		$this->trashManager->method('listTrashRoot')->willReturn([
			$this->trashItem('notes.md', 100),
			$newest,
			$this->trashItem('notes.md', 500),
		]);

		$this->trashManager->expects($this->once())->method('restoreItem')->with($newest);

		$this->service->restore($this->user, 'notes.md');
	}

	public function testItIgnoresLeadingSlashesOnEitherSide(): void {
		$wanted = $this->trashItem('/Photos/cat.jpg', 500);
		$this->trashManager->method('listTrashRoot')->willReturn([$wanted]);

		$this->trashManager->expects($this->once())->method('restoreItem')->with($wanted);

		$this->service->restore($this->user, '/Photos/cat.jpg');
	}

	public function testItComplainsWhenNothingMatches(): void {
		$this->trashManager->method('listTrashRoot')->willReturn([
			$this->trashItem('Documents/other.txt', 400),
		]);
		$this->trashManager->expects($this->never())->method('restoreItem');

		$this->expectException(NotFoundException::class);

		$this->service->restore($this->user, 'Photos/cat.jpg');
	}

	public function testItComplainsWhenTheTrashIsEmpty(): void {
		$this->trashManager->method('listTrashRoot')->willReturn([]);

		$this->expectException(NotFoundException::class);

		$this->service->restore($this->user, 'Photos/cat.jpg');
	}
}
