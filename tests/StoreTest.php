<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rairlie\LockingSession\Store as LockingSessionStore;
use Illuminate\Session\CookieSessionHandler;
use Illuminate\Session\NullSessionHandler;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler as SymfonyNullSessionHandler;

final class StoreTest extends TestCase
{
    /**
     * @dataProvider getSessionHandlers
     */
    public function testHandlerNeedsRequest($sessionHandler, $expectedResult)
    {
        $store = new LockingSessionStore('name', $sessionHandler);

        $this->assertEquals($expectedResult, $store->handlerNeedsRequest());
    }

    public function getSessionHandlers()
    {
        return [[
            new CookieSessionHandler($this->createMock(CookieJar::class), 0),
            true,
        ], [
            new NullSessionHandler,
            false,
        ]];
    }
}
