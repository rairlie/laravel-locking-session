<?php
namespace Rairlie\LockingSession\Middleware;

use Illuminate\Session\Middleware\StartSession as BaseStartSession;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

class StartSession extends BaseStartSession
{

    /**
     * Override so we ask the handler if its using cookies (rather than inferring
     * from the class instance)
     */
    protected function usingCookieSessions()
    {
        if (! $this->sessionConfigured()) {
            return false;
        }

        $handler = $this->manager->driver()->getHandler();

        if ($handler instanceOf NullSessionHandler) {
            return false;
        }

        return $handler->usingCookieSessions();
    }

}
