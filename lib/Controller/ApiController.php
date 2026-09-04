<?php

declare(strict_types=1);

namespace OCA\ChaoticFileCleaner\Controller;

use InvalidArgumentException;
use OCA\ChaoticFileCleaner\Exception\RestoreUnavailableException;
use OCA\ChaoticFileCleaner\ResponseDefinitions;
use OCA\ChaoticFileCleaner\Service\FileWheelService;
use OCA\ChaoticFileCleaner\Service\HistoryService;
use OCA\ChaoticFileCleaner\Service\RestoreService;
use OCA\ChaoticFileCleaner\Service\SettingsService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCS\OCSBadRequestException;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\AppFramework\OCSController;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

/**
 * @psalm-import-type ChaoticFileCleanerFile from ResponseDefinitions
 * @psalm-import-type ChaoticFileCleanerHistoryEntry from ResponseDefinitions
 * @psalm-import-type ChaoticFileCleanerSettings from ResponseDefinitions
 *
 * @psalm-suppress UnusedClass
 */
class ApiController extends OCSController {
	/**
	 * Upper bound for the amount of segments we are willing to draw on the wheel.
	 * More than this and the labels become unreadable anyway.
	 */
	private const MAX_WHEEL_SIZE = 40;

	public function __construct(
		string $appName,
		IRequest $request,
		private FileWheelService $fileWheelService,
		private HistoryService $historyService,
		private SettingsService $settingsService,
		private RestoreService $restoreService,
		private IUserSession $userSession,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Get a random selection of the current user's files to put on the wheel
	 *
	 * @param int $limit How many files to place on the wheel, between 2 and 40
	 *
	 * @return DataResponse<Http::STATUS_OK, array{files: list<ChaoticFileCleanerFile>, total: int, folder: string, nameFilter: string}, array{}>
	 *
	 * @throws OCSForbiddenException No user is logged in
	 * @throws OCSNotFoundException The configured folder no longer exists
	 *
	 * 200: The wheel candidates and the rules that produced them
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/wheel')]
	public function wheel(int $limit = 12): DataResponse {
		$limit = max(2, min(self::MAX_WHEEL_SIZE, $limit));
		$userId = $this->requireUserId();
		$settings = $this->settingsService->get($userId);

		try {
			$candidates = $this->fileWheelService->pickCandidates(
				$userId,
				$limit,
				$settings['folder'],
				$settings['nameFilter'],
			);
		} catch (NotFoundException $e) {
			// The folder the user picked has since been moved or deleted.
			throw new OCSNotFoundException($e->getMessage(), $e);
		}

		return new DataResponse($candidates + $settings);
	}

	/**
	 * Get the rules limiting what the wheel may choose from
	 *
	 * @return DataResponse<Http::STATUS_OK, ChaoticFileCleanerSettings, array{}>
	 *
	 * @throws OCSForbiddenException No user is logged in
	 *
	 * 200: The current settings
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/settings')]
	public function settings(): DataResponse {
		return new DataResponse($this->settingsService->get($this->requireUserId()));
	}

	/**
	 * Change the rules limiting what the wheel may choose from
	 *
	 * @param string $folder Folder to clean from, relative to the user's home. Empty for everything
	 * @param string $nameFilter Only names containing this text are eligible. Empty for every name
	 *
	 * @return DataResponse<Http::STATUS_OK, ChaoticFileCleanerSettings, array{}>
	 *
	 * @throws OCSBadRequestException The path or filter is not acceptable
	 * @throws OCSForbiddenException No user is logged in
	 * @throws OCSNotFoundException The folder does not exist for this user
	 *
	 * 200: The settings as they were stored
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/settings')]
	public function saveSettings(string $folder = '', string $nameFilter = ''): DataResponse {
		try {
			$stored = $this->settingsService->set($this->requireUserId(), $folder, $nameFilter);
		} catch (InvalidArgumentException $e) {
			throw new OCSBadRequestException($e->getMessage(), $e);
		} catch (NotFoundException $e) {
			throw new OCSNotFoundException($e->getMessage(), $e);
		}

		return new DataResponse($stored);
	}

	/**
	 * Delete the file the wheel landed on
	 *
	 * @param int $fileId The id of the doomed file
	 *
	 * @return DataResponse<Http::STATUS_OK, array{deleted: ChaoticFileCleanerFile, canRestore: bool}, array{}>
	 *
	 * @throws OCSForbiddenException No user is logged in, or the file may not be deleted
	 * @throws OCSNotFoundException The file does not exist for this user
	 *
	 * 200: The file that was deleted
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/files/{fileId}', requirements: ['fileId' => '\d+'])]
	public function destroy(int $fileId): DataResponse {
		$userId = $this->requireUserId();

		try {
			$deleted = $this->fileWheelService->delete($userId, $fileId);
		} catch (NotFoundException $e) {
			throw new OCSNotFoundException($e->getMessage(), $e);
		} catch (NotPermittedException $e) {
			throw new OCSForbiddenException($e->getMessage(), $e);
		}

		$this->historyService->record($userId, $deleted);

		return new DataResponse([
			'deleted' => $deleted,
			'canRestore' => $this->restoreService->isAvailable(),
		]);
	}

	/**
	 * Pull a file back out of the trash bin
	 *
	 * @param string $path The path the file had, relative to the user's home
	 *
	 * @return DataResponse<Http::STATUS_OK, array{restored: string}, array{}>
	 *
	 * @throws OCSBadRequestException There is no trash bin to restore from
	 * @throws OCSForbiddenException No user is logged in
	 * @throws OCSNotFoundException Nothing matching is waiting in the trash
	 *
	 * 200: The path that was put back
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/restore')]
	public function restore(string $path): DataResponse {
		$user = $this->requireUser();

		try {
			$this->restoreService->restore($user, $path);
		} catch (RestoreUnavailableException $e) {
			throw new OCSBadRequestException($e->getMessage(), $e);
		} catch (NotFoundException $e) {
			throw new OCSNotFoundException($e->getMessage(), $e);
		}

		// It is no longer a casualty, so it should not be listed as one.
		$this->historyService->forgetNewest($user->getUID(), $path);

		return new DataResponse(['restored' => $path]);
	}

	/**
	 * Get the files this user has already fed to the wheel, newest first
	 *
	 * @return DataResponse<Http::STATUS_OK, array{entries: list<ChaoticFileCleanerHistoryEntry>}, array{}>
	 *
	 * @throws OCSForbiddenException No user is logged in
	 *
	 * 200: The deletion history
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/history')]
	public function history(): DataResponse {
		return new DataResponse(
			['entries' => $this->historyService->list($this->requireUserId())],
		);
	}

	/**
	 * @throws OCSForbiddenException No user is logged in
	 */
	private function requireUser(): IUser {
		$user = $this->userSession->getUser();

		if ($user === null) {
			throw new OCSForbiddenException('No user is logged in');
		}

		return $user;
	}

	/**
	 * @throws OCSForbiddenException No user is logged in
	 */
	private function requireUserId(): string {
		return $this->requireUser()->getUID();
	}
}
