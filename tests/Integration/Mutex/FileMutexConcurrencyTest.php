<?php

namespace Sitnikovik\Phync\Test\Integration\Mutex;

use RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * Test for FileMutex class.
 * 
 * This test checks the functionality of the FileMutex class by simulating
 * concurrent access to a shared file resource. It ensures that the mutex
 * correctly protects the shared
 */
final class FileMutexConcurrencyTest extends TestCase
{
    /**
     * The temporary directory for the test.
     * 
     * @var string
     */
    private string $tmpDir;
    
    /**
     * The path to the counter file used for testing.
     * 
     * @var string
     */
    private string $counterFile;

    /**
     * The path to the script that increments the counter.
     * 
     * @var string
     */
    private string $script;

    /**
     * Set up the temporary directory and files for the test.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/phync_test_' . uniqid();
        mkdir($this->tmpDir);

        $this->counterFile = $this->tmpDir . '/counter.txt';
        file_put_contents($this->counterFile, '0');

        $roodDir = __DIR__ . '/../../..';
        $this->script = $roodDir . '/scripts/increment_with_mutex.php';
        if (!file_exists($this->script)) {
            throw new RuntimeException("Script not found: {$this->script}");
        }
    }

    /**
     * Clean up the temporary directory and files after the test.
     * 
     * @return void
     */
    protected function tearDown(): void
    {
        if (file_exists($this->counterFile)) {
            unlink($this->counterFile);
        }

        $lockFile = sys_get_temp_dir() . '/mutex.lock';
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        if (file_exists($this->tmpDir)) {
            rmdir($this->tmpDir);
        }
    }

    /**
     * Test that the mutex protects the shared file from
     * 
     * @return void
     */
    public function testMutexProtectsSharedFileFromRaceCondition(): void
    {
        $children = [];

        for ($i = 0; $i < 100; $i++) {
            $children[] = popen("php {$this->script} {$this->counterFile}", 'r');
        }

        foreach ($children as $child) {
            pclose($child);
        }

        $final = (int)file_get_contents($this->counterFile);
        $this->assertSame(100, $final, "Expected counter to be 100, got $final");
    }
}