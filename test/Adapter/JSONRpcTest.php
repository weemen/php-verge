<?php

namespace VergeCurrency\VergeClient\Adapter;


use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use VergeCurrency\VergeClient\Exception\ConnectionExcpetion;
use VergeCurrency\VergeClient\Exception\RuntimeException;

class JSONRpcTest extends TestCase
{
    /**
     * @test
     * @dataProvider methodProvider
     */
    public function OnlyAllowedMethodsCanBeCalled($method, $valid)
    {
        $logger  = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $jsonRpc = new JSONRpc('https://foo.bar', $logger);

        try {
            $jsonRpc->{$method}([]);
        } catch (ConnectionExcpetion $e) {
            $this->assertTrue($valid);
        } catch (RuntimeException $e) {
            $this->assertFalse($valid);
        } catch (\Throwable $e) {
            $this->assertFalse($valid);
        }
    }

    /**
     * @return array
     */
    public function methodProvider()
    {
        return [
          ["getaccount", true],
          ["getaddress", true],
          ["getbalance", true],
          ["getnewaddress", true],
          ["gettransaction", true],
          ["listaccounts", true],
          ["move", true],
          ["send", true],
          ["setaccount", true],
          ["validateaddress", true],
          ["someothermethod", false],
          [12, false],
          [1.1, false],
          [[], false],
          [new \stdClass(), false],
        ];
    }
}
