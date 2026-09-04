<?php

declare(strict_types=1);

namespace Service;

use OCA\ChaoticFileCleaner\AppInfo\Application;
use OCA\ChaoticFileCleaner\Service\HistoryService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HistoryServiceTest extends TestCase {
	private IConfig&MockObject $config;
	private ITimeFactory&MockObject $timeFactory;
	private HistoryService $service;

	/** The value currently "stored" for the user. */
	private string $stored = '';

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->timeFactory->method('getTime')->willReturn(1_700_000_000);

		$this->config->method('getUserValue')
			->willReturnCallback(fn (): string => $this->stored);
		$this->config->method('setUserValue')
			->willReturnCallback(function (string $u, string $a, string $k, string $v): void {
				$this->stored = $v;
			});

		$this->service = new HistoryService($this->config, $this->timeFactory);
	}

	private function file(string $name, string $path, int $size = 10): array {
		return ['id' => 1, 'name' => $name, 'path' => $path, 'size' => $size];
	}

	public function testHistoryStartsEmpty(): void {
		$this->assertSame([], $this->service->list('alice'));
	}

	public function testRecordedFileComesBack(): void {
		$this->service->record('alice', $this->file('cat.jpg', 'Photos/cat.jpg', 2048));

		$this->assertSame([[
			'name' => 'cat.jpg',
			'path' => 'Photos/cat.jpg',
			'size' => 2048,
			'deletedAt' => 1_700_000_000,
		]], $this->service->list('alice'));
	}

	public function testNewestEntryComesFirst(): void {
		$this->service->record('alice', $this->file('first.txt', 'first.txt'));
		$this->service->record('alice', $this->file('second.txt', 'second.txt'));

		$this->assertSame(
			['second.txt', 'first.txt'],
			array_column($this->service->list('alice'), 'name'),
		);
	}

	public function testHistoryIsCappedAndDropsTheOldest(): void {
		for ($i = 1; $i <= HistoryService::MAX_ENTRIES + 5; $i++) {
			$this->service->record('alice', $this->file("file$i.txt", "file$i.txt"));
		}

		$entries = $this->service->list('alice');

		$this->assertCount(HistoryService::MAX_ENTRIES, $entries);
		$this->assertSame('file' . (HistoryService::MAX_ENTRIES + 5) . '.txt', $entries[0]['name']);
		$this->assertSame('file6.txt', $entries[HistoryService::MAX_ENTRIES - 1]['name']);
	}

	public function testUnreadableValueIsTreatedAsNoHistory(): void {
		$this->stored = 'not json at all';

		$this->assertSame([], $this->service->list('alice'));
	}

	public function testEntriesMissingFieldsAreSkipped(): void {
		$this->stored = json_encode([
			['name' => 'good.txt', 'path' => 'good.txt', 'size' => 1, 'deletedAt' => 5],
			['name' => 'broken.txt'],
			'not even an array',
		]);

		$entries = $this->service->list('alice');

		$this->assertCount(1, $entries);
		$this->assertSame('good.txt', $entries[0]['name']);
	}

	public function testForgettingRemovesTheNewestMatchingEntry(): void {
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->stored = json_encode([
			['name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 1, 'deletedAt' => 300],
			['name' => 'dog.jpg', 'path' => 'Photos/dog.jpg', 'size' => 1, 'deletedAt' => 200],
			['name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 1, 'deletedAt' => 100],
		]);

		$this->assertTrue($this->service->forgetNewest('alice', 'Photos/cat.jpg'));

		$remaining = $this->service->list('alice');
		$this->assertSame(['Photos/dog.jpg', 'Photos/cat.jpg'], array_column($remaining, 'path'));
		// The older duplicate survives, so only one restore is forgotten at a time.
		$this->assertSame(100, $remaining[1]['deletedAt']);
	}

	public function testForgettingSomethingAbsentChangesNothing(): void {
		$this->service->record('alice', $this->file('cat.jpg', 'Photos/cat.jpg'));

		$this->assertFalse($this->service->forgetNewest('alice', 'Photos/nothing.jpg'));
		$this->assertCount(1, $this->service->list('alice'));
	}

	public function testItWritesUnderTheAppId(): void {
		$config = $this->createMock(IConfig::class);
		$config->method('getUserValue')->willReturn('');
		$config->expects($this->once())
			->method('setUserValue')
			->with('alice', Application::APP_ID, $this->isType('string'), $this->isType('string'));

		(new HistoryService($config, $this->timeFactory))
			->record('alice', $this->file('x.txt', 'x.txt'));
	}
}
