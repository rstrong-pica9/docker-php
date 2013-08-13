<?php

namespace Docker\Client;

use Docker\Message\Response;

/**
 * HttpClient
 */
class HttpClient extends AbstractClient{

    private $url;

    /** @var \Guzzle\Http\Client */
    private $connection;

    public function __construct($url) {

        parent::__construct($url);

        $this->connection = new \Guzzle\Http\Client($this->url);
    }

    public function isApplicable()
    {
        return (substr($this->url, 0, 7) == 'http://');
    }

    public function post()
    {
        // TODO: Implement post() method.
    }

    public function get($url)
    {

        $guzzleResponse = $this->connection->get($url)->send();

        $response = new Response();

        return $response;
    }
}
