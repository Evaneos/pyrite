<?php
/** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnhandledExceptionInspection */

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pyrite\Container\Container;
use Pyrite\Layer\Executor\Executable;
use Pyrite\Layer\ExecutorLayer;
use Pyrite\Response\ResponseBagImpl;
use Symfony\Component\HttpFoundation\Request;

final class ExecutorLayerTest extends TestCase
{
    /**
     * @test
     * @group layer
     */
    public function it_handles_nothing()
    {
        /** @var Executable|MockObject $executable */
        $executable = $this->getMockForAbstractClass(Executable::class);

        /** @var Container|MockObject $container */
        $container = $this->getMockForAbstractClass(Container::class);
        $container->method('get')
                  ->willReturn($executable);

        $layer = new ExecutorLayer($container);
        $layer->setConfiguration([
            'conf' => true
        ]);
        $layer->setRequest(new Request());

        $bag       = new ResponseBagImpl();
        $resultBag = $layer->handle($bag);

        self::assertEquals($bag, $resultBag);
    }
}
