<?php
/**
 * Class implementing an exclusive lock.
 */
namespace Rairlie\LockingSession;

use Symfony\Component\Finder\Finder;
use UnderflowException;
use Log;

class Lock
{

    protected $lockfilepath;
    protected $lockfp;
    protected $debug = false;

    const LOCKDIR_NAME = 'sessionlocks';

    /**
     * Constructor
     *
     * @param  string  $subject  ID of subject to lock, e.g. session ID
     */
    public function __construct($subject)
    {
        $lockName = preg_replace('{/}', '_', $subject); // Make safe for filesystem
        $this->lockfilepath = $this->getLockDir() . $lockName;
    }

    public function __destruct()
    {
        if ($this->lockfp) {
            $this->release();
        }
    }

    /**
     * Acquire an exclusive lock. Will block if locked by another process.
     */
    public function acquire()
    {
        if ($this->lockfp) {
            $this->log("acquire - existing lock");
            return;
        }

        $this->openLockFile();
        $this->log("acquire - try lock");
        flock($this->lockfp, LOCK_EX);
        $this->log("acquire - got lock");
    }

    /**
     * Release an acquired lock
     */
    public function release()
    {
        if (!$this->lockfp) {
            throw new UnderflowException("No lock to release");
        }

        $this->log("release");
        flock($this->lockfp, LOCK_UN);
        $this->closeLockFile();
    }

    /**
     * Garbage collect the lock dir. Any locks older than $maxlifetime will be removed.
     */
    public function gcLockDir($maxlifetime)
    {
        $this->log('gc');

        $files = Finder::create()
                    ->in($this->getLockDir())
                    ->files()
                    ->ignoreDotFiles(true)
                    ->date('<= now - '.$maxlifetime.' seconds');

        foreach ($files as $file) {
            $this->log('gc '. $file->getRealPath());
            unlink($file->getRealPath());
        }
    }

    protected function getLockDir()
    {
        return sys_get_temp_dir() . '/' . self::LOCKDIR_NAME . '/';
    }

    /**
     * Open the lock file on disk, creating it if it doesn't exist
     */
    protected function openLockFile()
    {
        if (!is_dir(dirname($this->lockfilepath))) {
            mkdir(dirname($this->lockfilepath), 0744, true);
        }
        $this->lockfp = fopen($this->lockfilepath, 'w+');
    }

    /**
     * Close the lock file on disk, but don't unlink it; another process may be holding the
     * fd and unlinking would make it appear that the lock doesn't exist.
     * Stale locks will be tidied up via the garbage collection method.
     */
    protected function closeLockFile()
    {
        fclose($this->lockfp);
        $this->lockfp = null;
    }

    /**
     * Log a debug diagnostic message, if enabled
     */
    protected function log($message)
    {
        $this->debug && Log::info($this->lockfilepath . ' ' . getmypid() . ' ' . $message);
    }

}
