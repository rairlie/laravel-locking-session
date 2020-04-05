<?php
namespace Rairlie\LockingSession;

use Illuminate\Session\Store as BaseStore;

class Store extends BaseStore
{

    public function __construct($name, $realHandler, $id = null, $lockfileDir = null)
    {
        $lockingSessionHandler = new LockingSessionHandler($realHandler, $lockfileDir);

        return parent::__construct($name, $lockingSessionHandler, $id);
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

    /**
     * Load the session data from the handler.
     *
     * To more accurately emulate native php when loading the session, existing data should be flushed before loading data from session store.
     *
     * @return void
     */
    protected function loadSession()
    {
        $this->flush();
        parent::loadSession();
    }
}
