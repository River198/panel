<?php

namespace river\Events\Server;

use river\Events\Event;
use river\Models\Server;
use Illuminate\Queue\SerializesModels;

class Installed extends Event
{
    use SerializesModels;

    /**
     * @var \river\Models\Server
     */
    public $server;

    /**
     * Create a new event instance.
     *
     * @var \river\Models\Server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }
}
