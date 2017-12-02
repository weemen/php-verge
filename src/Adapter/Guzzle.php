<?php

declare(strict_types=1);

namespace VergeCurrency\VergeClient\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use VergeCurrency\VergeClient\Exception\ConnectionExcpetion;
use VergeCurrency\VergeClient\Exception\ResponseException;
use VergeCurrency\VergeClient\Exception\RuntimeException;

class Guzzle implements AdapterInterface
{
    const CLIENTNAME = 'VergeClient-PHP/0.0.1';
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $notification = false;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var int|null
     */
    private $userId;

    /**
     * @var array
     */
    private $validMethods = [
        'getaddress',
        'getaccount',
        'getnewaddress',
        'listaccounts',
        'gettransaction',
        'setaccount',
        'getbalance',
        'move',
        'send',
        'validateaddress'
    ];

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, string $uri, LoggerInterface $logger, $userId = null)
    {
        $this->client   = $client;
        $this->logger   = $logger;
        $this->uri      = $uri;
        $this->userId   = $userId;
        $this->logger->debug(sprintf('Created Client from: "%s" which will connect to "%s"', self::class, $uri));
    }

    /**
     * Sets the notification state of the object. In this state, notifications are performed, instead of requests.
     *
     * @param boolean $notification
     */
    public function setRPCNotification(bool $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @param string $method
     * @param array  $arguments
     */
    public function __call(string $method, array $arguments)
    {
        if (false == in_array($method, $this->validMethods)) {
            throw new RuntimeException(
                sprintf('Method name "%s" does not exists or is not supported', $method)
            );
        }

        $request = $this->buildRequest($method, $arguments);

        try {
            $response = $this->client->request('POST', $this->uri, $this->buildRequest($method, $request));
        } catch (ConnectException $e) {
            throw new ConnectionExcpetion(
                sprintf('Cannot connect to: %s because of %s', $this->uri, $e->getMessage())
            );
        } catch (ClientException $e) {
            throw new ResponseException(
                sprintf(
                    'There was an error in processing your request: %s with status code %s',
                    json_encode($request),
                    $e->getMessage()
                )
            );
        }

        $this->logger->debug(
            sprintf('Recieved valid response from server: %s', $response->getBody())
        );

        if ($this->notification) {
            $this->logger->info('Returning notification state');
            return true;
        }

        return $this->validateResponse(json_decode((string) $response->getBody(), true));
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return array
     */
    private function buildRequest(string $method, array $arguments): array
    {
        $request = [
            'json'  => [
                'method' => $method,
                'Params' => $arguments,
                'id'     => $this->userId
            ],
            'headers' => [
                'User-Agent' => self::CLIENTNAME,
            ],
            'connect_timeout' => 30
        ];

        $this->logger->info(
            sprintf('Request created for user: "%s" containing: %s', $this->userId, json_encode($request))
        );

        return $request;
    }

    /**
     * @param array $response
     *
     * @return mixed
     */
    private function validateResponse(array $response)
    {
        if ($this->userId != null && $response['id'] != $this->userId) {
            throw new ResponseException(
                sprintf('Incorrect response id (request id: %s, response id: %s)', $this->userId, $response['id'])
            );
        }

        if (!is_null($response['error'])) {
            throw new ResponseException('Request error: '.$response['error']);
        }

        $this->logger->info('Response message validated succesfully');
        return $response['result'];
    }
}
