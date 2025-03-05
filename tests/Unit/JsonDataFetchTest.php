<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\TestCase;

class JsonDataFetchTest extends TestCase
{
    private string $usersFirstJson;
    private string $usersSecondJson;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usersFirstJson = json_encode([
            [
                "w88Amount" => 150,
                "Currency" => "USD",
                "w88Email" => "test1@example.com",
                "statusCode" => 1,
                "registrationData" => "2018-11-30",
                "w88Identification" => "id-001"
            ]
        ]);

        $this->usersSecondJson = json_encode([
            [
                "balance" => 200,
                "currency" => "EUR",
                "email" => "test2@example.com",
                "status" => 1,
                "created_at" => "2018-11-30",
                "id" => "id-002"
            ]
        ]);

        Storage::shouldReceive('exists')
            ->with('data/users/data1.json')->andReturn(true);
        Storage::shouldReceive('exists')
            ->with('data/users/data2.json')->andReturn(true);

        Storage::shouldReceive('get')
            ->with('data/users/data1.json')->andReturn($this->usersFirstJson);
        Storage::shouldReceive('get')
            ->with('data/users/data2.json')->andReturn($this->usersSecondJson);
    }

    /** @test */
    public function it_fetches_data_from_single_json_file()
    {
        $filePath = 'data/users/data1.json';

        $data = json_decode(Storage::get($filePath), true);

        $this->assertNotEmpty($data);
        $this->assertCount(1, $data);
        $this->assertEquals(150, $data[0]['w88Amount']);
        $this->assertEquals('USD', $data[0]['Currency']);
    }

    /** @test */
    public function it_fetches_data_from_multiple_json_files()
    {
        $files = ['data/users/data1.json', 'data/users/data2.json'];

        $mergedData = collect([]);

        foreach ($files as $file) {
            if (Storage::exists($file)) {
                $jsonData = json_decode(Storage::get($file), true);
                $mergedData = $mergedData->merge($jsonData);
            }
        }

        $this->assertCount(2, $mergedData);
        $this->assertEquals(150, $mergedData[0]['w88Amount']);
        $this->assertEquals(200, $mergedData[1]['balance']);
    }
}
