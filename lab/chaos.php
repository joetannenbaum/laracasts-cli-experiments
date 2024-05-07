<?php

$chaosPath = __DIR__ . '/../directory-watcher';

$clearInterval = 50;

if (!is_dir($chaosPath)) {
    mkdir(
        directory: $chaosPath,
        recursive: true,
    );
}

$iteration = 0;

// Generate a random string
function randomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return trim($randomString);
}

function randomExtension()
{
    $extensions = ['txt', 'json', 'php', 'js', 'ts', 'csv'];

    return $extensions[rand(0, count($extensions) - 1)];
}

function randomFilename()
{
    return randomString() . '.' . randomExtension();
}

function clearDirectory($chaosPath)
{
    foreach (glob($chaosPath . '/*') as $file) {
        if (is_dir($file)) {
            rmdir($file);
        } else {
            unlink($file);
        }
    }

    echo "Cleared directory\n";
}

if (($argv[1] ?? null) === 'clean') {
    exit;
}

$addFile = function () use ($chaosPath) {
    $filename = randomFilename();
    file_put_contents($chaosPath . '/' . $filename, randomString());

    echo "Added $filename\n";
};

$makeDir = function () use ($chaosPath) {
    $dir = randomString();

    mkdir($chaosPath . '/' . $dir);

    echo "Made directory $dir\n";
};

$removeFile = function () use ($chaosPath) {
    $files = glob($chaosPath . '/*');

    if (count($files) === 0) {
        return;
    }

    $file = $files[rand(0, count($files) - 1)];

    if (is_dir($file)) {
        rmdir($file);
    } else {
        unlink($file);
    }

    echo 'Removed ' . basename($file) . "\n";
};

$chmod = function () use ($chaosPath) {
    $files = glob($chaosPath . '/*');

    if (count($files) === 0) {
        return;
    }

    $file = $files[rand(0, count($files) - 1)];

    $modes = [0777, 0755, 0700];

    chmod($file, $modes[rand(0, count($modes) - 1)]);

    echo 'Chmod ' . basename($file) . "\n";
};

$touch = function () use ($chaosPath) {
    $files = glob($chaosPath . '/*');

    if (count($files) === 0) {
        return;
    }

    $file = $files[rand(0, count($files) - 1)];

    if (is_dir($file)) {
        return;
    }

    touch($file);

    echo 'Touched ' . basename($file) . "\n";
};

$addToFile = function () use ($chaosPath) {
    $files = glob($chaosPath . '/*');

    if (count($files) === 0) {
        return;
    }

    $file = $files[rand(0, count($files) - 1)];

    if (is_dir($file)) {
        return;
    }

    file_put_contents($file, randomString(), FILE_APPEND);

    echo 'Wrote to ' . basename($file) . "\n";
};

$actions = [
    $addFile,
    $makeDir,
    $chmod,
    $touch,
    $addToFile,
];

$actions = array_merge($actions);
$actions = array_merge($actions);
$actions = array_merge($actions);

$actions[] = $removeFile;

while (true) {
    $actions[rand(0, count($actions) - 1)]();

    if ($iteration % $clearInterval === 0) {
        clearDirectory($chaosPath);
    }

    $iteration++;

    usleep(rand(500_000, 500_000 * 2));
}
