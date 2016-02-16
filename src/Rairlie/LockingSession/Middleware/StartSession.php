<?php
namespace Rairlie\LockingSession\Middleware;

use Illuminate\Session\Middleware\StartSession as BaseStartSession;

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

        return $this->manager->driver()->getHandler()->usingCookieSessions();
    }

}
