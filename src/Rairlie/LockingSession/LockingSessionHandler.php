<?php
namespace Rairlie\LockingSession;

use SessionHandlerInterface;
use Illuminate\Session\CookieSessionHandler;
use Symfony\Component\Finder\Finder;

class LockingSessionHandler implements SessionHandlerInterface
{

    const LOCKDIR_NAME = 'sessionlocks';

    protected $lockPath;

    protected $lockFP;

    public function __construct(SessionHandlerInterface $handler)
    {
        $this->handler = $handler;

        $this->lockPath = sys_get_temp_dir() . '/' . self::LOCKDIR_NAME . '/';
        if (!is_dir($this->lockPath)) {
            mkdir($this->lockPath, 0744, true);
        }
    }

    public function close()
    {
        $this->unlockSession();
        return $this->handler->close();
    }

    public function destroy($session_id)
    {
        return $this->handler->destroy($session_id);
    }

    public function gc($maxlifetime)
    {
        $this->gcLockfiles($maxlifetime);
        return $this->handler->gc($maxlifetime);
    }

    public function open($save_path, $name)
    {
        return $this->handler->open($save_path, $name);
    }

    public function read($session_id)
    {
        $this->lockSession($session_id);
        return $this->handler->read($session_id);
    }

    public function write($session_id, $session_data)
    {
        $this->lockSession($session_id);
        $result = $this->handler->write($session_id, $session_data);
        $this->unlockSession($session_id);
        return $result;
    }

    protected function lockSession($session_id)
    {
        if ($this->lockFP) {
            return;
        }

        $this->lockFP = fopen($this->lockPath . $session_id, 'w+');
        flock($this->lockFP, LOCK_EX);
    }

    protected function unlockSession($session_id)
    {
        $result = flock($this->lockFP, LOCK_UN);
        fclose($this->lockFP);
        $this->lockFP = null;
    }

    protected function gcLockfiles($maxlifetime)
    {
        $files = Finder::create()
                    ->in($this->lockPath)
                    ->files()
                    ->ignoreDotFiles(true)
                    ->date('<= now - '.$maxlifetime.' seconds');

        foreach ($files as $file) {
            unlink($file->getRealPath());
        }
    }

    public function needsRequest()
    {
        return $this->handler instanceof CookieSessionHandler;
    }

    /**
     * Route any other methods through to the real session handler (e.g. setRequest)
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->handler, $name], $arguments);
    }

}
