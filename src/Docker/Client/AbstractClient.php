<?php

namespace Docker\Client;

use Docker\Message\Response;

/**
 * ClientInterface
 */
abstract class AbstractClient {

    protected $url;

    public function __construct($url) {

        $this->url = $url;
    }

    public abstract function isApplicable();

    public abstract function post();

    /**
     * @param $url
     *
     * @return Response
     */
    public abstract function get($url);
}
