<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessJsonFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $handle = fopen(storage_path("app/data/$this->filePath"), 'r');
        if (!$handle) return;

        $jsonData = '';

        while (!feof($handle)) {
            $jsonData .= fread($handle, 8192); // Read in chunks
        }

        fclose($handle);

        $data = json_decode($jsonData, true);

        // Bulk insert for better performance
        $chunks = array_chunk($data, 1000);
        foreach ($chunks as $chunk) {
            JsonData::insert($chunk);
        }
    }
}
