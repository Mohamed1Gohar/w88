<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

use App\Services\ProcessJsonFileService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public $processJsonFileServise;
    protected array $filePaths;
    protected array $map;
    public function __construct()
    {

        $this->map = [
            'w88Amount' => 'amount',
            'balance' => 'amount',

            'Currency' => 'currency',
            'currency' => 'currency',

            'w88Email' => 'email',
            'email' => 'email',

            'statusCode' => 'status',
            'status' => 'status',

            'registrationData' => 'created_at',
            'created_at' => 'created_at',

            'w88Identification' => 'id',
            'id' => 'id',
        ];
    }

    public function index(Request $request, ProcessJsonFileService $processJsonFileServise) {

        $provider = $request->provider;
        $fileMap = [
            'data1.json',
            'data2.json'
        ];


        if (!empty($fileMap) && in_array("$provider.json", $fileMap)) {
            $data = $processJsonFileServise->processLargeJsonFiles(["$provider.json"])
                ->map(fn($record) => $this->normalizeData($record));
        } else {
            $data = $processJsonFileServise->processLargeJsonFiles($fileMap)
                ->map(fn($record) => $this->normalizeData($record));
        }

//        $filters = array_filter(request()->all());

        return $data->merge($data);
    }



    private function normalizeData(array $record): array
    {
        $normalized = [];
        foreach ($record as $key => $value) {
            $normalized[$this->map[$key] ?? $key] = $value;
        }
        return $normalized;
    }


    private function applyFilters(Collection $data, array $filters): Collection
    {
        $filters = array_filter($filters);

        return empty($filters) ? $data : $data->filter(function ($record) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($key === 'balanceMin' && ($record['balance'] ?? 0) < $value) return false;
                if ($key === 'balanceMax' && ($record['balance'] ?? 0) > $value) return false;
                if ($key === 'amountMin' && ($record['amount'] ?? 0) < $value) return false;
                if ($key === 'amountMax' && ($record['amount'] ?? 0) > $value) return false;
                if ($key === 'statusCode' && ($record['status_code'] ?? '') != $value) return false;
                if (!in_array($key, ['balanceMin', 'balanceMax', 'amountMin', 'amountMax', 'status_code']) &&
                    (!isset($record[$key]) || $record[$key] != $value)) {
                    return false;
                }
            }
            return true;
        });
    }


}
