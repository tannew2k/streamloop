<?php

namespace App\Jobs;

use App\Enums\LiveStatusEnum;
use App\Models\Channel;
use App\Services\TikTokService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EndLiveStreamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $channel;

    /**
     * Create a new job instance.
     */
    public function __construct(array $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tiktok = new TikTokService();
        $tiktok->setUsername($this->channel['username']);
        $tiktok->setChannel($this->channel);
        $result = $tiktok->postEndLiveStream();
        if ($result === true) {
            Channel::update([
                'username' => $this->channel['username'],
            ], [
                'process_id' => null,
                'meta' => null,
                'live_status' => LiveStatusEnum::OFFLINE,
            ]);
            Log::info('Stream ended for '.$this->channel['username']);
            return;
        }
        Log::error('Failed to end stream for '.$this->channel['username']);
    }
}
