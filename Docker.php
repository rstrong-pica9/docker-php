<?php
use Guzzle\Http\Client;

/**
 * Docker
 */
class Docker
{

    private $connection;

    public function __construct($url)
    {

        $this->connection = new Client($url);
    }

    public function getPort($id, $privatePort)
    {

        $container = $this->getJson($id);

        $tcp = $container['NetworkSettings']['PortMapping']['Tcp'];

        return isset($tcp[$privatePort]) ? $tcp[$privatePort] : '';
    }

    public function createContainer($image, $options = array())
    {

        $options['Image'] = $image;

        #$options = array_merge($this->getDefaultContainerConfig(), $options);

        $options = json_encode($options, JSON_FORCE_OBJECT);

        $request = $this->connection->post("/containers/create", null, null, array('body' => $options));

        $response = $request->send();

        if ($response->isError()) {
            throw new \Exception((string) $response->getBody(), $response->getStatusCode());
        }

        $data = json_decode((string) $response->getBody(), true);

        return $data['Id'];
    }

    public function startContainer($id, $binds)
    {

        $response = $this->connection->post("/containers/$id/start", null, null, array('Binds' => $binds))->send();

        if ($response->isError()) {
            throw new \Exception((string) $response->getBody(), $response->getStatusCode());
        }

        return true;
    }

    protected function getJson($id)
    {

        $response = $this->connection->get("/containers/$id/json")->send();

        if ($response->isError()) {
            throw new \Exception((string) $response->getBody(), $response->getStatusCode());
        }

        return json_decode((string) $response->getBody(), true);
    }

    protected function getDefaultContainerConfig()
    {

        return array(
            'Hostname' => '',
            'User' => '',
            'Memory' => 0,
            'MemorySwap' => 0,
            'AttachStdin' => false,
            'AttachStdout' => true,
            'AttachStderr' => true,
            'PortSpecs' => null,
            'Tty' => false,
            'OpenStdin' => false,
            'StdinOnce' => false,
            'Env' => null,
            #'Cmd' => array('date'),
            'Dns' => null,
            'Image' => 'base',
            'Volumes' => array(),
            'VolumesFrom' => ''
        );


    }

}
