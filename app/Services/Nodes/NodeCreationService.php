<?php

namespace river\Services\Nodes;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use river\Models\Node;
use Illuminate\Contracts\Encryption\Encrypter;
use river\Contracts\Repository\NodeRepositoryInterface;

class NodeCreationService
{
    /**
     * @var \river\Contracts\Repository\NodeRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * CreationService constructor.
     */
    public function __construct(Encrypter $encrypter, NodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
    }

    /**
     * Create a new node on the panel.
     *
     * @return \river\Models\Node
     *
     * @throws \river\Exceptions\Model\DataValidationException
     */
    public function handle(array $data)
    {
        $data['uuid'] = Uuid::uuid4()->toString();
        $data['daemon_token'] = $this->encrypter->encrypt(Str::random(Node::DAEMON_TOKEN_LENGTH));
        $data['daemon_token_id'] = Str::random(Node::DAEMON_TOKEN_ID_LENGTH);

        return $this->repository->create($data, true, true);
    }
}
