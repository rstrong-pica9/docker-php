<?php

/**
 * Docker
 */
class Docker {

    const URL = 'unix:///var/run/docker.sock';

    private $connection;

    public function __construct() {

        $this->connection = fsockopen(self::URL);
    }

    public function getPort($id, $privatePort) {


        $response = $this->send("/containers/$id/json");

        $container = json_decode($response['text'], true);

        $tcp = $container['NetworkSettings']['PortMapping']['Tcp'];

        return isset($tcp[$privatePort]) ? $tcp[$privatePort] : '';
    }

    protected function send($url) {

        $out = "GET $url HTTP/1.1\r\n";
        $out .= "Connection: Close\r\n\r\n";

        fwrite($this->connection, $out);

        $rsp = "";
        while (!feof($this->connection)) {
            $rsp .= fgets($this->connection, 128);
        }
        fclose($this->connection);

        list($headers, $response['text']) = explode("\r\n\r\n", $rsp);

        $headers = explode("\r\n", $headers);
        $statusLine = array_shift($headers);

        $response['header']['status'] = $statusLine;

        foreach ($headers as $header) {
            list($key, $val) = explode(': ', $header);
            $response['header'][$key] = $val;
        }

        if ($statusLine != 'HTTP/1.1 200 OK') {
            throw new \Exception($statusLine);
        }

        return $response;
    }

}
