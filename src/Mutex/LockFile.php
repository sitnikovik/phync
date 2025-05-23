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
 * LockFile class implements a mutex using a lock file.
 * 
 * Mutex is a synchronization primitive used to control access to a shared resource
 * in concurrent environments. It ensures that only one process or thread can access
 * the critical section of code or resource at a time to prevent.
 */
final class LockFile implements Mutex
{
    /**
     * The flag indicating whether the mutex is currently locked.
     * 
     * @var bool
     */
    private bool $locked = false;

    /**
     * The file pointer for the lock file.
     * 
     * @var resource
     */
    private $fp;

    /**
     * Creates a new instance of the LockFile class.
     * 
     * @param string $path The path to the lock file.
     */
    public function __construct(
        private string $path
    ) {}

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

        $this->fp = fopen($this->path, 'c');
        if (!$this->fp) {
            throw new RuntimeException('Failed to open lock file');
        }

        while (!flock($this->fp, LOCK_EX)) {
            // spin without sleeping. 
            // sleeps can schedule other processes to run
            // and we don't want to give up the lock
            // to other processes
        }

        $this->locked = true;
    }

    /**
     * Unlocks the mutex.
     * 
     * This method removes the lock file, allowing other processes to acquire the lock.
     * Not deletes the lock file itself, but releases the lock on it.
     * 
     * @return void
     * @throws RuntimeException if the lock file cannot be deleted or does not exist.
     */
    public function unlock(): void
    {
        if (!$this->locked) {
            throw new RuntimeException('Lock not acquired');
        }

        if (!flock($this->fp, LOCK_UN)) {
            fclose($this->fp);
            throw new RuntimeException('Failed to unlock');
        }

        $this->locked = false;
        fclose($this->fp);
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

        $this->fp = fopen($this->path, 'c');
        if (!$this->fp) {
            throw new RuntimeException('Failed to open lock file');
        }

        if (flock($this->fp, LOCK_EX | LOCK_NB)) {
            $this->locked = true;
            return true;
        }

        $this->locked = false;
        fclose($this->fp);
        return false;
    }
}
