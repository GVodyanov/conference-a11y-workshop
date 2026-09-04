<?php

declare(strict_types=1);

namespace OCA\ChaoticFileCleaner\Controller;

use OCA\ChaoticFileCleaner\ResponseDefinitions;
use OCA\ChaoticFileCleaner\Service\FileWheelService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\AppFramework\OCSController;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * @psalm-import-type ChaoticFileCleanerFile from ResponseDefinitions
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
		private IUserSession $userSession,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Get a random selection of the current user's files to put on the wheel
	 *
	 * @param int $limit How many files to place on the wheel, between 2 and 40
	 *
	 * @return DataResponse<Http::STATUS_OK, array{files: list<ChaoticFileCleanerFile>, total: int}, array{}>
	 *
	 * @throws OCSForbiddenException No user is logged in
	 *
	 * 200: The wheel candidates
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/wheel')]
	public function wheel(int $limit = 12): DataResponse {
		$limit = max(2, min(self::MAX_WHEEL_SIZE, $limit));

		return new DataResponse(
			$this->fileWheelService->pickCandidates($this->requireUserId(), $limit),
		);
	}

	/**
	 * Delete the file the wheel landed on
	 *
	 * @param int $fileId The id of the doomed file
	 *
	 * @return DataResponse<Http::STATUS_OK, array{deleted: ChaoticFileCleanerFile}, array{}>
	 *
	 * @throws OCSForbiddenException No user is logged in, or the file may not be deleted
	 * @throws OCSNotFoundException The file does not exist for this user
	 *
	 * 200: The file that was deleted
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/files/{fileId}', requirements: ['fileId' => '\d+'])]
	public function destroy(int $fileId): DataResponse {
		try {
			$deleted = $this->fileWheelService->delete($this->requireUserId(), $fileId);
		} catch (NotFoundException $e) {
			throw new OCSNotFoundException($e->getMessage(), $e);
		} catch (NotPermittedException $e) {
			throw new OCSForbiddenException($e->getMessage(), $e);
		}

		return new DataResponse(['deleted' => $deleted]);
	}

	/**
	 * @throws OCSForbiddenException No user is logged in
	 */
	private function requireUserId(): string {
		$user = $this->userSession->getUser();

		if ($user === null) {
			throw new OCSForbiddenException('No user is logged in');
		}

		return $user->getUID();
	}
}
