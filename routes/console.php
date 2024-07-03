<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

Schedule::call(function () {
    $files = Storage::disk('public')->files('reports');
    $now = Carbon::now();

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $lastModified = Carbon::createFromTimestamp(Storage::disk('public')->lastModified($file));
            $diffInDays = $now->diffInDays($lastModified);

            if ($diffInDays > 1) {
                Storage::disk('public')->delete($file);
            }
        }
    }
})->everyFourHours();
