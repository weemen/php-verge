<?php

namespace VergeCurrency\VergeClient\Adapter;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use VergeCurrency\VergeClient\Exception\ConnectionExcpetion;
use VergeCurrency\VergeClient\Exception\ResponseException;
use VergeCurrency\VergeClient\Exception\RuntimeException;

class GuzzleTest extends TestCase
{
    /**
     * @test
     * @dataProvider providesValidMethods
     */
    public function ItCanCallAllowedMethods($method)
    {
        $client  = $this->getMockBuilder(Client::class)
            ->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        $client->expects($this->any())
            ->method('request')
            ->will($this->returnValue($response));

        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $guzzle = new Guzzle($client, 'https://foo.bar', $logger, 1);
        $guzzle->setRPCNotification(true);

        $guzzle->{$method}([]);
        try {
            $this->assertTrue($guzzle->{$method}([]));
        } catch (\Excpetion $e) {
            $this->fail(
                sprintf('Method "%s" is not a valid method')
            );
        }
    }

    /**
     * @return array
     */
    public function providesValidMethods()
    {
        return [
            ["getaccount"],
            ["getaddress"],
            ["getbalance"],
            ["getnewaddress"],
            ["gettransaction"],
            ["listaccounts"],
            ["move"],
            ["send"],
            ["setaccount"],
            ["validateaddress"],
        ];
    }

    /**
     * @test
     * @dataProvider providesInValidMethods
     */
    public function ItCannotCallDisallowedMethods($method)
    {
        $client  = $this->getMockBuilder(Client::class)->getMock();

        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $guzzle = new Guzzle($client, 'https://foo.bar', $logger, 1);

        try {
            $guzzle->{$method}([]);
            $this->fail(
                sprintf('Method "%s" is not a valid method')
            );
        } catch (RuntimeException $e) {
            $this->assertTrue(is_string($method));
        } catch (Throwable $e) {
            $this->assertFalse(is_string($method));
        }
    }

    /**
     * @return array
     */
    public function providesInValidMethods()
    {
        return [
            ["some_other_method"],
            [12],
            [1.1],
            [[]],
            [new \stdClass()]
        ];
    }

    /**
     * @test
     * @dataProvider providesHttpCodes
     */
    public function ItThrowsResponseExceptionOnHttpErrorCodes($HttpCode)
    {
        $client  = $this->getMockBuilder(Client::class)
            ->getMock();

        $request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue($HttpCode));

        $response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue('hello'));


        $client->expects($this->any())
            ->method('request')
            ->will($this->throwException(
                new ClientException('empty reply from server', $request, $response)
            ));

        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $guzzle = new Guzzle($client, 'https://foo.bar', $logger, 1);
        $guzzle->setRPCNotification(true);

        $this->expectException(ResponseException::class);
        $guzzle->listaccounts();
    }

    public function providesHttpCodes()
    {
        return [
            [400],
            [403],
            [500],
            [503]
        ];
    }

    /**
     * @test
     */
    public function ItCanThrowConnectionException()
    {
        $client  = $this->getMockBuilder(Client::class)
            ->getMock();

        $request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();

        $client->expects($this->any())
            ->method('request')
            ->will($this->throwException(
                new ConnectException('empty reply from server', $request)
            ));

        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $guzzle = new Guzzle($client, 'https://foo.bar', $logger, 1);
        $guzzle->setRPCNotification(true);

        $this->expectException(ConnectionExcpetion::class);
        $guzzle->listaccounts();
    }

    /**
     * @test
     */
    public function ItCanValidateResponse()
    {
        $client  = $this->getMockBuilder(Client::class)
            ->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        $response->expects($this->exactly(2))
            ->method('getBody')
            ->will($this->returnValue(
                json_encode([
                    "error" => NULL,
                    "id"    => 1,
                    "result" => "hello world"
                ])
            ));

        $client->expects($this->any())
            ->method('request')
            ->will($this->returnValue($response));

        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $guzzle = new Guzzle($client, 'https://foo.bar', $logger, 1);
        $guzzle->setRPCNotification(false);

        $this->assertEquals("hello world", $guzzle->listaccounts());
    }

    /**
     * @test
     */
    public function ItCanInvalidateResponseDueToMissingId()
    {
        $this->markTestIncomplete('Unclear what should happen in this case!');
        $client  = $this->getMockBuilder(Client::class)
            ->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        $response->expects($this->exactly(2))
            ->method('getBody')
            ->will($this->returnValue(
                json_encode([
                    "error" => NULL,
                    "id"    => 1,
                    "result" => "hello world"
                ])
            ));

        $client->expects($this->any())
            ->method('request')
            ->will($this->returnValue($response));

        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $guzzle = new Guzzle($client, 'https://foo.bar', $logger, 1);
        $guzzle->setRPCNotification(false);

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Incorrect response id (request id: 1, response id: ');
        $guzzle->listaccounts();
    }

    /**
     * @test
     */
    public function ItCanInvalidateResponseDueToIncorrectId()
    {
        $client  = $this->getMockBuilder(Client::class)
            ->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        $response->expects($this->exactly(2))
            ->method('getBody')
            ->will($this->returnValue(
                json_encode([
                    "error" => NULL,
                    "id"    => 2,
                    "result" => "hello world"
                ])
            ));

        $client->expects($this->any())
            ->method('request')
            ->will($this->returnValue($response));

        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $guzzle = new Guzzle($client, 'https://foo.bar', $logger, 1);
        $guzzle->setRPCNotification(false);

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Incorrect response id (request id: 1, response id: 2');
        $guzzle->listaccounts();
    }

    /**
     * @test
     */
    public function ItThrowsExceptionWhenResponseContainsError()
    {
        $client  = $this->getMockBuilder(Client::class)
            ->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        $response->expects($this->exactly(2))
            ->method('getBody')
            ->will($this->returnValue(
                json_encode([
                    "error" => NULL,
                    "id"    => 1,
                    "error" => "some error occured",
                    "result" => "hello world"
                ])
            ));

        $client->expects($this->any())
            ->method('request')
            ->will($this->returnValue($response));

        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $guzzle = new Guzzle($client, 'https://foo.bar', $logger, 1);
        $guzzle->setRPCNotification(false);

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Request error: some error occured');
        $guzzle->listaccounts();
    }
}
