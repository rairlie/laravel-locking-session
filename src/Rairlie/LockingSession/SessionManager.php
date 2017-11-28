<?php
namespace Rairlie\LockingSession;

use Illuminate\Session\SessionManager as BaseSessionManager;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

class SessionManager extends BaseSessionManager
{

    /**
     * Override to return our own Store/Encrypted store
     */
    protected function buildSession($handler)
    {
        if ($handler instanceOf NullSessionHandler) {
            return parent::buildSession($handler);
        }

        if ($this->app['config']['session.encrypt']) {
            return new EncryptedStore(
                $this->app['config']['session.cookie'],
                $handler,
                $this->app['encrypter'],
                $this->app['config']['session.lockfile_dir']
            );
        } else {
            return new Store(
                $this->app['config']['session.cookie'],
                $handler,
                null,
                $this->app['config']['session.lockfile_dir']
            );
        }
    }

}
