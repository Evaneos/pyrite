<?php
/** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Pyrite\Response;

use PHPUnit\Framework\TestCase;
use Pyrite\Errors\ErrorSubscriber;
use Symfony\Component\HttpFoundation\Response;

final class ResponseBagImplTest extends TestCase
{

    /**
     * @test
     * @group        response
     *
     * @dataProvider validTypesProvider
     */
    public function it_sets_a_valid_type($type)
    {
        $bag = new ResponseBagImpl();
        self::assertEquals(ResponseBag::TYPE_DEFAULT, $bag->getType());

        $bag->setType($type);
        self::assertEquals($type, $bag->getType());
    }

    /**
     * @return array
     */
    public function validTypesProvider()
    {
        return [
            'default'  => [ResponseBag::TYPE_DEFAULT],
            'streamed' => [ResponseBag::TYPE_STREAMED],
            'binary'   => [ResponseBag::TYPE_BINARY]
        ];
    }

    /**
     * @test
     * @group response
     */
    public function it_fails_setting_an_invalid_type()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $bag = new ResponseBagImpl();
        $bag->setType('FakeType');
    }

    /**
     * @test
     * @group response
     */
    public function it_sets_a_value()
    {
        $key   = 'home';
        $value = new ErrorSubscriber();

        $bag = new ResponseBagImpl();
        $bag->set($key, $value);
        self::assertEquals($value, $bag->get($key));
    }

    /**
     * @test
     * @group response
     */
    public function it_returns_the_default_value_if_key_does_not_exist()
    {
        $defaultValue = 'test';

        $bag = new ResponseBagImpl();
        self::assertEquals($defaultValue, $bag->get('fakeKey', $defaultValue));
    }

    /**
     * @test
     * @group response
     */
    public function it_retrieves_all_data()
    {
        $keys = ['value1', 'value2', 'value3'];

        $bag = new ResponseBagImpl();
        foreach ($keys as $key) {
            $bag->set($key, $key);
        }

        $all = $bag->getAll();
        self::assertCount(count($keys), $all);
        foreach ($keys as $key) {
            self::assertContains($key, $all);
        }
    }

    /**
     * @test
     * @group response
     */
    public function it_has_a_valid_key()
    {
        $keys = ['value1', 'value2', 'value3'];

        $bag = new ResponseBagImpl();
        foreach ($keys as $key) {
            $bag->set($key, $key);
            self::assertTrue($bag->has($key));
        }
    }

    /**
     * @test
     * @group response
     */
    public function it_has_not_a_missing_key()
    {
        $bag = new ResponseBagImpl();
        self::assertFalse($bag->has('fake'));
    }

    /**
     * @test
     * @group response
     */
    public function it_adds_headers()
    {
        $headers = ['header-1', 'header-2', 'header-3', 'header-4'];

        $bag = new ResponseBagImpl();
        self::assertEmpty($bag->getHeaders());
        foreach ($headers as $header) {
            $bag->addHeader($header, $header);
        }

        $all = $bag->getHeaders();
        self::assertCount(count($headers), $all);
        foreach ($headers as $header) {
            self::assertContains($header, $all);
        }
    }

    /**
     * @test
     * @group response
     */
    public function it_replaces_header_when_already_there()
    {
        $firstLocation = 'http://www.evaneos.fr/';
        $newLocation   = 'http://www.evaneos.fr/new/home/';

        $bag = new ResponseBagImpl();
        $bag->addHeader('Location', $firstLocation);
        self::assertEquals($firstLocation, $bag->getHeaders()['Location']);

        $bag->addHeader('Location', $newLocation);
        self::assertEquals($newLocation, $bag->getHeaders()['Location']);
    }

    /**
     * @test
     * @group response
     */
    public function it_sets_a_result_code()
    {
        $code = Response::HTTP_NOT_FOUND;

        $bag = new ResponseBagImpl();
        self::assertEquals(Response::HTTP_OK, $bag->getResultCode());

        $bag->setResultCode($code);
        self::assertEquals($code, $bag->getResultCode());
    }

    /**
     * @test
     * @group response
     */
    public function it_sets_result()
    {
        $results = 'success';

        $bag = new ResponseBagImpl();
        self::assertEmpty($bag->getResult());

        $bag->setResult($results);
        self::assertEquals($results, $bag->getResult());
    }

    /**
     * @test
     * @group response
     */
    public function it_sets_a_callback()
    {
        $callback = function () {
            return true;
        };

        $bag = new ResponseBagImpl();
        self::assertNull($bag->getCallback());

        $bag->setCallback($callback);
        self::assertEquals($callback, $bag->getCallback());
    }
}
