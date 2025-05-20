<?php

use Sitnikovik\Phync\Mutex\FileMutex;

require __DIR__ . '/../vendor/autoload.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php increment_with_mutex.php /path/to/counter.txt\n");
    exit(1);
}

$counterFile = $argv[1];
$mutex = new FileMutex();

$mutex->lock();

$value = (int)file_get_contents($counterFile);
file_put_contents($counterFile, (string)($value + 1));

$mutex->unlock();