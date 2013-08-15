<?php

namespace Docker\Tests;

use Docker\Docker;

/**
 * DockerTest
 */
class DockerTest extends \PHPUnit_Framework_TestCase {

    /** @var $client Docker */
    protected $client;

    public function setUp() {

        $this->client = new Docker('unix:///var/run/docker.sock');
    }

    public function testCreateDeleteContainer() {


        $id = $this->client->createContainer('fritze/drupal');

        $this->assertSame(strlen($id), 12);

        $this->assertTrue($this->client->deleteContainer($id, true));

    }

    public function testStartStopContainer() {

        $id = $this->client->createContainer('fritze/drupal');

        $this->assertSame(strlen($id), 12);


        $this->assertTrue($this->client->startContainer($id));

        $this->assertTrue($this->client->stopContainer($id, 1));

        $this->assertTrue($this->client->deleteContainer($id, true));

    }



    public function testListContainers() {


        #var_dump($this->client->listContainers());
    }
}
