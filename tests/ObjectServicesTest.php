<?php

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\Objects\ObjectServices;
use App\Models\Objects\ObjectModel;
use Carbon\Carbon;

class ObjectServicesTest extends TestCase
{
    private $objectServices;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectServices = new ObjectServices();
    }

    public function testCreateObjectSuccess()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->andReturn(['key1' => 'value1']);

        $mockModel = new \stdClass();
        $mockModel->id = 1;
        $mockModel->key = 'key1';
        $mockModel->value = 'value1';
        $mockModel->created_at = Carbon::now();
        $mockModel->updated_at = Carbon::now();

        \Mockery::mock('alias:' . ObjectModel::class)
            ->shouldReceive('create')
            ->andReturn($mockModel);
        
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $result = $this->objectServices->createObject($request);

        $this->assertTrue($result['success']);
        $this->assertEquals('Data stored successfully', $result['message']);
        $this->assertArrayHasKey('data', $result);
    }

    public function testCreateObjectEmptyData()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->andReturn([]);

        $result = $this->objectServices->createObject($request);

        $this->assertFalse($result['success']);
        $this->assertEquals(1005, $result['errors']['code']);
        $this->assertEquals('No data provided', $result['errors']['message']);
    }

    public function testCreateObjectException()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->andReturn(['key1' => 'value1']);

        \Mockery::mock('alias:' . ObjectModel::class)
            ->shouldReceive('create')
            ->andThrow(new \Exception('Database error'));
        
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollback')->once();

        $result = $this->objectServices->createObject($request);

        $this->assertFalse($result['success']);
        $this->assertEquals(1007, $result['errors']['code']);
        $this->assertStringContainsString('Error storing data:', $result['errors']['message']);
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
        $this->assertEquals('test_value', $result['data']['value']);
    }

    public function testGetObjectKeyNotFound()
    {
        $request = ['key' => 'nonexistent_key'];

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('key', 'nonexistent_key')->andReturnSelf();
        DB::shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn(null);

        $result = $this->objectServices->getObject($request);

        $this->assertFalse($result['success']);
        $this->assertEquals(1006, $result['errors']['code']);
        $this->assertEquals('Key not found', $result['errors']['message']);
    }

    public function testGetObjectEmptyKey()
    {
        $request = [];

        $result = $this->objectServices->getObject($request);

        $this->assertFalse($result['success']);
        $this->assertEquals(1005, $result['errors']['code']);
        $this->assertEquals('No data provided', $result['errors']['message']);
    }

    public function testGetObjectByTimestampNotFound()
    {
        $request = ['key' => 'test_key', 'timestamp' => 1640995200];

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('key', 'test_key')->andReturnSelf();
        DB::shouldReceive('where')->with('created_at', '=', '2022-01-01 00:00:00')->andReturnSelf();
        DB::shouldReceive('orderBy')->with('created_at', 'desc')->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn(null);

        $result = $this->objectServices->getObject($request);

        $this->assertFalse($result['success']);
        $this->assertEquals(1009, $result['errors']['code']);
        $this->assertEquals('Key not found for the given timestamp', $result['errors']['message']);
    }

    public function testGetAllRecordsSuccess()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('input')->with('page', 1)->andReturn(1);
        $request->shouldReceive('input')->with('per_page', 5)->andReturn(10);

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

        $result = $this->objectServices->getAllRecords($request);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertEquals(1, $result['pagination']['current_page']);
        $this->assertEquals(10, $result['pagination']['per_page']);
        $this->assertEquals(1, $result['pagination']['total']);
    }

    public function testGetAllRecordsWithPagination()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('input')->with('page', 1)->andReturn(2);
        $request->shouldReceive('input')->with('per_page', 5)->andReturn(5);

        DB::shouldReceive('table')->with('timevault_objects')->andReturnSelf();
        DB::shouldReceive('count')->andReturn(15);
        DB::shouldReceive('orderBy')->with('created_at', 'asc')->andReturnSelf();
        DB::shouldReceive('limit')->with(5)->andReturnSelf();
        DB::shouldReceive('offset')->with(5)->andReturnSelf();
        DB::shouldReceive('get')->andReturn(collect([]));

        $result = $this->objectServices->getAllRecords($request);

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
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('input')->andReturn(1);

        DB::shouldReceive('table')->andThrow(new \Exception('Database connection failed'));

        $result = $this->objectServices->getAllRecords($request);

        $this->assertFalse($result['success']);
        $this->assertEquals(1007, $result['errors']['code']);
        $this->assertStringContainsString('Error retrieving data:', $result['errors']['message']);
    }
}