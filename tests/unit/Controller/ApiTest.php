<?php

declare(strict_types=1);

namespace Controller;

use InvalidArgumentException;
use OCA\ChaoticFileCleaner\AppInfo\Application;
use OCA\ChaoticFileCleaner\Controller\ApiController;
use OCA\ChaoticFileCleaner\Exception\RestoreUnavailableException;
use OCA\ChaoticFileCleaner\Service\FileWheelService;
use OCA\ChaoticFileCleaner\Service\HistoryService;
use OCA\ChaoticFileCleaner\Service\RestoreService;
use OCA\ChaoticFileCleaner\Service\SettingsService;
use OCP\AppFramework\OCS\OCSBadRequestException;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase {
	private FileWheelService&MockObject $service;
	private HistoryService&MockObject $historyService;
	private SettingsService&MockObject $settingsService;
	private RestoreService&MockObject $restoreService;
	private IUserSession&MockObject $userSession;
	private ApiController $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->service = $this->createMock(FileWheelService::class);
		$this->historyService = $this->createMock(HistoryService::class);
		$this->settingsService = $this->createMock(SettingsService::class);
		$this->restoreService = $this->createMock(RestoreService::class);
		// Unless a test says otherwise, nothing is narrowed down.
		$this->settingsService->method('get')->willReturn(['folder' => '', 'nameFilter' => '']);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->controller = new ApiController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			$this->service,
			$this->historyService,
			$this->settingsService,
			$this->restoreService,
			$this->userSession,
		);
	}

	private function loginAs(string $userId): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($userId);
		$this->userSession->method('getUser')->willReturn($user);
	}

	public function testWheelReturnsCandidates(): void {
		$this->loginAs('alice');
		$files = [['id' => 7, 'name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 42]];

		$this->service->expects($this->once())
			->method('pickCandidates')
			->with('alice', 12, '', '')
			->willReturn(['files' => $files, 'total' => 1]);

		$data = $this->controller->wheel(12)->getData();

		$this->assertSame($files, $data['files']);
		$this->assertSame(1, $data['total']);
	}

	/**
	 * @return list<array{int, int}> Requested limit and the limit that reaches the service
	 */
	public static function limitProvider(): array {
		return [
			'below the minimum' => [0, 2],
			'negative' => [-5, 2],
			'inside the range' => [8, 8],
			'above the maximum' => [500, 40],
		];
	}

	/**
	 * @dataProvider limitProvider
	 */
	public function testWheelClampsTheLimit(int $requested, int $expected): void {
		$this->loginAs('alice');

		$this->service->expects($this->once())
			->method('pickCandidates')
			->with('alice', $expected, '', '')
			->willReturn(['files' => [], 'total' => 0]);

		$this->controller->wheel($requested);
	}

	public function testWheelPassesTheStoredSettingsThrough(): void {
		$controller = new ApiController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			$this->service,
			$this->historyService,
			$settings = $this->createMock(SettingsService::class),
			$this->restoreService,
			$this->userSession,
		);
		$this->loginAs('alice');
		$settings->method('get')->willReturn(['folder' => 'Photos', 'nameFilter' => 'IMG']);

		$this->service->expects($this->once())
			->method('pickCandidates')
			->with('alice', 12, 'Photos', 'IMG')
			->willReturn(['files' => [], 'total' => 0]);

		$data = $controller->wheel()->getData();

		// The active rules ride along so the UI can show what is in force.
		$this->assertSame('Photos', $data['folder']);
		$this->assertSame('IMG', $data['nameFilter']);
	}

	public function testWheelReportsAVanishedFolder(): void {
		$this->loginAs('alice');
		$this->service->method('pickCandidates')->willThrowException(new NotFoundException('gone'));

		$this->expectException(OCSNotFoundException::class);

		$this->controller->wheel();
	}

	public function testSettingsAreReturned(): void {
		$controller = new ApiController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			$this->service,
			$this->historyService,
			$settings = $this->createMock(SettingsService::class),
			$this->restoreService,
			$this->userSession,
		);
		$this->loginAs('alice');
		$settings->method('get')->willReturn(['folder' => 'Work', 'nameFilter' => 'draft']);

		$this->assertSame(
			['folder' => 'Work', 'nameFilter' => 'draft'],
			$controller->settings()->getData(),
		);
	}

	public function testSavingSettingsReturnsWhatWasStored(): void {
		$this->loginAs('alice');

		$this->settingsService->expects($this->once())
			->method('set')
			->with('alice', '/Photos/', ' IMG ')
			->willReturn(['folder' => 'Photos', 'nameFilter' => 'IMG']);

		$this->assertSame(
			['folder' => 'Photos', 'nameFilter' => 'IMG'],
			$this->controller->saveSettings('/Photos/', ' IMG ')->getData(),
		);
	}

	public function testSavingARejectedPathIsABadRequest(): void {
		$this->loginAs('alice');
		$this->settingsService->method('set')->willThrowException(new InvalidArgumentException('nope'));

		$this->expectException(OCSBadRequestException::class);

		$this->controller->saveSettings('../etc', '');
	}

	public function testSavingAMissingFolderIsNotFound(): void {
		$this->loginAs('alice');
		$this->settingsService->method('set')->willThrowException(new NotFoundException('gone'));

		$this->expectException(OCSNotFoundException::class);

		$this->controller->saveSettings('Nowhere', '');
	}

	public function testSavingSettingsRequiresALoggedInUser(): void {
		$this->userSession->method('getUser')->willReturn(null);

		$this->expectException(OCSForbiddenException::class);

		$this->controller->saveSettings('', '');
	}

	public function testWheelRequiresALoggedInUser(): void {
		$this->userSession->method('getUser')->willReturn(null);

		$this->expectException(OCSForbiddenException::class);

		$this->controller->wheel();
	}

	public function testDestroyReturnsTheDeletedFile(): void {
		$this->loginAs('bob');
		$file = ['id' => 7, 'name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 42];

		$this->service->expects($this->once())
			->method('delete')
			->with('bob', 7)
			->willReturn($file);

		$this->assertSame($file, $this->controller->destroy(7)->getData()['deleted']);
	}

	public function testDestroyRecordsTheFileInTheHistory(): void {
		$this->loginAs('bob');
		$file = ['id' => 7, 'name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 42];
		$this->service->method('delete')->willReturn($file);

		$this->historyService->expects($this->once())
			->method('record')
			->with('bob', $file);

		$this->controller->destroy(7);
	}

	public function testAFileThatSurvivesIsNotRecorded(): void {
		$this->loginAs('bob');
		$this->service->method('delete')->willThrowException(new NotPermittedException('nope'));

		$this->historyService->expects($this->never())->method('record');

		$this->expectException(OCSForbiddenException::class);

		$this->controller->destroy(7);
	}

	public function testDestroyReportsWhetherRestoreIsPossible(): void {
		$this->loginAs('bob');
		$this->service->method('delete')->willReturn(
			['id' => 7, 'name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 42],
		);
		$this->restoreService->method('isAvailable')->willReturn(true);

		$this->assertTrue($this->controller->destroy(7)->getData()['canRestore']);
	}

	public function testRestorePutsTheFileBackAndForgetsIt(): void {
		$this->loginAs('bob');

		$this->restoreService->expects($this->once())
			->method('restore')
			->with($this->isInstanceOf(IUser::class), 'Photos/cat.jpg');
		$this->historyService->expects($this->once())
			->method('forgetNewest')
			->with('bob', 'Photos/cat.jpg');

		$this->assertSame('Photos/cat.jpg', $this->controller->restore('Photos/cat.jpg')->getData()['restored']);
	}

	public function testRestoreWithoutATrashBinIsABadRequest(): void {
		$this->loginAs('bob');
		$this->restoreService->method('restore')->willThrowException(new RestoreUnavailableException('no trash'));
		// A file that was never restored must stay in the history.
		$this->historyService->expects($this->never())->method('forgetNewest');

		$this->expectException(OCSBadRequestException::class);

		$this->controller->restore('Photos/cat.jpg');
	}

	public function testRestoreOfSomethingNotInTheTrashIsNotFound(): void {
		$this->loginAs('bob');
		$this->restoreService->method('restore')->willThrowException(new NotFoundException('gone'));
		$this->historyService->expects($this->never())->method('forgetNewest');

		$this->expectException(OCSNotFoundException::class);

		$this->controller->restore('Photos/cat.jpg');
	}

	public function testRestoreRequiresALoggedInUser(): void {
		$this->userSession->method('getUser')->willReturn(null);

		$this->expectException(OCSForbiddenException::class);

		$this->controller->restore('Photos/cat.jpg');
	}

	public function testHistoryReturnsTheStoredEntries(): void {
		$this->loginAs('alice');
		$entries = [['name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 42, 'deletedAt' => 1_700_000_000]];

		$this->historyService->expects($this->once())
			->method('list')
			->with('alice')
			->willReturn($entries);

		$this->assertSame($entries, $this->controller->history()->getData()['entries']);
	}

	public function testHistoryRequiresALoggedInUser(): void {
		$this->userSession->method('getUser')->willReturn(null);

		$this->expectException(OCSForbiddenException::class);

		$this->controller->history();
	}

	public function testDestroyTranslatesAMissingFile(): void {
		$this->loginAs('bob');
		$this->service->method('delete')->willThrowException(new NotFoundException('gone'));

		$this->expectException(OCSNotFoundException::class);

		$this->controller->destroy(7);
	}

	public function testDestroyTranslatesAProtectedFile(): void {
		$this->loginAs('bob');
		$this->service->method('delete')->willThrowException(new NotPermittedException('nope'));

		$this->expectException(OCSForbiddenException::class);

		$this->controller->destroy(7);
	}
}
