<?php

use App\DirectoryWatcher;

require __DIR__  . '/../vendor/autoload.php';

(new DirectoryWatcher(__DIR__ . '/../directory-watcher'))->watch();
