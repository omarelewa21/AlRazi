<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $fileName, protected array $payloadObservations)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::timeout(10000)
            ->post(env('PROCESS_SERVER') . '/report', $this->payloadObservations);
        Storage::disk('public')->put("reports/{$this->fileName}", $response->body());
    }
}
