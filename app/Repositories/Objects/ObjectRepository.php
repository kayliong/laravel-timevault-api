<?php
namespace App\Repositories\Objects;

use App\Interfaces\Objects\ObjectRepositoryInterface;
use App\Models\Objects\ObjectModel;

class ObjectRepository implements ObjectRepositoryInterface
{
    /**
     * Create a new object record.
     *
     * @param array $data
     * @return ObjectModel
     */
    public function create(array $data)
    {
        return ObjectModel::create($data);
    }

    /**
     * Find an object by its key.
     *
     * @param string $key
     * @return ObjectModel|null
     */
    public function findLatestByKey(string $key)
    {
        $latestObj = ObjectModel::where('key', $key)
            ->orderBy('created_at', 'desc')
            ->first();
        return $latestObj ?: null;
    }

    /**
     * Get all object records with pagination.
     *
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(int $limit, int $offset)
    {
        return ObjectModel::skip($offset)->take($limit)->get();
    }
}