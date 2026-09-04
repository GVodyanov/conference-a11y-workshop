<?php

declare(strict_types=1);

namespace OCA\ChaoticFileCleaner\Service;

use OCA\ChaoticFileCleaner\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;

/**
 * Keeps a per-user record of everything the wheel has devoured.
 *
 * The list is short and only ever read as a whole, so it lives in a single
 * user config value rather than in a table of its own.
 */
class HistoryService {
	private const CONFIG_KEY = 'deletion_history';

	/** How many past victims to remember before the oldest falls off. */
	public const MAX_ENTRIES = 50;

	public function __construct(
		private IConfig $config,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * The files this user has fed to the wheel, newest first.
	 *
	 * @param string $userId The user whose history to read
	 *
	 * @return list<array{name: string, path: string, size: int, deletedAt: int}>
	 */
	public function list(string $userId): array {
		$raw = $this->config->getUserValue($userId, Application::APP_ID, self::CONFIG_KEY, '');
		if ($raw === '') {
			return [];
		}

		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			// A value we cannot read is treated as no history rather than an error;
			// losing a joke app's history is not worth failing the request over.
			return [];
		}

		$entries = [];
		foreach ($decoded as $entry) {
			if (!$this->isValidEntry($entry)) {
				continue;
			}

			$entries[] = [
				'name' => (string)$entry['name'],
				'path' => (string)$entry['path'],
				'size' => (int)$entry['size'],
				'deletedAt' => (int)$entry['deletedAt'],
			];
		}

		return $entries;
	}

	/**
	 * Add a file to the front of the user's history.
	 *
	 * @param string $userId The user who span the wheel
	 * @param array{id: int, name: string, path: string, size: int} $file The file that was deleted
	 */
	public function record(string $userId, array $file): void {
		$entries = $this->list($userId);

		array_unshift($entries, [
			'name' => $file['name'],
			'path' => $file['path'],
			'size' => $file['size'],
			'deletedAt' => $this->timeFactory->getTime(),
		]);

		$this->config->setUserValue(
			$userId,
			Application::APP_ID,
			self::CONFIG_KEY,
			json_encode(array_slice($entries, 0, self::MAX_ENTRIES)),
		);
	}

	/**
	 * Drop the newest entry for a path, because the file is no longer lost.
	 *
	 * @param string $userId The user whose history to amend
	 * @param string $path The path that came back from the dead
	 *
	 * @return bool Whether an entry was actually removed
	 */
	public function forgetNewest(string $userId, string $path): bool {
		$entries = $this->list($userId);

		foreach ($entries as $index => $entry) {
			if ($entry['path'] !== $path) {
				continue;
			}

			// The list is newest first, so the first hit is the newest one.
			array_splice($entries, $index, 1);
			$this->config->setUserValue(
				$userId,
				Application::APP_ID,
				self::CONFIG_KEY,
				json_encode($entries),
			);

			return true;
		}

		return false;
	}

	/**
	 * @param mixed $entry
	 *
	 * @psalm-assert-if-true array{name: mixed, path: mixed, size: mixed, deletedAt: mixed} $entry
	 */
	private function isValidEntry(mixed $entry): bool {
		return is_array($entry)
			&& isset($entry['name'], $entry['path'], $entry['size'], $entry['deletedAt']);
	}
}
