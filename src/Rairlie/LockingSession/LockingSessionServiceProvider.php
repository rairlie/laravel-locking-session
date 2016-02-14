<?php
namespace Rairlie\LockingSession;

use Illuminate\Session\SessionServiceProvider;

class LockingSessionServiceProvider extends SessionServiceProvider
{

    protected function registerSessionManager()
    {
        $this->app->singleton('session', function ($app) {
            return new SessionManager($app);
        });
    }

}
