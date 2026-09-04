<?php

declare(strict_types=1);

namespace OCA\ChaoticFileCleaner;

/**
 * @psalm-type ChaoticFileCleanerFile = array{
 *     id: int,
 *     name: string,
 *     path: string,
 *     size: int,
 * }
 *
 * @psalm-type ChaoticFileCleanerHistoryEntry = array{
 *     name: string,
 *     path: string,
 *     size: int,
 *     deletedAt: int,
 * }
 *
 * @psalm-type ChaoticFileCleanerSettings = array{
 *     folder: string,
 *     nameFilter: string,
 * }
 *
 * @psalm-suppress UnusedClass
 */
class ResponseDefinitions {
}
