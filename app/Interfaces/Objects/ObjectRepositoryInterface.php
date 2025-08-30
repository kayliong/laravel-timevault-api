<?php
namespace App\Interfaces\Objects;

interface ObjectRepositoryInterface
{
    public function create(array $data);
    public function findLatestByKey(string $key);
    public function getAll(int $limit, int $offset);
}
