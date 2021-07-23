<?php
namespace Rairlie\LockingSession;

use Illuminate\Session\EncryptedStore as BaseEncryptedStore;

class EncryptedStore extends BaseEncryptedStore
{
    /**
     * Create a new session instance.
     *
     * @param  string $name
     * @param  \SessionHandlerInterface $realHandler
     * @param  \Illuminate\Contracts\Encryption\Encrypter $encrypter
     * @param  string|null $lockfileDir
     * @return void
     */
    public function __construct($name, $realHandler, $encrypter, $lockfileDir = null)
    {
        $lockingSessionHandler = new LockingSessionHandler($realHandler, $lockfileDir);

        parent::__construct($name, $lockingSessionHandler, $encrypter);
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
