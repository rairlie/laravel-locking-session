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

        if ($this->container['config']['session.encrypt']) {
            return new EncryptedStore(
                $this->container['config']['session.cookie'],
                $handler,
                $this->container['encrypter'],
                $this->container['config']['session.lockfile_dir']
            );
        } else {
            return new Store(
                $this->container['config']['session.cookie'],
                $handler,
                null,
                $this->container['config']['session.lockfile_dir']
            );
        }
    }

}
