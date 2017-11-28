<?php
namespace Rairlie\LockingSession;

use SessionHandlerInterface;
use Illuminate\Session\CookieSessionHandler;
use Illuminate\Session\ExistenceAwareInterface;
use Rairlie\LockingSession\Lock;

class LockingSessionHandler implements SessionHandlerInterface, ExistenceAwareInterface
{

    protected $realHandler;
    protected $lock;
    protected $lockfileDir;

    public function __construct(SessionHandlerInterface $realHandler, $lockfileDir)
    {
        $this->realHandler = $realHandler;
        $this->lockfileDir = $lockfileDir;
    }

    public function close()
    {
        return $this->realHandler->close();
    }

    public function destroy($session_id)
    {
        return $this->realHandler->destroy($session_id);
    }

    public function gc($maxlifetime)
    {
        $dummy = new Lock('dummy', $this->lockfileDir);
        $dummy->gcLockDir($maxlifetime);

        return $this->realHandler->gc($maxlifetime);
    }

    public function open($save_path, $name)
    {
        return $this->realHandler->open($save_path, $name);
    }

    public function read($session_id)
    {
        // Lock the session before reading and hold the lock
        $this->acquireLock($session_id);
        return $this->realHandler->read($session_id);
    }

    public function write($session_id, $session_data)
    {
        $this->acquireLock($session_id);
        $result = $this->realHandler->write($session_id, $session_data);
        $this->releaseLock();
        return $result;
    }

    public function needsRequest()
    {
        return $this->usingCookieSessions();
    }

    public function usingCookieSessions()
    {
        return $this->realHandler instanceof CookieSessionHandler;
    }

    /**
     * Route any other methods through to the real session realHandler (e.g. setRequest)
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->realHandler, $name], $arguments);
    }

    protected function acquireLock($id)
    {
        if (!$this->lock) {
            $this->lock = new Lock($id, $this->lockfileDir);
        }
        $this->lock->acquire();
    }

    protected function releaseLock()
    {
        $this->lock->release();
        $this->lock = null;
    }

    /**
     * Set the existence of the session on the handler if applicable.
     *
     * @param  bool  $value
     * @return void
     */
    public function setExists($value)
    {
        if ($this->realHandler instanceof ExistenceAwareInterface) {
            $this->realHandler->setExists($value);
        }
    }

}
