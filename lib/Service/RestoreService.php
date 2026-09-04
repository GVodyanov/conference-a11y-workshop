<?php

declare(strict_types=1);

namespace OCA\ChaoticFileCleaner\Service;

use OCA\ChaoticFileCleaner\Exception\RestoreUnavailableException;
use OCA\Files_Trashbin\Trash\ITrashItem;
use OCA\Files_Trashbin\Trash\ITrashManager;
use OCP\Files\NotFoundException;
use OCP\IUser;

/**
 * Pulls a file back out of the trash bin.
 *
 * The trash manager belongs to the Deleted files app rather than to the public
 * API, so it is an optional dependency: when that app is disabled the service
 * container cannot resolve it and hands us null instead, exactly as it does for
 * the files app's own delete command.
 */
class RestoreService {
	public function __construct(
		private ?ITrashManager $trashManager = null,
	) {
	}

	/**
	 * Whether there is a trash bin to restore from at all.
	 */
	public function isAvailable(): bool {
		return $this->trashManager !== null;
	}

	/**
	 * Put a file back where it came from.
	 *
	 * @param IUser $user The owner of the file
	 * @param string $path The path the file had, relative to the user's home
	 *
	 * @throws RestoreUnavailableException If the trash bin is not available
	 * @throws NotFoundException If no matching file is waiting in the trash
	 */
	public function restore(IUser $user, string $path): void {
		if ($this->trashManager === null) {
			throw new RestoreUnavailableException('The Deleted files app is not available');
		}

		$wanted = trim($path, '/');
		$candidates = [];

		foreach ($this->trashManager->listTrashRoot($user) as $item) {
			if (!$item instanceof ITrashItem) {
				continue;
			}

			if (trim($item->getOriginalLocation(), '/') === $wanted) {
				$candidates[] = $item;
			}
		}

		if ($candidates === []) {
			throw new NotFoundException('Nothing in the trash for: ' . $path);
		}

		// The same path may have been thrown away more than once; the newest
		// deletion is the one the user just watched happen.
		usort($candidates, static fn (ITrashItem $a, ITrashItem $b): int => $b->getDeletedTime() <=> $a->getDeletedTime());

		$this->trashManager->restoreItem($candidates[0]);
	}
}
