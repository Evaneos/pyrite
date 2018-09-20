<?php
/** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Pyrite\Factory;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pyrite\Container\Container;
use Pyrite\Stack\Session as StackSession;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class SessionTest extends TestCase
{
    /**
     * @return Session
     */
    private function getSessionFactory()
    {
        /** @var Container|MockObject $container */
        $container = $this->getMockForAbstractClass(Container::class);
        $container->method('getParameter')
                  ->with('cookie')
                  ->willReturn([]);

        return new Session($container);
    }

    /**
     * @return HttpKernelInterface|MockObject
     */
    private function getKernelApp()
    {
        return $this->getMockForAbstractClass(HttpKernelInterface::class);
    }

    /**
     * @test
     * @group session
     */
    public function it_registers_a_session()
    {
        $expectedKernelName = 'pyKer';

        list($kernelName, $session) = $this->getSessionFactory()->register($this->getKernelApp(), $expectedKernelName);

        self::assertEquals($expectedKernelName, $kernelName);
        self::assertInstanceOf(StackSession::class, $session);
    }

    /**
     * @test
     * @group session
     */
    public function it_registers_a_started_session()
    {
        $expectedKernelName = 'pyKer';

        $parameters = ['start' => true];
        list($kernelName, $session) = $this->getSessionFactory()
                                           ->register($this->getKernelApp(), $expectedKernelName, $parameters);

        self::assertEquals($expectedKernelName, $kernelName);
        self::assertInstanceOf(StackSession::class, $session);
    }

    /**
     * @test
     * @group session
     */
    public function it_fails_when_no_app_provided()
    {
        $this->setExpectedException(\RuntimeException::class);

        $this->getSessionFactory()->register(null);
    }

}
