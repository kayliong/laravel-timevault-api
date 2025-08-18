<?php

namespace App\Http\Controllers\Objects;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Services\Objects\ObjectServices;

class ObjectController extends BaseController
{
    /**
     * Handle the incoming request to create an object.
     *
     * @return \Illuminate\Http\Response
     */
    public function createObject(Request $request)
    {
        // validate input request
        $validator = $this->validateCreateRequest($request);

        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            $errors = array_column(array_values($errors), '0');

            return $this->handleErrorResponse(null, null, $errors);
        }

        // create an object via service layer
        $result = (new ObjectServices())->createObject($request);
        $statusCode = $result['success']===true ? 201 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Handle the incoming request to get an object by key.
     *
     * @return \Illuminate\Http\Response
     */
    public function getObjectByKey(Request $request)
    {
        // validate input request
        $validator = $this->validateGetByKeyRequest($request);

        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            $errors = array_column(array_values($errors), '0');

            return $this->handleErrorResponse(null, null, $errors);
        }

        // get object via service layer
        $result = (new ObjectServices())->getObject($request);
        $statusCode = $result['success']===true ? 201 : 400;
        return response()->json($result, $statusCode);
    }

    public function getAllRecords(Request $request)
    {
        // get all records via service layer
        $result = (new ObjectServices())->getAllRecords($request);
        $statusCode = $result['success']===true ? 201 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Validate the incoming request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
     private function validateCreateRequest(Request $request)
    {
        // dynamic validation
        $validationRules = [];
        $data = $request->all();
        foreach (array_keys($data) as $key) {
            $validationRules[$key] = 'required|string|max:255'; // key must exist, value can be null
        }

        $validationMessages = [
            'required' => ['code' => '1001', 'message' => 'Missing field :attribute'],
            'string' => ['code' => '1004', 'message' => 'Invalid data type for :attribute'],
        ];

        return Validator::make($data, $validationRules, $validationMessages);
    }

    private function validateGetByKeyRequest(Request $request)
    {
        // dynamic validation
        $validationRules = [
            'key' => 'required|string|max:255',
        ];

        $validationMessages = [
            'required' => ['code' => '1001', 'message' => 'Missing field :attribute'],
            'string' => ['code' => '1004', 'message' => 'Invalid data type for :attribute'],
        ];

        return Validator::make(['key' => $request["key"]], $validationRules, $validationMessages);
    }

    /**
     * Handle the error response
     *
     * @param string $code
     * @param string $message
     * @param array $errors
     * @return \Illuminate\Http\Response
     */
    private function handleErrorResponse($code, $message, $errors = [])
    {
        if (isset($code) && isset($message)) {
            $errors[] = ['code' => $code, 'message' => $message];
        }
        return response()->json(['success'=>false, 'errors' => $errors], 400);
    }
}