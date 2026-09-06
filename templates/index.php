<?php

declare(strict_types=1);

use OCP\Util;

Util::addScript(OCA\ChaoticFileCleaner\AppInfo\Application::APP_ID, OCA\ChaoticFileCleaner\AppInfo\Application::APP_ID . '-main');
Util::addStyle(OCA\ChaoticFileCleaner\AppInfo\Application::APP_ID, OCA\ChaoticFileCleaner\AppInfo\Application::APP_ID . '-main');

?>

<div id="chaotic_file_cleaner" dir="ltr"></div>
