<?php

/*
 * (c) Ilya Sitnikov <sitnikovik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sitnikovik\Phync\Mutex\LockFile;

require __DIR__ . '/../vendor/autoload.php';

if ($argc < 3) {
    fwrite(STDERR, "Usage: php increment_with_mutex.php /path/to/counter.txt /path/to/mutex.lock\n");
    exit(1);
}

$counterFile = $argv[1];
$mutex = new LockFile($argv[2]);

$mutex->lock();

$value = (int)file_get_contents($counterFile);
file_put_contents($counterFile, (string)($value + 1));

$mutex->unlock();