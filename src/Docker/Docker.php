<?php

namespace Docker;

/**
 * Docker
 */
class Docker
{

    private $url;

    /**
     * Constructs a new docker object
     *
     * @param $url
     */
    public function __construct($url)
    {

        $this->url = $url;
    }

    /**
     * Gets a list of containers
     *
     * @param array $conditions
     *
     *   Conditions might be:
     *     all    => true or false, Show all containers. Only running containers are shown by default
     *     limit  => Show ``limit`` last created containers, include non-running ones.
     *     since: => Show only containers created since Id, include non-running ones.
     *     before => Show only containers created before Id, include non-running ones.
     *     size   => true or false, Show the containers sizes
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function listContainers($conditions = array())
    {
        $availableKeys = array('all', 'limit', 'since', 'before', 'size');

        foreach ($conditions as $key => $value) {
            if (!in_array($key, $availableKeys)) {
                throw new \Exception("Key $key is not available");
            }
        }

        return $this->getJson("/containers/json?" . http_build_query($conditions));
    }


    /**
     * Creates a new container
     *
     * @param $image
     *   Name of the image
     * @param array $options
     *   Options might be:
     *     'Hostname' => '',
     *     'User' => '',
     *     'Memory' => 0,
     *     'MemorySwap' => 0,
     *     'AttachStdin' => false,
     *     'AttachStdout' => true,
     *     'AttachStderr' => true,
     *     'PortSpecs' => null,
     *     'Tty' => false,
     *     'OpenStdin' => false,
     *     'StdinOnce' => false,
     *     'Env' => null,
     *     'Cmd' => array('date'),
     *     'Dns' => null,
     *     'Volumes' => array(),
     *     'VolumesFrom' => ''
     *
     * @return mixed
     *   The new container id
     *
     * @throws \Exception
     */
    public function createContainer($image, $options = array())
    {

        $options['Image'] = $image;

        $body = $this->post("/containers/create", $options);

        $data = json_decode($body, true);

        return $data['Id'];
    }

    /**
     * Inspects a container
     *
     * @param $id
     *   The container id
     * @return mixed
     *   Array of low-level information
     */
    public function inspectContainer($id)
    {
        return $this->getJson("/containers/$id/json");
    }

    /**
     * List processes running inside a container
     *
     * @param $id
     *   The container id
     *
     * @param string $psArgs
     *   ps arguments to use (eg. aux)
     *
     * @return mixed
     */
    public function listProcesses($id, $psArgs = '')
    {

        return $this->getJson("/containers/$id/top?ps_args=$psArgs");
    }

    /**
     * Inspect changes on container ``id`` 's filesystem
     *
     * @param $id
     *   The container id
     *
     * @return mixed
     */
    public function inspectChanges($id)
    {

        return $this->getJson("/containers/$id/inspect");
    }

    /**
     * Export a container
     *
     * @param $id
     *   The container id
     *
     * @return mixed
     */
    public function exportContainer($id)
    {

        return $this->getJson("/containers/$id/export");
    }

    /**
     * Start a container
     *
     * @param $id
     *   The container id
     *
     * @param $binds
     *   An array of volume mounts e.g. array('path/to/destination/in/container' => 'path/to/source/on/host')
     *
     * @return bool
     *   Returns true if container successfully started
     * @throws \Exception
     */
    public function startContainer($id, $binds = array())
    {
        $this->post("/containers/$id/start", array('Binds' => $binds));

        return true;
    }

    /**
     * Stop a contaier
     *
     * @param $id
     *   The container id
     *
     * @param int $timeout
     *   Number of seconds to wait for the container to stop before killing it
     *
     * @return bool
     *   Returns true if container successfully stoped
     *
     * @throws \Exception
     */
    public function stopContainer($id, $timeout = 5)
    {

        $this->post("/containers/$id/stop?t=$timeout");

        return true;
    }

    /**
     * Restart a contaier
     *
     * @param $id
     *   The container id
     *
     * @param int $timeout
     *   Number of seconds to wait for the container to stop before killing it
     *
     * @return bool
     *   Returns true if container successfully restarted
     *
     * @throws \Exception
     */
    public function restartContainer($id, $timeout = 5)
    {

        $this->post("/containers/$id/restart?t=$timeout");

        return true;
    }

    /**
     * Kill a container
     *
     * @param $id
     *   The container id
     *
     * @return bool
     *   Returns true if container successfully killed
     *
     * @throws \Exception
     */
    public function killContainer($id)
    {

        $this->post("/containers/$id/kill");

        return true;
    }


    public function getPort($id, $privatePort)
    {

        $container = $this->inspectContainer($id);
        $tcp = $container['NetworkSettings']['PortMapping']['Tcp'];

        return isset($tcp[$privatePort]) ? $tcp[$privatePort] : '';
    }


    protected function getJson($url)
    {
        $body = $this->get($url);

        return json_decode($body, true);
    }

    protected function get($url)
    {

        if (substr($this->url, 0, 7) == 'http://') {
            return $this->httpGet($url);
        } else {
            return $this->send('GET', $url);
        }

    }

    protected function httpGet($url)
    {

        $request = new \HttpRequest($this->url . $url, \HttpRequest::METH_GET);

        try {
            $request->send();
            if ($request->getResponseCode() >= 200 && $request->getResponseCode() < 300) {
                return $request->getResponseBody();
            }
        } catch (\HttpException $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }

        return '';
    }

    protected function post($url, $data = "")
    {

        if (substr($this->url, 0, 7) == 'http://') {
            return $this->httpPost($url, $data);
        } else {
            return $this->send('POST', $url, $data);
        }

    }


    protected function httpPost($url, $data)
    {

        $request = new \HttpRequest($url, \HttpRequest::METH_POST, array('proxyhost' => $this->url));

        $request->setBody(json_encode($data, JSON_FORCE_OBJECT));


        try {
            $request->send();
            if ($request->getResponseCode() >= 200 && $request->getResponseCode() < 300) {
                return $request->getResponseBody();
            }
        } catch (\HttpException $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }

        return '';

    }

    protected function send($method, $url, $body = '')
    {

        $body = json_encode($body, JSON_FORCE_OBJECT);

        $length = strlen($body);
        $data = '';
        $fp = fsockopen($this->url, null, $errno, $errstr, 30);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            $out = "$method $url HTTP/1.1\r\n";
            $out .= "Content-Length: $length\r\n";
            $out .= "Connection: Close\r\n\r\n$body";

            fwrite($fp, $out);
            while (!feof($fp)) {
                $data .= fgets($fp, 1024);
            }
            fclose($fp);
        }

        $data = http_parse_message($data);

        return $data->body;
    }

}
