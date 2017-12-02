<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use VergeCurrency\VergeClient\Adapter\Guzzle;
use VergeCurrency\VergeClient\Client;

include_once '../vendor/autoload.php';

$userId = 1;

$logger  = new Logger('VergeClientLogger');
$logger->pushHandler(
    new StreamHandler('verge-client.log', Logger::DEBUG)
);

$guzzleClient = new \GuzzleHttp\Client();

$connect_string = sprintf(
    'http://%s:%s@%s:%s/',
    'leon',
    'weemen',
    '127.0.0.1',
    '20102'
);

$adapter = new Guzzle(
    new \GuzzleHttp\Client(),
    $connect_string,
    $logger,
    $userId
);

$client = new Client($adapter);
$client->list_accounts();


/*
## Simple command-line script to show examples
require "./verge.php";

$config = array(
    'user' => 'vergerpcuser',
    'pass' => 'rpcpassword',
    'host' => '127.0.0.1',
    'port' => '20102' );

// create client conncetion
$verge = new verge( $config );


// create a new address
$address = $verge->get_address( 'vergeDEV' );
print( "Address: $address \n" );

// list accounts in wallet
print_r( $verge->list_accounts() );

// get balance in wallet
print( "mkaz: " . $verge->get_balance( 'vergeDEV' ) );

// move money from accounts in wallet
// moves from 'account a' to account 'account b'
$verge->move( 'from name', 'to name', 10000 );

// send money externally (withdrawl?)
// send 10 coins from account (name) to external address
$verge->send( 'name', 'DMheu3hJtEx84DBTKjedKmSwekYvWgsEM3', 10 );
*/
