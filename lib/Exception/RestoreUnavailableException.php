<?php

declare(strict_types=1);

namespace OCA\ChaoticFileCleaner\Exception;

use Exception;

/**
 * Thrown when there is no trash bin to pull a file back out of.
 */
class RestoreUnavailableException extends Exception {
}
