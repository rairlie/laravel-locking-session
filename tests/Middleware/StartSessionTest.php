<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rairlie\LockingSession\Middleware\StartSession;
use Illuminate\Session\SessionManager;
use Illuminate\Session\CookieSessionHandler;
use Illuminate\Session\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler as SymfonyNullSessionHandler;
use Rairlie\LockingSession\Store as LockingSessionStore;
use Illuminate\Session\Store;
use Rairlie\LockingSession\LockingSessionHandler;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;

final class StartSessionTest extends TestCase
{
    /**
     * Test that the usingCookieSessions method returns the expected
     * result for different session handlers
     *
     * @dataProvider getSessionHandlersThatUseCookies
     * @dataProvider getSessionHandlersThatDontUseCookies
     */
    public function testUsingCookieSessionsMethod($sessionHandler, $expectedResult)
    {
        $startSession = $this->createStartSession($sessionHandler);
        $method = $this->makeMethodPublic($startSession, 'usingCookieSessions');

        $this->assertEquals($expectedResult, $method->invoke($startSession));
    }

    public function getSessionHandlersThatUseCookies()
    {
        return [[
            new LockingSessionStore(
                'name',
                new CookieSessionHandler($this->createMock(CookieJar::class), 0)
            ),
            true
        ]];
    }

    public function getSessionHandlersThatDontUseCookies()
    {
        return [[
            new LockingSessionStore('name', new NullSessionHandler),
            false,
        ], [
            new LockingSessionStore('name', new SymfonyNullSessionHandler),
            false,
        ], [
            null,
            false,
        ], [
            new Store('name', new SymfonyNullSessionHandler),
            false,
        ], [
            new Store('name', new NullSessionHandler),
            false,
        ]];
    }

    private function createStartSession(Store $store = null)
    {
        $manager = $this->createMock(SessionManager::class);

        $manager
             ->method('getSessionConfig')
             ->willReturn([
                 'driver' => $store ? 'foo' : null,
             ]);

        $manager
             ->method('driver')
             ->willReturn($store);

        return new StartSession($manager);
    }

    private function makeMethodPublic($object, string $methodName)
    {
        $reflection = new ReflectionObject($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
