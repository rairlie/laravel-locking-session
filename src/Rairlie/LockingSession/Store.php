<?php
namespace Rairlie\LockingSession;

use Illuminate\Session\Store as BaseStore;
use SessionHandlerInterface;

class Store extends BaseStore
{
    /**
     * Create a new session instance.
     *
     * @param  string $name
     * @param  SessionHandlerInterface $realHandler
     * @param  string|null $id
     * @param  string|null $lockfileDir
     * @return void
     */
    public function __construct($name, $realHandler, $id = null, $lockfileDir = null)
    {
        $lockingSessionHandler = new LockingSessionHandler($realHandler, $lockfileDir);

        parent::__construct($name, $lockingSessionHandler, $id);
    }

    public function handlerNeedsRequest()
    {
        return $this->handler->needsRequest();
    }

    /**
     * Save the session data to storage.
     *
     * To more accurately emulate native php session locking, a session should only be written to after it has been started
     * and should not be written to after it has been closed.
     *
     * @return void
     */
    public function save()
    {
        if ($this->started) {
            parent::save();
        }
    }
}
