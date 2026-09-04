<?php

declare(strict_types=1);

namespace OCA\ChaoticFileCleaner\Service;

use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;

/**
 * Collects the files a user owns and feeds them to the wheel of fate.
 */
class FileWheelService {
	/**
	 * Hard ceiling on how many nodes we walk through, so that accounts with
	 * a huge amount of files still get an answer instead of a timeout.
	 */
	private const MAX_SCANNED_NODES = 20000;

	public function __construct(
		private IRootFolder $rootFolder,
	) {
	}

	/**
	 * Pick a random selection of deletable files from the user's storage.
	 *
	 * The selection is randomised server side so that the wheel is not always
	 * filled with the first few files of the alphabetically first folder.
	 *
	 * @param string $userId The user whose files should be considered
	 * @param int $limit How many files to return at most
	 *
	 * @return array{files: list<array{id: int, name: string, path: string, size: int}>, total: int}
	 */
	public function pickCandidates(string $userId, int $limit): array {
		$userFolder = $this->rootFolder->getUserFolder($userId);

		/** @var list<array{id: int, name: string, path: string, size: int}> $candidates */
		$candidates = [];
		/** @var list<Folder> $queue */
		$queue = [$userFolder];
		$scanned = 0;

		while ($queue !== [] && $scanned < self::MAX_SCANNED_NODES) {
			$folder = array_shift($queue);

			try {
				$children = $folder->getDirectoryListing();
			} catch (NotFoundException|NotPermittedException) {
				// A folder that vanished or that we may not read is simply skipped
				continue;
			}

			foreach ($children as $child) {
				$scanned++;

				if ($child instanceof Folder) {
					$queue[] = $child;
					continue;
				}

				if (!$child instanceof File || !$child->isDeletable()) {
					continue;
				}

				$candidates[] = [
					'id' => $child->getId(),
					'name' => $child->getName(),
					'path' => $userFolder->getRelativePath($child->getPath()) ?? $child->getName(),
					'size' => (int)$child->getSize(),
				];
			}
		}

		$total = count($candidates);
		shuffle($candidates);

		return [
			'files' => array_slice($candidates, 0, max(1, $limit)),
			'total' => $total,
		];
	}

	/**
	 * Let fate have its way with a single file.
	 *
	 * Looking the file up through the user folder means a user can only ever
	 * delete something they actually have access to.
	 *
	 * @param string $userId The user the file belongs to
	 * @param int $fileId The id of the file to delete
	 *
	 * @return array{id: int, name: string, path: string, size: int} The file that was deleted
	 *
	 * @throws NotFoundException If no such file exists for this user
	 * @throws NotPermittedException If the file may not be deleted
	 */
	public function delete(string $userId, int $fileId): array {
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$node = $userFolder->getFirstNodeById($fileId);

		if (!$node instanceof File) {
			throw new NotFoundException('No such file: ' . $fileId);
		}

		if (!$node->isDeletable()) {
			throw new NotPermittedException('File may not be deleted: ' . $fileId);
		}

		$deleted = [
			'id' => $node->getId(),
			'name' => $node->getName(),
			'path' => $userFolder->getRelativePath($node->getPath()) ?? $node->getName(),
			'size' => (int)$node->getSize(),
		];

		$node->delete();

		return $deleted;
	}
}
