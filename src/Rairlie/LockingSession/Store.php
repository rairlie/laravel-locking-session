<?php
namespace Rairlie\LockingSession;

use Illuminate\Session\Store as BaseStore;

class Store extends BaseStore
{

    public function __construct($name, $actualHandler, $id = null)
    {
        $lockingSessionHandler = new LockingSessionHandler($actualHandler);

        return parent::__construct($name, $lockingSessionHandler, $id);
    }

    public function handlerNeedsRequest()
    {
        return $this->handler->needsRequest();
    }

}
