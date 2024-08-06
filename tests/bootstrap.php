<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/vendor/autoload.php';

WP_Mock::setUsePatchwork(true);
WP_Mock::activateStrictMode();
WP_Mock::bootstrap();
