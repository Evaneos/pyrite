<?php
/** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Pyrite\Config;

use PHPUnit\Framework\TestCase;

final class NullConfigTest extends TestCase
{
    /**
     * @test
     * @group config
     */
    public function it_loads_an_empty_configuration()
    {
        $config = new NullConfig();

        self::assertEmpty($config->load(true));
    }
}
