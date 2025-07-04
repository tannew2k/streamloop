<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Enums\LiveStatusEnum;

class GoogleDriveService
{
    private array $channel;

    public function __construct(array $channel) {
        $this->channel = $channel;
    }

    /**
     * Check if file exists
     */
    public function isFileIdExists(string $fileId): bool
    {
        if (Storage::disk('public')->exists("$fileId.mp4")) {
            return true;
        }

        return false;
    }

    /**
     * Download Google Drive video
     */
    public function download(string $videoUrl): ?string
    {
        try {
            preg_match('/file\/d\/([^\/]+)/', $videoUrl, $matches);
            if ($matches === false) {
                Log::error('Error downloading video', [
                    'url' => $videoUrl,
                ]);

                return null;
            }
            $fileId = $matches[1];
            $fileHash = md5($fileId);
            if ($this->isFileIdExists($fileHash)) {
                return storage_path("app/public/$fileHash.mp4");
            }
            $response = Http::withOptions([
                'follow_redirects' => true,
            ])
                ->get("https://drive.usercontent.google.com/download?id=$fileId&export=download");
            $html = $response->body();
            if (Str::contains($html, 'name="uuid"') === false) {
                Log::error('Error downloading video', [
                    'url' => $videoUrl,
                ]);

                return null;
            }
            $uuid = Str::between($html, 'name="uuid" value="', '"');
            $downloadUrl = "https://drive.usercontent.google.com/download?id=$fileId&export=download&confirm=t&uuid=$uuid";
            $response = Http::withOptions([
                'follow_redirects' => true,
                'stream' => true,
                'sink' => storage_path("app/public/$fileHash.mp4"),
                'timeout' => 0,
            ])
                ->get($downloadUrl);
            // Download video as stream
            $stream = $response->getBody();
            $filename = md5($fileId).'.mp4';
            $path = storage_path("app/public/$filename");
            $file = fopen($path, 'w');
            while (! $stream->eof()) {
                fwrite($file, $stream->read(1024));
            }
            fclose($file);

            return $path;
        } catch (\Exception $e) {
            Log::error('Error downloading video', [
                'url' => $videoUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Execute download video
     * @return false|string
     */
    public function execute()
    {
        $videoUrl = $this->channel['video_url'];
        $path = $this->download($videoUrl);
        if ($path === null) {
            return false;
        }
        return $path;
    }
}