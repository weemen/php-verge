<?php

declare(strict_types=1);

namespace VergeCurrency\VergeClient;

use VergeCurrency\VergeClient\Adapter\AdapterInterface;
use VergeCurrency\VergeClient\Exception\InvalidVergeAccountException;

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
        if (false === $this->isValidVergeAddress($address)) {
            throw new InvalidVergeAccountException(
                'Address: '.$address.' is not a valid verge address!'
            );
        }

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
        if (false === $this->isValidVergeAddress($address)) {
            throw new InvalidVergeAccountException(
                'Cannot associate address to account: '.$address.' is not a valid verge address!'
            );
        }

        if (false === $this->isValidVergeAccount($account)) {
            throw new InvalidVergeAccountException('Cannot associate address to account, account: '.$account.' does not exist');
        }

        return $this->adapter->setaccount($address, $account);
    }


    /**
     * Get balance for given account
     *
     * @param string $account account name
     * @return float account balance
     */
    public function getBalance(string $source_account, float $minconf = 1.0)
    {
        if (false === $this->isValidVergeAccount($source_account)) {
            throw new InvalidVergeAccountException('Source account: '.$source_account.' does not exist');
        }

        return $this->adapter->getbalance($source_account, $minconf);
    }


    /**
     * Move coins from one account on wallet to another
     * Both accounts are local to this verged instance
     *
     * @param string $source_account account moving from
     * @param string $destination_account account moving to
     * @param float $amount amount of coins to move
     * @return
     */
    public function move(string $source_account, string $destination_account, float $amount)
    {
        if (false === $this->isValidVergeAccount($source_account)) {
            throw new InvalidVergeAccountException("Source account: ".$source_account." does not exist");
        }

        if (false === $this->isValidVergeAccount($destination_account)) {
            throw new InvalidVergeAccountException("Destination account: ".$destination_account." does not exist");
        }

        return $this->adapter->move($source_account, $destination_account, $amount);
    }


    /**
     * Send coins to any VERGE Address
     *
     * @param string $source_account account sending coins from
     * @param string $destination_address VERGE address sending to
     * @param float $amount amount of coins to send
     * @return string txid
     */
    public function send(string $source_account, string $destination_address, float $amount)
    {
        if (false === $this->isValidVergeAccount($source_account)) {
            throw new InvalidVergeAccountException('Source account: '.$source_account.' does not exist');
        }

        if (false === $this->isValidVergeAddress($destination_address)) {
            throw new InvalidVergeAccountException(
                'Destination address: '.$destination_address.' is not a valid verge address!'
            );
        }

        $txid = $this->adapter->sendfrom($source_account, $destination_address, $amount);
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

    /**
     * @param string $account
     */
    private function isValidVergeAccount(string $account)
    {
        return (array_key_exists($account, $this->adapter->listaccounts()));
    }

    /**
     * validate verge address
     * @param string $address
     *
     * @return bool
     */
    private function isValidVergeAddress(string $address)
    {
        $validation = $this->validateAddress($address);
        return ($validation['isvalid']) ? true : false;
    }
}
