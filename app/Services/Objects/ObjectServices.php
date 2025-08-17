<?php

namespace App\Services\Objects;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Objects\ObjectModel; // use Object model

class ObjectServices
{
    /**
     * Create a new object.
     *
     * @param array $request
     * @return array
     */
    public function createObject($request): array
    {
        try {
            DB::beginTransaction();
            $data = $request->all();

            // additional validation for empty data, just in case Validator missed it
            if (empty($data)) {
                return [
                    'success' => false,
                    'message' => 'No data provided'
                ];
            }

            $stored = [];

            foreach ($data as $key => $value) {
                // convert value to JSON string if it's an array or object
                $valueToStore = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

                // create a new record
                $timeVaultObject = ObjectModel::create([
                    'key' => $key,
                    'value' => $valueToStore
                ]);

                $stored[$key] = [
                    'id' => $timeVaultObject->id,
                    'key' => $timeVaultObject->key,
                    'value' => $timeVaultObject->value,
                    'created_at' => $timeVaultObject->created_at->format('Y-m-d H:i:s'),
                    'created_at_timestamp' => $timeVaultObject->updated_at->timestamp
                ];
            }
            DB::commit();
            return [
                'success' => true,
                'message' => 'Data stored successfully',
                'data' => $stored
            ];

        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'Error storing key-value pair: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Perform get object, call different function determine by timestamp
     *
     * @param array $request
     * @return array
     */
    public function getObject($request)
    {
        // if request has timestamp value, call get by timestamp method
        if (isset($request["timestamp"])) {
            return $this->getObjectByTimestamp($request);
        }

        return $this->getLatestObject($request);
    }

    /**
     * Get the object model instance.
     *
     * @param array $request
     * @return ObjectModel
     */
    private function getLatestObject($request)
    {
        try {
            $key = $request["key"] ?? null;

            if (empty($key)) {
                return [
                    'success' => false,
                    'message' => 'Key parameter is required'
                ];
            }

            // get record by key, order by created_at desc
            $record = DB::table('timevault_objects')
                ->where('key', $key)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$record) {
                return [
                    'success' => false,
                    'message' => 'Key not found'
                ];
            }

            // try to decode JSON, if it fails return as string
            $value = json_decode($record->value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $value = $record->value;
            }

            return [
                'success' => true,
                'data' => [
                    'key' => $record->key,
                    'value' => $value,
                    'created_at' => $record->created_at
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get object by key and timestamp.
     *
     * @param array $request
     * @return array
     */
    private function getObjectByTimestamp($request)
    {
        try {
            // validate required parameters, can move to a separate validation function in controller
            $key = $request["key"] ?? null;
            $timestamp = $request["timestamp"] ?? null;

            if (empty($key) || empty($timestamp)) {
                return [
                    'success' => false,
                    'message' => 'Key and timestamp parameters are required'
                ];
            }

            // convert Unix timestamp to datetime string used for querying
            $datetime = date('Y-m-d H:i:s', (int)$timestamp);

            $record = DB::table('timevault_objects')
                ->where('key', $key)
                ->where('created_at', '=', $datetime)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$record) {
                return [
                    'success' => false,
                    'message' => 'Key not found for the given timestamp'
                ];
            }

            // try to decode JSON, if it fails return as string
            $value = json_decode($record->value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $value = $record->value;
            }

            return [
                'success' => true,
                'data' => [
                    'key' => $record->key,
                    'value' => $value,
                    'created_at' => $record->created_at
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all records with pagination from the object table.
     *
     * @param array $request
     * @return array
     */
    public function getAllRecords($request)
    {
        $maxRecords = 500; // maximum records to return
        try {
            // get pagination parameters
            $page = max(1, (int) $request->input('page', 1));
            $perPage = max(1, min($maxRecords, (int) $request->input('per_page', 1))); // Default 10, max 100

            // calculate offset
            $offset = ($page - 1) * $perPage;

            // get total count
            $total = DB::table('timevault_objects')->count();

            // calculate pagination info
            $totalPages = ceil($total / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPrevPage = $page > 1;

            // get paginated records
            $records = DB::table('timevault_objects')
                ->orderBy('created_at', 'asc')
                ->limit($perPage)
                ->offset($offset)
                ->get();

            $formattedRecords = [];

            foreach ($records as $record) {
                // try to decode JSON, if it fails return as string
                $value = json_decode($record->value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $value = $record->value;
                }

                $createdAt = Carbon::parse($record->created_at);
                $updatedAt = Carbon::parse($record->updated_at);

                $formattedRecords[] = [
                    'id' => $record->id,
                    'key' => $record->key,
                    'value' => $value,
                    'created_at' => $createdAt->format('Y-m-d H:i:s'),
                    'created_at_timestamp' => $createdAt->timestamp
                ];
            }

            return [
                'success' => true,
                'data' => $formattedRecords,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next_page' => $hasNextPage,
                    'has_prev_page' => $hasPrevPage,
                    'next_page' => $hasNextPage ? $page + 1 : null,
                    'prev_page' => $hasPrevPage ? $page - 1 : null,
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $total)
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving all data: ' . $e->getMessage()
            ];
        }
    }
}
