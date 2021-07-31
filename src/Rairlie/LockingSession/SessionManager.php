<?php
namespace Rairlie\LockingSession;

use Illuminate\Session\SessionManager as BaseSessionManager;
use Illuminate\Session\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler as SymfonyNullHandler;

class SessionManager extends BaseSessionManager
{

    /**
     * Override to return our own Store/Encrypted store
     */
    protected function buildSession($handler)
    {
        if ($handler instanceOf NullSessionHandler || $handler instanceof SymfonyNullHandler) {
            return parent::buildSession($handler);
        }

        $container = isset($this->container) ? $this->container : $this->app;

        if ($container['config']['session.encrypt']) {
            return new EncryptedStore(
                $container['config']['session.cookie'],
                $handler,
                $container['encrypter'],
                $container['config']['session.lockfile_dir']
            );
        } else {
            return new Store(
                $container['config']['session.cookie'],
                $handler,
                null,
                $container['config']['session.lockfile_dir']
            );
        }
    }

}
