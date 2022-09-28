<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Events\Server;

use river\Models\Server;
use Illuminate\Queue\SerializesModels;

class Saving
{
    use SerializesModels;

    /**
     * The Eloquent model of the server.
     *
     * @var \river\Models\Server
     */
    public $server;

    /**
     * Create a new event instance.
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }
}
