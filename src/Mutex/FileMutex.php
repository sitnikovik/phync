<?php

/*
 * (c) Ilya Sitnikov <sitnikovik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sitnikovik\Phync\Mutex;

use RuntimeException;

/**
 * A mutex (short for mutual exclusion) is a synchronization primitive used to control access
 * to a shared resource in concurrent environments.
 */
final class FileMutex implements Mutex
{
    /**
     * The delay in microseconds before retrying to acquire the lock.
     * 
     * @var int
     */
    private const RETRY_MS = 1 * 1_000_000;

    /**
     * The path to the lock file.
     * 
     * @var string
     */
    private string $path;

    /**
     * The flag indicating whether the mutex is currently locked.
     * 
     * @var bool
     */
    private bool $locked = false;

    /**
     * Creates a new instance of the FileMutex class.
     */
    public function __construct()
    {
        $this->path = sys_get_temp_dir() . 'mutex.lock';
    }

    /**
     * Blocks for the writing.
     * 
     * Writers must wait until all readers are done and no other writers are active.
     * 
     * @return void
     * @throws RuntimeException if the lock cannot be acquired or lock file cannot be created.
     */
    public function lock(): void
    {
        if ($this->locked) {
            throw new RuntimeException('Lock already acquired');
        }

        $fp = fopen($this->path, 'c');
        if (!$fp) {
            fclose($fp);
            throw new RuntimeException('Failed to create lock');
        }

        while (!flock($fp, LOCK_EX | LOCK_NB)) {
            echo "Waiting for lock...\n";
            usleep(self::RETRY_MS);
        }

        $this->locked = true;
    }

    /**
     * Unlocks the mutex.
     * 
     * This method removes the lock file, allowing other processes to acquire the lock.
     * 
     * @return void
     * @throws RuntimeException if the lock file cannot be deleted or does not exist.
     */
    public function unlock(): void
    {
        if (!$this->locked) {
            throw new RuntimeException('Lock not acquired');
        }

        $fp = fopen($this->path, 'c');
        if (!flock($fp, LOCK_UN)) {
            fclose($fp);
            throw new RuntimeException('Failed to unlock');
        }

        $this->locked = false;
        fclose($fp);
    }

    /**
     * Tries to acquire the lock without blocking.
     * 
     * @return bool True if the lock was acquired, false otherwise.
     */
    public function tryLock(): bool
    {
        if ($this->locked) {
            return false;
        }

        $fp = fopen($this->path, 'c');
        if (!$fp) {
            throw new RuntimeException("Cannot open lock file");
        }

        if (flock($fp, LOCK_EX | LOCK_NB)) {
            $this->locked = true;
            return true;
        }

        $this->locked = false;
        fclose($fp);
        return false;
    }
}
