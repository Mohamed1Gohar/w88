<?php

namespace App\Filters;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class JsonFilter
{
    protected Collection $data;

    public function __construct(array $filePaths)
    {
        $this->data = collect();

        foreach ($filePaths as $filePath) {
            if (Storage::exists($filePath)) {
                $json = Storage::get($filePath);
                $decodedData = json_decode($json, true);

                if (is_array($decodedData)) {
                    $normalizedData = collect($decodedData)->map(fn($item) => $this->normalizeData($item, $filePath));
                    $this->data = $this->data->merge($normalizedData);
                }
            }
        }
    }

    private function normalizeData(array $item, string $filePath): array
    {
        $mapping = [
            'data/users/data1.json' => [
                'amount' => 'w88Amount',
                'currency' => 'Currency',
                'email' => 'w88Email',
                'status' => 'statusCode',
                'created_at' => 'registrationData',
                'id' => 'w88Identification'
            ],
            'data/users/data2.json' => [
                'amount' => 'balance',
                'currency' => 'currency',
                'email' => 'email',
                'status' => 'status',
                'created_at' => 'created_at',
                'id' => 'id'
            ]
        ];

        $map = $mapping[$filePath] ?? [];

        return [
            'amount' => $item[$map['amount']] ?? null,
            'currency' => $item[$map['currency']] ?? null,
            'email' => $item[$map['email']] ?? null,
            'status' => $item[$map['status']] ?? null,
            'created_at' => $item[$map['created_at']] ?? null,
            'id' => $item[$map['id']] ?? null
        ];
    }

    public function applyFilters(array $filters): self
    {
        foreach ($filters as $key => $value) {
            if ($key === 'min_amount') {
                $this->data = $this->data->filter(fn($item) => $item['amount'] >= $value);
            } elseif ($key === 'max_amount') {
                $this->data = $this->data->filter(fn($item) => $item['amount'] <= $value);
            } elseif ($this->data->first() && array_key_exists($key, $this->data->first())) {
                $this->data = $this->data->filter(fn($item) => str_contains(strtolower($item[$key]), strtolower($value)));
            }
        }

        return $this;
    }

    public function get(): Collection
    {
        return $this->data->values();
    }
}
