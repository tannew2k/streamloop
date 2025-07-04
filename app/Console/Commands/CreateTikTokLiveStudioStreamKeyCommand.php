<?php

namespace App\Console\Commands;

use App\Services\TikTokService;
use Illuminate\Console\Command;

class CreateTikTokLiveStudioStreamKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:stream-key {channel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a TikTok Live Studio Stream Key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the channel name
        $channelName = $this->argument('channel');
        if (empty($channelName)) {
            $this->error('Please provide a channel name');

            return;
        }

        $this->info('Creating TikTok Live Studio Stream Key for '.$channelName);

        // Check if the channel exists
        $tiktok = new TikTokService();
        $tiktok->setUsername($channelName);
        $tiktok->run($channelName);
    }
}
