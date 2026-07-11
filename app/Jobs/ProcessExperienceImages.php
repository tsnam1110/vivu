<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Experience;
use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProcessExperienceImages implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $experienceId) {}

    public function handle(): void
    {
        $experience = Experience::query()->with('media')->find($this->experienceId);
        if (! $experience) {
            return;
        }

        foreach ($experience->media as $media) {
            $this->processOne($media);
        }
    }

    private function processOne(Media $media): void
    {
        if (! Storage::disk($media->disk)->exists($media->path)) {
            return;
        }

        try {
            $fullPath = Storage::disk($media->disk)->path($media->path);
            $image = Image::read($fullPath);
            $image->scaleDown(width: 1600);
            $image->save($fullPath);

            $media->update([
                'width' => $image->width(),
                'height' => $image->height(),
            ]);
        } catch (\Throwable) {
            // Image processing is best-effort; keep original file.
        }
    }
}
