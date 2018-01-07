<?php

namespace VergeCurrency\VergeClient;


use PHPUnit\Framework\TestCase;
use VergeCurrency\VergeClient\Adapter\AdapterInterface;
use VergeCurrency\VergeClient\Exception\InvalidVergeAccountException;

class ClientTest extends TestCase
{

    /**
     * @test
     */
    public function ItCanNotMoveVergeFromNonExistingSourceAccount()
    {
        $adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['listaccounts','move'])
            ->getMock();

        $adapterInterface->expects($this->once())
            ->method('listaccounts')
            ->will($this->returnValue([]));

        $client = new Client($adapterInterface);
        $this->expectException(InvalidVergeAccountException::class);
        $this->expectExceptionMessage("Source account: some_source_account does not exist");

        $client->move('some_source_account', 'some_destination_account', 1000);
    }

    /**
     * @test
     */
    public function ItCanNotMoveVergeToNonExistingDestinationAccount()
    {
        $adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['listaccounts','move'])
            ->getMock();

        $adapterInterface->expects($this->exactly(2))
            ->method('listaccounts')
            ->will($this->returnValue(['some_source_account' => 1000]));

        $client = new Client($adapterInterface);
        $this->expectException(InvalidVergeAccountException::class);
        $this->expectExceptionMessage("Destination account: some_destination_account does not exist");

        $client->move('some_source_account', 'some_destination_account', 1000);
    }

    /**
     * @test
     */
    public function ItCanNotSendVergeFromNonExistingSourceAccount()
    {
        $adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['listaccounts','send'])
            ->getMock();

        $adapterInterface->expects($this->once())
            ->method('listaccounts')
            ->will($this->returnValue([]));

        $client = new Client($adapterInterface);
        $this->expectException(InvalidVergeAccountException::class);
        $this->expectExceptionMessage("Source account: some_source_account does not exist");

        $client->send('some_source_account', 'some_destination_address', 1000);
    }

    /**
     * @test
     */
    public function ItCanNotSendVergeToInvalidVergeAddress()
    {
        $adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['listaccounts','send', 'validateAddress'])
            ->getMock();

        $adapterInterface->expects($this->once())
            ->method('listaccounts')
            ->will($this->returnValue(['some_source_account' => 1000]));

        $adapterInterface->expects($this->once())
            ->method('validateAddress')
            ->will($this->returnValue(['isvalid' => null]));

        $client = new Client($adapterInterface);
        $this->expectException(InvalidVergeAccountException::class);
        $this->expectExceptionMessage('Destination address: some_destination_address is not a valid verge address!');

        $client->send('some_source_account', 'some_destination_address', 1000);
    }
}
