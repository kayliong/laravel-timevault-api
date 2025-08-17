<?php

namespace Tests;

use App\Services\Objects\ObjectServices;
use App\Models\Objects\ObjectModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mockery;

class ObjectServicesTest extends TestCase
{
    protected $objectServices;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectServices = new ObjectServices();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCreateObjectSuccess()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('all')->andReturn(['key1' => 'value1', 'key2' => ['nested' => 'array']]);

        $mockModel = Mockery::mock('overload:' . ObjectModel::class);
        $mockModel->shouldReceive('create')->twice()->andReturn((object)[
            'id' => 1,
            'key' => 'key1',
            'value' => 'value1',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $result = $this->objectServices->createObject($mockRequest);

        $this->assertTrue($result['success']);
        $this->assertEquals('Data stored successfully', $result['message']);
        $this->assertArrayHasKey('data', $result);
    }

    public function testCreateObjectEmptyData()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('all')->andReturn([]);

        $result = $this->objectServices->createObject($mockRequest);

        $this->assertFalse($result['success']);
        $this->assertEquals('No data provided', $result['message']);
    }

    public function testCreateObjectException()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('all')->andReturn(['key1' => 'value1']);

        $mockModel = Mockery::mock('overload:' . ObjectModel::class);
        $mockModel->shouldReceive('create')->andThrow(new \Exception('Database error'));

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollback')->once();

