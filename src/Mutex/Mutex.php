<?php

/*
 * (c) Ilya Sitnikov <sitnikovik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sitnikovik\Phync\Mutex;

/**
 * A mutex (short for mutual exclusion) is a synchronization primitive used to control access 
 * to a shared resource in concurrent environments. 
 * 
 * It ensures that only one process or thread can access the critical section of code 
 * or resource at a time, preventing race conditions and inconsistent state.
 */
interface Mutex {

    /**
     * Blocks for the writing.
     * 
     * Writers must wait until all readers are done and no other writers are active.
     * 
     * @return void
     */
    public function lock(): void;
    
    /**
     * Unlocks the mutex.
     * 
     * @return void
     */
    public function unlock(): void;

    /**
     * Tries to acquire the lock without blocking.
     * 
     * @return bool True if the lock was acquired, false otherwise.
     */
    public function tryLock(): bool;
}