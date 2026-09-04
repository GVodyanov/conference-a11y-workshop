<?php

declare(strict_types=1);

namespace OCA\ChaoticFileCleaner\Service;

use InvalidArgumentException;
use OCA\ChaoticFileCleaner\AppInfo\Application;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IConfig;

/**
 * Per-user rules narrowing down what the wheel is allowed to eat.
 */
class SettingsService {
	private const KEY_FOLDER = 'clean_folder';
	private const KEY_NAME_FILTER = 'name_filter';

	/** Filters longer than this are refused rather than silently truncated. */
	private const MAX_FILTER_LENGTH = 255;

	public function __construct(
		private IConfig $config,
		private IRootFolder $rootFolder,
	) {
	}

	/**
	 * The user's current rules.
	 *
	 * An empty folder means the whole home directory, an empty filter means
	 * every file name qualifies.
	 *
	 * @param string $userId The user whose settings to read
	 *
	 * @return array{folder: string, nameFilter: string}
	 */
	public function get(string $userId): array {
		return [
			'folder' => $this->config->getUserValue($userId, Application::APP_ID, self::KEY_FOLDER, ''),
			'nameFilter' => $this->config->getUserValue($userId, Application::APP_ID, self::KEY_NAME_FILTER, ''),
		];
	}

	/**
	 * Store new rules, after checking the folder is really one of the user's folders.
	 *
	 * @param string $userId The user whose settings to write
	 * @param string $folder Folder to clean from, relative to the user's home
	 * @param string $nameFilter Only file names containing this are eligible
	 *
	 * @return array{folder: string, nameFilter: string} The settings as they were stored
	 *
	 * @throws InvalidArgumentException If the path or filter is not acceptable
	 * @throws NotFoundException If the folder does not exist for this user
	 */
	public function set(string $userId, string $folder, string $nameFilter): array {
		$normalisedFolder = self::normalisePath($folder);
		$trimmedFilter = trim($nameFilter);

		if (mb_strlen($trimmedFilter) > self::MAX_FILTER_LENGTH) {
			throw new InvalidArgumentException('The name filter is too long');
		}

		if ($normalisedFolder !== '') {
			$userFolder = $this->rootFolder->getUserFolder($userId);
			if (!$userFolder->get($normalisedFolder) instanceof Folder) {
				throw new NotFoundException('Not a folder: ' . $normalisedFolder);
			}
		}

		$this->config->setUserValue($userId, Application::APP_ID, self::KEY_FOLDER, $normalisedFolder);
		$this->config->setUserValue($userId, Application::APP_ID, self::KEY_NAME_FILTER, $trimmedFilter);

		return ['folder' => $normalisedFolder, 'nameFilter' => $trimmedFilter];
	}

	/**
	 * Reduce a path to bare segments relative to the user's home.
	 *
	 * The file picker hands back paths like `/Photos/2024`, and we store
	 * `Photos/2024`. Traversal out of the home directory is refused outright
	 * rather than normalised away.
	 *
	 * @param string $path The path to clean up
	 *
	 * @throws InvalidArgumentException If the path tries to climb out of the home folder
	 */
	public static function normalisePath(string $path): string {
		$segments = [];

		foreach (explode('/', str_replace('\\', '/', $path)) as $segment) {
			if ($segment === '' || $segment === '.') {
				continue;
			}

			if ($segment === '..') {
				throw new InvalidArgumentException('Path may not leave the home folder');
			}

			$segments[] = $segment;
		}

		return implode('/', $segments);
	}
}
