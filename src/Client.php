<?php

declare(strict_types=1);

namespace VergeCurrency\VergeClient;

use VergeCurrency\VergeClient\Adapter\AdapterInterface;

class Client
{

    private $adapter;

    /**
     * Create client to conncet on init
     * @param array            $config
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Creates or Retrieves a VERGE address for a account name
     * An account is just a string used as key to identify account,
     * A VERGE address is returned which can receive coins
     *
     * @param string $account some string used as key to account
     * @return string VERGE address
     */
    public function getAddress(string $account)
    {
        return $this->adapter->getaccountaddress($account);
    }


    /**
     * Given a VERGE address returns the account name
     *
     * @param string $address VERGE addresss
     * @return string account name key
     */
    public function getAccount(string $address)
    {
        return $this->adapter->getaccount($address);
    }


    /**
     * Create new address for account, recommended to include
     * account name for further API use.
     *
     * @param string $account account name
     * @return string verge address
     */
    public function getNewAddress(string $account = '')
    {
        return $this->adapter->getnewaddress($account);
    }


    /**
     * Get list of all accounts on in this verged wallet
     *
     * @return array strings of account => balance
     */
    public function listAccounts()
    {
        return $this->adapter->listaccounts();
    }

    /**
     * Get the details of a transaction
     *
     * @param string $txid transaction id
     * @return array describing the transaction
     */
    public function getTransaction(string $txid)
    {
        return $this->adapter->gettransaction($txid);
    }

    /**
     * Associate verge address to account string
     *
     * @param string $address verge address
     * @param string $account account string
     */
    public function setAccount(string $address, string $account)
    {
        return $this->adapter->setaccount($address, $account);
    }


    /**
     * Get balance for given account
     *
     * @param string $account account name
     * @return float account balance
     */
    public function getBalance(string $account, float $minconf = 1.0)
    {
        return $this->adapter->getbalance($account, $minconf);
    }


    /**
     * Move coins from one account on wallet to another
     * Both accounts are local to this verged instance
     *
     * @param string $account_from account moving from
     * @param string $account_to account moving to
     * @param float $amount amount of coins to move
     * @return
     */
    public function move(string $account_from, string $account_to, float $amount)
    {
        return $this->adapter->move($account_from, $account_to, $amount);
    }


    /**
     * Send coins to any VERGE Address
     *
     * @param string $account account sending coins from
     * @param string $to_address VERGE address sending to
     * @param float $amount amount of coins to send
     * @return string txid
     */
    public function send(string $account, string $to_address, float $amount)
    {
        $txid = $this->adapter->sendfrom($account, $to_address, $amount);
        return $txid;
    }

    /**
     * Validate a given VERGE Address
     * @param string $address to validate
     * @return array with the properties of the address
     */
    public function validateAddress(string $address)
    {
        return $this->adapter->validateaddress($address);
    }
}
