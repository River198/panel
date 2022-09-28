<?php

namespace river\Services\Allocations;

use Exception;
use IPTools\Network;
use river\Models\Node;
use Illuminate\Database\ConnectionInterface;
use river\Exceptions\DisplayException;
use river\Contracts\Repository\AllocationRepositoryInterface;
use river\Exceptions\Service\Allocation\CidrOutOfRangeException;
use river\Exceptions\Service\Allocation\PortOutOfRangeException;
use river\Exceptions\Service\Allocation\InvalidPortMappingException;
use river\Exceptions\Service\Allocation\TooManyPortsInRangeException;

class AssignmentService
{
    public const CIDR_MAX_BITS = 27;
    public const CIDR_MIN_BITS = 32;
    public const PORT_FLOOR = 1024;
    public const PORT_CEIL = 65535;
    public const PORT_RANGE_LIMIT = 1000;
    public const PORT_RANGE_REGEX = '/^(\d{4,5})-(\d{4,5})$/';

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var \river\Contracts\Repository\AllocationRepositoryInterface
     */
    protected $repository;

    /**
     * AssignmentService constructor.
     */
    public function __construct(AllocationRepositoryInterface $repository, ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->repository = $repository;
    }

    /**
     * Insert allocations into the database and link them to a specific node.
     *
     * @throws \river\Exceptions\DisplayException
     * @throws \river\Exceptions\Service\Allocation\CidrOutOfRangeException
     * @throws \river\Exceptions\Service\Allocation\InvalidPortMappingException
     * @throws \river\Exceptions\Service\Allocation\PortOutOfRangeException
     * @throws \river\Exceptions\Service\Allocation\TooManyPortsInRangeException
     */
    public function handle(Node $node, array $data)
    {
        $explode = explode('/', $data['allocation_ip']);
        if (count($explode) !== 1) {
            if (!ctype_digit($explode[1]) || ($explode[1] > self::CIDR_MIN_BITS || $explode[1] < self::CIDR_MAX_BITS)) {
                throw new CidrOutOfRangeException();
            }
        }

        try {
            $underlying = gethostbyname($data['allocation_ip']);
            $parsed = Network::parse($underlying);
        } catch (Exception $exception) {
            /* @noinspection PhpUndefinedVariableInspection */
            throw new DisplayException("Could not parse provided allocation IP address ({$underlying}): {$exception->getMessage()}", $exception);
        }

        $this->connection->beginTransaction();
        foreach ($parsed as $ip) {
            foreach ($data['allocation_ports'] as $port) {
                if (!is_digit($port) && !preg_match(self::PORT_RANGE_REGEX, $port)) {
                    throw new InvalidPortMappingException($port);
                }

                $insertData = [];
                if (preg_match(self::PORT_RANGE_REGEX, $port, $matches)) {
                    $block = range($matches[1], $matches[2]);

                    if (count($block) > self::PORT_RANGE_LIMIT) {
                        throw new TooManyPortsInRangeException();
                    }

                    if ((int) $matches[1] <= self::PORT_FLOOR || (int) $matches[2] > self::PORT_CEIL) {
                        throw new PortOutOfRangeException();
                    }

                    foreach ($block as $unit) {
                        $insertData[] = [
                            'node_id' => $node->id,
                            'ip' => $ip->__toString(),
                            'port' => (int) $unit,
                            'ip_alias' => array_get($data, 'allocation_alias'),
                            'server_id' => null,
                        ];
                    }
                } else {
                    if ((int) $port <= self::PORT_FLOOR || (int) $port > self::PORT_CEIL) {
                        throw new PortOutOfRangeException();
                    }

                    $insertData[] = [
                        'node_id' => $node->id,
                        'ip' => $ip->__toString(),
                        'port' => (int) $port,
                        'ip_alias' => array_get($data, 'allocation_alias'),
                        'server_id' => null,
                    ];
                }

                $this->repository->insertIgnore($insertData);
            }
        }

        $this->connection->commit();
    }
}
