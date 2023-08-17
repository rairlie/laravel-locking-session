<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rairlie\LockingSession\Lock;

final class LockTest extends TestCase
{
    public function testItGarbageCollectsTheLockDir(): void
    {
        $path = sys_get_temp_dir() . '/laravel-locking-session-test';
        $lockfile = $path . '/lock_1';

        @mkdir($path);
        touch($lockfile);

        $lock = new Lock('test', $path);
        $lock->gcLockDir(0);

        $this->assertDirectoryExists($path);
        $this->assertFileNotExists($lockfile);
    }

    /**
     * Should be no error calling gcLockDir when dir doesn't exist
     */
    public function testGcLockDirNotExist(): void
    {
        $path = '/path/does/not/exist';

        $lock = new Lock('test', $path);
        $lock->gcLockDir(0);

        $this->assertDirectoryNotExists($path);
    }
}
