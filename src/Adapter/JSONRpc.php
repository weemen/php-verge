<?php
declare(strict_types=1);

namespace VergeCurrency\VergeClient\Adapter;

use Psr\Log\LoggerInterface;
use VergeCurrency\VergeClient\Exception\ConnectionExcpetion;
use VergeCurrency\VergeClient\Exception\ResponseException;
use VergeCurrency\VergeClient\Exception\RuntimeException;

/**
 * The object of this class are generic jsonRPC 1.0 clients
 * http://json-rpc.org/wiki/specification
 *
 * @author sergio <jsonrpcphp@inservibile.org>
 */
class JSONRpc implements AdapterInterface
{

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
     * Debug state
     *
     * @var boolean
     */
    private $debug;

    /**
     * The server URL
     *
     * @var string
     */
    private $url;

    /**
     * The request id
     *
     * @var integer
     */
    private $id;

    /**
     * If true, notifications are performed instead of requests
     *
     * @var boolean
     */
    private $notification = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string          $url
     * @param LoggerInterface $logger
     */
    public function __construct(string $url, LoggerInterface $logger)
    {
        // server URL
        $this->url = $url;
        // proxy
        empty($proxy) ? $this->proxy = '' : $this->proxy = $proxy;
        // debug state
        empty($debug) ? $this->debug = false : $this->debug = true;
        // message id
        $this->id = 1;

        $this->logger = $logger;
    }

    /**
     * Sets the notification state of the object. In this state, notifications are performed, instead of requests.
     *
     * @param boolean $notification
     */
    public function setRPCNotification($notification)
    {
        empty($notification) ?
                            $this->notification = false
                            :
                            $this->notification = true;
    }

    /**
     * Performs a jsonRCP request and gets the results as an array
     *
     * @param string $method
     * @param array $params
     * @return array
     */
    public function __call(string $method, array $params)
    {

        // check
        if (false == in_array($method, $this->validMethods)) {
            throw new RuntimeException(
                sprintf('Method name "%s" does not exists or is not supported', $method)
            );
        }

        // sets notification or request task
        $currentId = null;
        if (!$this->notification) {
            $currentId = $this->id;
        }

        // prepares the request
        $request = json_encode([
            'method' => $method,
            'params' => $params,
            'id' => $currentId
        ]);

        $this->logger->debug(sprintf("Try to perform request: %s", $request));

        // performs the HTTP POST
        $context  = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => $request
            ]
        ]);


        $fp = @fopen($this->url, 'r', false, $context);

        if (!is_resource($fp)) {
            throw new ConnectionExcpetion('Unable to connect to '.$this->url);
        }

        $response = '';
        while ($row = fgets($fp)) {
            $response.= trim($row)."\n";
        }

        $this->logger->debug(sprintf('Recieved response from server: %s', $response));

        // final checks and return
        if ($this->notification) {
            return true;
        }

        return $this->validateResponse(json_decode($response, true), $currentId);
    }

    /**
     * @param array $response
     * @param int   $currentId
     *
     * @return array
     */
    private function validateResponse(array $response, int $currentId): array
    {
        if ($response['id'] != $currentId) {
            throw new ResponseException(
                sprintf('Incorrect response id (request id: %s, response id: %s)', $currentId, $response['id'])
            );
        }
        if (!is_null($response['error'])) {
            throw new ResponseException('Request error: '.$response['error']);
        }

        $this->logger->info('Response message validated succesfully');
        return $response['result'];
    }
}
