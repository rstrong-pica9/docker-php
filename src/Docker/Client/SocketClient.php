<?php

namespace Docker\Client;

/**
 * SocketClient
 */
class SocketClient extends AbstractClient{




    public function isApplicable()
    {
        return (substr($this->url, 0, 7) == 'unix://');
    }



    public function post()
    {
        // TODO: Implement post() method.
    }

    public function get()
    {
        // TODO: Implement get() method.
    }
}
