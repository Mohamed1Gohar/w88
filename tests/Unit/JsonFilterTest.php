<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Filters\JsonFilter;
use Illuminate\Support\Facades\Storage;

class JsonFilterTest extends TestCase
{
    private string $usersFirst;
    private string $usersSecond;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usersFirst = json_encode([
            [
                "w88Amount" => 150,
                "Currency" => "USD",
                "w88Email" => "test1@example.com",
                "statusCode" => 1,
                "registrationData" => "2018-11-30",
                "w88Identification" => "id-001"
            ],
            [
                "w88Amount" => 300,
                "Currency" => "EUR",
                "w88Email" => "test2@example.com",
                "statusCode" => 1,
                "registrationData" => "2018-11-30",
                "w88Identification" => "id-002"
            ]
        ]);

        $this->usersSecond = json_encode([
            [
                "balance" => 250,
                "currency" => "USD",
                "email" => "test3@example.com",
                "status" => 1,
                "created_at" => "2018-11-30",
                "id" => "id-003"
            ],
            [
                "balance" => 400,
                "currency" => "GBP",
                "email" => "test4@example.com",
                "status" => 1,
                "created_at" => "2018-11-30",
                "id" => "id-004"
            ]
        ]);

        Storage::shouldReceive('exists')
            ->with('data/users/data1.json')->andReturn(true);
        Storage::shouldReceive('exists')
            ->with('data/users/data2.json')->andReturn(true);

        Storage::shouldReceive('get')
            ->with('data/users/data1.json')->andReturn($this->usersFirst);
        Storage::shouldReceive('get')
            ->with('data/users/data2.json')->andReturn($this->usersSecond);
    }

    /** @test */
    public function it_loads_and_merges_json_data()
    {
        $filter = new JsonFilter(['data/users/data1.json', 'data/users/data2.json']);
        $data = $filter->get();

        $this->assertCount(4, $data);
    }

    /** @test */
    public function it_filters_by_min_amount()
    {
        $filter = new JsonFilter(['data/users/data1.json', 'data/users/data2.json']);
        $filteredData = $filter->applyFilters(['min_amount' => 250])->get();

        $this->assertCount(3, $filteredData);
        $this->assertTrue($filteredData->every(fn ($item) => $item['amount'] >= 250));
    }

    /** @test */
    public function it_filters_by_max_amount()
    {
        $filter = new JsonFilter(['data/users/data1.json', 'data/users/data2.json']);
        $filteredData = $filter->applyFilters(['max_amount' => 250])->get();
        $this->assertCount(5, $filteredData);
        $this->assertTrue($filteredData->every(fn ($item) => $item['amount'] <= 250));
    }

    /** @test */
    public function it_filters_by_min_and_max_amount()
    {
        $filter = new JsonFilter(['data/users/data1.json', 'data/users/data2.json']);
        $filteredData = $filter->applyFilters(['min_amount' => 200, 'max_amount' => 350])->get();

        $this->assertCount(1, $filteredData);
        $this->assertTrue($filteredData->every(fn ($item) => $item['amount'] >= 200 && $item['amount'] <= 350));
    }

    /** @test */
    public function it_filters_by_currency()
    {
        $filter = new JsonFilter(['data/users/data1.json', 'data/users/data2.json']);
        $filteredData = $filter->applyFilters(['currency' => 'USD'])->get();

        $this->assertCount(3, $filteredData);
        $this->assertTrue($filteredData->every(fn ($item) => $item['currency'] === 'USD'));
    }

    /** @test */
    public function it_filters_by_email()
    {
        $filter = new JsonFilter(['data/users/data1.json', 'data/users/data2.json']);
        $filteredData = $filter->applyFilters(['email' => 'ahmed@gmail.com'])->get();

        $this->assertCount(1, $filteredData);
        $this->assertEquals('ahmed@gmail.com', $filteredData->first()['email']);
    }
}
