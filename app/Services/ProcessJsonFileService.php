<?php

namespace App\Services;

use App\Jobs\ProcessJsonFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Ramsey\Collection\Collection;

class ProcessJsonFileService
{

    function processLargeJsonFiles(array $filePaths)
    {
        $cacheKey = 'merged_json_data';

        return collect(Cache::remember($cacheKey, now()->addMinutes(60), function () use ($filePaths) {
            $dataX = collect(json_decode(Storage::get($filePaths[0]), true));
            $dataY = collect(json_decode(Storage::get($filePaths[1]), true));

            return $dataX->merge($dataY)->toArray();
        }));

    }



    function normalizeData(array $record): array
    {
        $map = [
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

        $normalized = [];
        foreach ($record as $key => $value) {
            $normalized[$map[$key] ?? $key] = $value;
        }

        return $normalized;
    }


    // Process Three JSON Files
    public function processJsonFiles()
    {
        $files = ['dataProviderX.json', 'dataProviderY.json', 'dataProviderZ.json'];

        foreach ($files as $file) {
            if (Storage::exists("data/$file")) {
                ProcessJsonFile::dispatch($file);
            }
        }

        return response()->json(['message' => 'Processing started...'], 202);
    }

    // Retrieve JSON Data with Pagination & Caching
    public function getJsonData()
    {
        $cacheKey = 'json_data';

        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () {
//            return JsonData::paginate(50);
        });

        return response()->json($data);
    }


    ///- ------------------------
    ///


//    protected array $filePaths;
//    protected array $map;
//
//    public function __construct(array $filePaths)
//    {
//        $this->filePaths = $filePaths;
//
//        // خريطة توحيد أسماء المفاتيح
//        $this->map = [
//            'full_name' => 'name',
//            'user_name' => 'name',
//            'email_address' => 'email',
//            'contact' => 'phone',
//            'mobile' => 'phone',
//        ];
//    }
//
//    /**
//     * تحميل البيانات كسطر بسطر باستخدام LazyCollection
//     */
//    public function loadJsonData(): LazyCollection
//    {
//        return LazyCollection::make(function () {
//            foreach ($this->filePaths as $filePath) {
//                $stream = Storage::disk('local')->readStream($filePath);
//
//                while (($line = fgets($stream)) !== false) {
//                    yield json_decode($line, true);
//                }
//
//                fclose($stream);
//            }
//        });
//    }
//
//    /**
//     * توحيد أسماء المفاتيح
//     */
//    private function normalizeData(array $record): array
//    {
//        $normalized = [];
//        foreach ($record as $key => $value) {
//            $normalized[$this->map[$key] ?? $key] = $value;
//        }
//        return $normalized;
//    }
//
//    /**
//     * إرجاع البيانات المفلترة
//     */
//    public function getFilteredData(): LazyCollection
//    {
//        return $this->loadJsonData()
//            ->map(fn($record) => $this->normalizeData($record))
//            ->filter(fn($record) => !empty($record['email']) && str_contains($record['email'], '@gmail.com'));
//    }

}
