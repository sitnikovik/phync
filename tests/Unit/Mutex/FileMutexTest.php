<?php

namespace Sitnikovik\Phync\Test\Unit\Mutex;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Sitnikovik\Phync\Mutex\FileMutex;

/**
 * Test for FileMutex class.
 */
final class FileMutexTest extends TestCase
{
    /**
     * The path to the lock file used for testing.
     *
     * @var string
     */
    private string $lockFilePath;

    /** 
     * Setups the test environment.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        $this->lockFilePath = sprintf(
            '%s/mutex-%s.lock',
            sys_get_temp_dir(),
            uniqid('', true)
        );
    }

    /**
     * Test the lock method throwns an exception on double lock.
     *
     * @return void
     */
    public function testLockThrowsExceptionOnDoubleLock(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Lock already acquired');

        $mutex = new FileMutex($this->lockFilePath);

        $mutex->lock();
        $mutex->lock();
    }

    /**
     * Test the unlock method throws an exception on double unlock.
     *
     * @return void
     */
    public function testUnlockThrowsExceptionOnDoubleUnlock(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Lock not acquired');

        $mutex = new FileMutex($this->lockFilePath);

        $mutex->lock();
        $mutex->unlock();
        $mutex->unlock();
    }

    /**
     * Test the tryLock method returns true on first lock attempt.
     *
     * @return void
     */
    public function testTryLockReturnsTrueOnFirstLockAttempt(): void
    {
        $mutex = new FileMutex($this->lockFilePath);

        $this->assertTrue($mutex->tryLock());
    }

    /**
     * Test the tryLock method returns false on second lock attempt.
     *
     * @return void
     */
    public function testTryLockReturnsFalseWhenLockIsAlreadyAcquired(): void
    {
        $mutex = new FileMutex($this->lockFilePath);

        $this->assertTrue($mutex->tryLock());
        $this->assertFalse($mutex->tryLock());
    }
}
