<?php

namespace App\Jobs;

use App\Models\Diagnose;
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
    public function __construct(protected string $fileName, protected Diagnose $diagnoseModel)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(count($this->diagnoseModel->observations) > 1) {
            $this->sendForMultipleFiles();
        } else {
            $this->sendForSingleFile();
        }
        $this->diagnoseModel->update(['report' => "reports/{$this->fileName}"]);
    }

    private function sendForMultipleFiles(): void
    {
        $response = Http::timeout(10000)
            ->post(env('PROCESS_SERVER') . '/full_report', $this->diagnoseModel->observations);
        Storage::disk('public')->put("reports/{$this->fileName}", $response->body());
    }

    private function sendForSingleFile(): void
    {
        $response = Http::timeout(10000)
            ->post(env('PROCESS_SERVER') . '/report', $this->diagnoseModel->observations);
        Storage::disk('public')->put("reports/{$this->fileName}", $response->body());
    }
}
