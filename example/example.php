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
$client->listAccounts();

// create a new address
$address = $client->getAddress( 'vergeDEV' );
print( "Address: $address \n" );

// list accounts in wallet
print_r( $client->listAccounts() );

// get balance in wallet
print( "mkaz: " . $client->getBalance( 'vergeDEV' ) );

// move money from accounts in wallet
// moves from 'account a' to account 'account b'
//$verge->move( 'from name', 'to name', 10000 );

// send money externally (withdrawl?)
// send 10 coins from account (name) to external address
//$verge->send( 'name', 'DMheu3hJtEx84DBTKjedKmSwekYvWgsEM3', 10 );