        $result = $this->objectServices->createObject($mockRequest);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Error storing key-value pair:', $result['message']);
    }

    public function testGetObjectWithTimestamp()
    {
        $request = ['key' => 'test_key', 'timestamp' => 1640995200];

        $mockRecord = (object)[
            'key' => 'test_key',
            'value' => 'test_value',
            'created_at' => '2022-01-01 00:00:00'
        ];

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('key', 'test_key')->andReturnSelf();
        DB::shouldReceive('where')->with('created_at', '=', '2022-01-01 00:00:00')->andReturnSelf();
        DB::shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
        DB::shouldReceive('first')->andReturn($mockRecord);

        $result = $this->objectServices->getObject($request);

        $this->assertTrue($result['success']);
        $this->assertEquals('test_key', $result['data']['key']);
        $this->assertEquals('test_value', $result['data']['value']);
    }

    public function testGetObjectWithoutTimestamp()
    {
        $request = ['key' => 'test_key'];

        $mockRecord = (object)[
            'key' => 'test_key',
            'value' => 'test_value',
            'created_at' => '2022-01-01 00:00:00'
        ];

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('key', 'test_key')->andReturnSelf();
        DB::shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
        DB::shouldReceive('first')->andReturn($mockRecord);

        $result = $this->objectServices->getObject($request);

        $this->assertTrue($result['success']);
        $this->assertEquals('test_key', $result['data']['key']);
    }

    public function testGetLatestObjectMissingKey()
    {
        $request = [];

        $result = $this->objectServices->getObject($request);

        $this->assertFalse($result['success']);
        $this->assertEquals('Key parameter is required', $result['message']);
    }

    public function testGetLatestObjectNotFound()
    {
        $request = ['key' => 'nonexistent_key'];

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('key', 'nonexistent_key')->andReturnSelf();
        DB::shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
        DB::shouldReceive('first')->andReturn(null);

        $result = $this->objectServices->getObject($request);

        $this->assertFalse($result['success']);
        $this->assertEquals('Key not found', $result['message']);
    }

    public function testGetObjectByTimestampMissingParams()
    {
        $request = ['key' => 'test_key'];

        $result = $this->objectServices->getObject($request);

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('key', 'test_key')->andReturnSelf();
        DB::shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
        DB::shouldReceive('first')->andReturn(null);

        $this->assertFalse($result['success']);
    }

    public function testGetObjectByTimestampNotFound()
    {
        $request = ['key' => 'test_key', 'timestamp' => 1640995200];

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('key', 'test_key')->andReturnSelf();
        DB::shouldReceive('where')->with('created_at', '=', '2022-01-01 00:00:00')->andReturnSelf();
        DB::shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
        DB::shouldReceive('first')->andReturn(null);

        $result = $this->objectServices->getObject($request);

        $this->assertFalse($result['success']);
        $this->assertEquals('Key not found for the given timestamp', $result['message']);
    }

    public function testGetObjectWithJsonValue()
    {
        $request = ['key' => 'test_key'];
        $jsonValue = '{"nested":"value"}';

        $mockRecord = (object)[
            'key' => 'test_key',
            'value' => $jsonValue,
            'created_at' => '2022-01-01 00:00:00'
        ];

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('key', 'test_key')->andReturnSelf();
        DB::shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
        DB::shouldReceive('first')->andReturn($mockRecord);

        $result = $this->objectServices->getObject($request);

        $this->assertTrue($result['success']);
        $this->assertEquals(['nested' => 'value'], $result['data']['value']);
    }

    public function testGetAllRecordsSuccess()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('page', 1)->andReturn(1);
        $mockRequest->shouldReceive('input')->with('per_page', 1)->andReturn(10);

        $mockRecords = collect([
            (object)[
                'id' => 1,
                'key' => 'key1',
                'value' => 'value1',
                'created_at' => '2022-01-01 00:00:00',
                'updated_at' => '2022-01-01 00:00:00'
            ]
        ]);

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('count')->andReturn(1);
        DB::shouldReceive('orderBy')->with('created_at', 'asc')->andReturnSelf();
        DB::shouldReceive('limit')->with(10)->andReturnSelf();
        DB::shouldReceive('offset')->with(0)->andReturnSelf();
        DB::shouldReceive('get')->andReturn($mockRecords);

        $result = $this->objectServices->getAllRecords($mockRequest);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertEquals(1, $result['pagination']['current_page']);
        $this->assertEquals(10, $result['pagination']['per_page']);
        $this->assertEquals(1, $result['pagination']['total']);
    }

    public function testGetAllRecordsWithPagination()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('page', 1)->andReturn(2);
        $mockRequest->shouldReceive('input')->with('per_page', 1)->andReturn(5);

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('count')->andReturn(15);
        DB::shouldReceive('orderBy')->with('created_at', 'asc')->andReturnSelf();
        DB::shouldReceive('limit')->with(5)->andReturnSelf();
        DB::shouldReceive('offset')->with(5)->andReturnSelf();
        DB::shouldReceive('get')->andReturn(collect([]));

        $result = $this->objectServices->getAllRecords($mockRequest);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['pagination']['current_page']);
        $this->assertEquals(5, $result['pagination']['per_page']);
        $this->assertEquals(15, $result['pagination']['total']);
        $this->assertEquals(3, $result['pagination']['total_pages']);
        $this->assertTrue($result['pagination']['has_next_page']);
        $this->assertTrue($result['pagination']['has_prev_page']);
    }

    public function testGetAllRecordsException()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('page', 1)->andReturn(1);
        $mockRequest->shouldReceive('input')->with('per_page', 1)->andReturn(10);

        DB::shouldReceive('table')->with('timevault_objects')->andThrow(new \Exception('Database error'));

        $result = $this->objectServices->getAllRecords($mockRequest);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Error retrieving all data:', $result['message']);
    }

    public function testGetAllRecordsMaxLimit()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('input')->with('page', 1)->andReturn(1);
        $mockRequest->shouldReceive('input')->with('per_page', 1)->andReturn(1000);

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('count')->andReturn(0);
        DB::shouldReceive('orderBy')->with('created_at', 'asc')->andReturnSelf();
        DB::shouldReceive('limit')->with(500)->andReturnSelf();
        DB::shouldReceive('offset')->with(0)->andReturnSelf();
        DB::shouldReceive('get')->andReturn(collect([]));

        $result = $this->objectServices->getAllRecords($mockRequest);

        $this->assertTrue($result['success']);
        $this->assertEquals(500, $result['pagination']['per_page']);
    }
}