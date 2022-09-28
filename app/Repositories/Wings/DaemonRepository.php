<?php

namespace river\Repositories\Wings;

use GuzzleHttp\Client;
use river\Models\Node;
use Webmozart\Assert\Assert;
use river\Models\Server;
use Illuminate\Contracts\Foundation\Application;

abstract class DaemonRepository
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \river\Models\Server|null
     */
    protected $server;

    /**
     * @var \river\Models\Node|null
     */
    protected $node;

    /**
     * BaseWingsRepository constructor.
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * Set the server model this request is stemming from.
     *
     * @return $this
     */
    public function setServer(Server $server)
    {
        $this->server = $server;

        $this->setNode($this->server->node);

        return $this;
    }

    /**
     * Set the node model this request is stemming from.
     *
     * @return $this
     */
    public function setNode(Node $node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Return an instance of the Guzzle HTTP Client to be used for requests.
     */
    public function getHttpClient(array $headers = []): Client
    {
        Assert::isInstanceOf($this->node, Node::class);

        return new Client([
            'verify' => $this->app->environment('production'),
            'base_uri' => $this->node->getConnectionAddress(),
            'timeout' => config('river.guzzle.timeout'),
            'connect_timeout' => config('river.guzzle.connect_timeout'),
            'headers' => array_merge($headers, [
                'Authorization' => 'Bearer ' . $this->node->getDecryptedKey(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]),
        ]);
    }
}
