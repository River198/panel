<?php

namespace river\Repositories\Wings;

use river\Models\Node;
use GuzzleHttp\Exception\TransferException;
use river\Exceptions\Http\Connection\DaemonConnectionException;

class DaemonConfigurationRepository extends DaemonRepository
{
    /**
     * Returns system information from the wings instance.
     *
     * @throws \river\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function getSystemInformation(): array
    {
        try {
            $response = $this->getHttpClient()->get('/api/system');
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }

        return json_decode($response->getBody()->__toString(), true);
    }

    /**
     * Updates the configuration information for a daemon. Updates the information for
     * this instance using a passed-in model. This allows us to change plenty of information
     * in the model, and still use the old, pre-update model to actually make the HTTP request.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \river\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function update(Node $node)
    {
        try {
            return $this->getHttpClient()->post(
                '/api/update',
                ['json' => $node->getConfiguration()]
            );
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
