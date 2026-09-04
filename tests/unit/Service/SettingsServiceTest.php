<?php

declare(strict_types=1);

namespace Service;

use InvalidArgumentException;
use OCA\ChaoticFileCleaner\Service\SettingsService;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SettingsServiceTest extends TestCase {
	private IConfig&MockObject $config;
	private Folder&MockObject $userFolder;
	private SettingsService $service;

	/** @var array<string, string> */
	private array $stored = [];

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->userFolder = $this->createMock(Folder::class);

		$rootFolder = $this->createMock(IRootFolder::class);
		$rootFolder->method('getUserFolder')->willReturn($this->userFolder);

		$this->config->method('getUserValue')
			->willReturnCallback(fn (string $u, string $a, string $k, string $d = ''): string => $this->stored[$k] ?? $d);
		$this->config->method('setUserValue')
			->willReturnCallback(function (string $u, string $a, string $k, string $v): void {
				$this->stored[$k] = $v;
			});

		$this->service = new SettingsService($this->config, $rootFolder);
	}

	public function testDefaultsToTheWholeHomeAndNoFilter(): void {
		$this->assertSame(['folder' => '', 'nameFilter' => ''], $this->service->get('alice'));
	}

	public function testItStoresAndReturnsSettings(): void {
		$this->userFolder->method('get')->willReturn($this->createMock(Folder::class));

		$stored = $this->service->set('alice', '/Photos/2024', 'IMG');

		$this->assertSame(['folder' => 'Photos/2024', 'nameFilter' => 'IMG'], $stored);
		$this->assertSame(['folder' => 'Photos/2024', 'nameFilter' => 'IMG'], $this->service->get('alice'));
	}

	/**
	 * @return list<array{string, string}>
	 */
	public static function pathProvider(): array {
		return [
			'leading slash' => ['/Photos', 'Photos'],
			'trailing slash' => ['Photos/', 'Photos'],
			'both' => ['/Photos/2024/', 'Photos/2024'],
			'root' => ['/', ''],
			'empty' => ['', ''],
			'redundant separators' => ['//Photos///2024', 'Photos/2024'],
			'single dots' => ['./Photos/./2024', 'Photos/2024'],
			'backslashes' => ['\\Photos\\2024', 'Photos/2024'],
		];
	}

	/**
	 * @dataProvider pathProvider
	 */
	public function testItNormalisesPaths(string $given, string $expected): void {
		$this->assertSame($expected, SettingsService::normalisePath($given));
	}

	/**
	 * @return list<array{string}>
	 */
	public static function traversalProvider(): array {
		return [
			['../../etc/passwd'],
			['Photos/../../..'],
			['..'],
			['Photos/../../secret'],
		];
	}

	/**
	 * @dataProvider traversalProvider
	 */
	public function testItRefusesToLeaveTheHomeFolder(string $path): void {
		$this->expectException(InvalidArgumentException::class);

		SettingsService::normalisePath($path);
	}

	public function testItRefusesAPathThatIsNotAFolder(): void {
		$this->userFolder->method('get')->willReturn($this->createMock(File::class));

		$this->expectException(NotFoundException::class);

		$this->service->set('alice', 'Photos/cat.jpg', '');
	}

	public function testItPropagatesAMissingFolder(): void {
		$this->userFolder->method('get')->willThrowException(new NotFoundException('gone'));

		$this->expectException(NotFoundException::class);

		$this->service->set('alice', 'Nowhere', '');
	}

	public function testTheWholeHomeNeedsNoFolderLookup(): void {
		$this->userFolder->expects($this->never())->method('get');

		$this->assertSame(['folder' => '', 'nameFilter' => ''], $this->service->set('alice', '', ''));
	}

	public function testItTrimsTheFilter(): void {
		$this->assertSame('IMG', $this->service->set('alice', '', '  IMG  ')['nameFilter']);
	}

	public function testItRefusesAnOverlongFilter(): void {
		$this->expectException(InvalidArgumentException::class);

		$this->service->set('alice', '', str_repeat('a', 256));
	}
}
