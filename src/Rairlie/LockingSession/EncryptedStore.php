<?php
namespace Rairlie\LockingSession;

use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Session\EncryptedStore as BaseEncryptedStore;
use SessionHandlerInterface;

class EncryptedStore extends BaseEncryptedStore
{
    /**
     * Create a new session instance.
     *
     * @param  string $name
     * @param  SessionHandlerInterface $handler
     * @param  EncrypterContract $encrypter
     * @param  string|null $id
     * @param  string|null $lockfileDir
     * @return void
     */
    public function __construct(
        $name,
        SessionHandlerInterface $handler,
        EncrypterContract $encrypter,
        $id = null,
        $lockfileDir = null
    ) {
        $lockingSessionHandler = new LockingSessionHandler($handler, $lockfileDir);

        parent::__construct($name, $lockingSessionHandler, $encrypter, $id);
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
