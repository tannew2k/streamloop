<?php

namespace App\Jobs;

use App\Enums\LiveStatusEnum;
use App\Helpers\WebhookHelper;
use App\Models\Channel;
use App\Services\TikTokService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

class CreateStreamKeyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $channel;

    const PROXY_CONFIG_NAME = 'proxy';

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
        try {
            // Check proxy is valid
            $response = Http::withOptions([
                'proxy' => $this->channel['proxy'],
                'timeout' => 10,
            ])->get('https://ifconfig.io/all.json');
            $data = $response->json();
            if (! isset($data['ip'])) {
                Log::error('Invalid proxy: '.$this->channel['proxy']);
                WebhookHelper::updateChannel($this->channel['username'], [
                    'live_status' => LiveStatusEnum::ERROR,
                    'message' => 'Invalid proxy: '.$this->channel['proxy'],
                ]);
                return;
            }
            $ip = $data['ip'];
            $countryCode = $data['country_code'];
            Log::info('Proxy is valid: '.$this->channel['proxy'].' ('.$countryCode.'|'.$ip.')');

            /**
             * Download video from Google Drive
             */
            $googleDriveService = new GoogleDriveService($this->channel);
            $videoPath = $googleDriveService->execute();
            if ($videoPath === false) {
                Log::error('Error downloading video from Google Drive: '.$this->channel['video_url']);
                WebhookHelper::updateChannel($this->channel['username'], [
                    'live_status' => LiveStatusEnum::ERROR,
                    'message' => 'Error downloading video from Google Drive: '.$this->channel['video_url'],
                ]);
                return;
            }
            /**
             * Create stream key for TikTok
             */
            $tiktok = new TikTokService();
            $tiktok->setUsername($this->channel['username']);
            $streamInfo = $tiktok->run($this->channel);
            if (! $streamInfo) {
                Log::error('Error creating stream key for '.$this->channel['username']);
                WebhookHelper::updateChannel($this->channel['username'], [
                    'live_status' => LiveStatusEnum::ERROR,
                    'message' => 'Error creating stream key'.$this->channel['username']
                ]);
                return;
            }
            Log::info('Stream key created for '.$this->channel['username'].': '.json_encode([
                'stream_server' => $streamInfo['stream_server'],
                'stream_key' => $streamInfo['stream_key'],
            ]));

            // Start live stream
            $proxyName = md5($this->channel['username'].'|'.$this->channel['proxy']);
            $this->writeProxyConfig($this->channel['proxy'], $proxyName);
            $streamServer = $streamInfo['stream_server'];
            $streamKey = $streamInfo['stream_key'];
            $streamUrl = '"'.$streamServer.'/'.$streamKey.'"';
            // Start ffmpeg process for streaming video to TikTok server using RTMP, with encoding settings.
            // $command = "ffmpeg -re -stream_loop -1 -i $videoPath -threads 2 -c:v libx264 -preset veryfast -c:a copy -f flv $streamUrl";
            $command = "ffmpeg -re -stream_loop -1 -i $videoPath -pix_fmt yuv420p -vsync 1 -threads 1 -vcodec libx264 -r 25 -g 60 -sc_threshold 0 -b:v 512k -bufsize 640k -maxrate 640k -preset veryfast -profile:v baseline -tune film -acodec aac -b:a 128k -ac 2 -ar 48000 -af \"aresample=async=1:min_hard_comp=0.100000:first_pts=0\" -bsf:v h264_mp4toannexb -f flv $streamUrl";
            Log::info('Starting live stream: '.$command);
            $configPath = storage_path(self::PROXY_CONFIG_NAME.'/'.$proxyName.'.conf');
            // If config path does not exist, create it.
            if (! file_exists($configPath)) {
                $this->writeProxyConfig($this->channel['proxy'], $proxyName);
            }
            // Using proxychains to route the ffmpeg process through a proxy server.
            $proxyChainsCommand = "proxychains4 -f $configPath $command";
            Log::info('Starting live stream with proxy: '.$proxyChainsCommand);
            // Start process then get process ID.
            WebhookHelper::updateChannel($this->channel['username'], [
                'live_status' => LiveStatusEnum::ONLINE,
                'message' => 'Live stream has started',
            ]);
            // Delay for 5 seconds before starting the live stream.
            sleep(5);
            $process = Process::forever()->run($proxyChainsCommand);
            $errorCode = $process->exitCode();
            $errorOutput = $process->errorOutput();
            Log::error('Error code: '.$errorCode);
            Log::error('Error output: '.$errorOutput);
            if ($process->failed()) {
                Log::error('Error starting live stream for '.$this->channel['username']);
                WebhookHelper::updateChannel($this->channel['username'], [
                    'live_status' => LiveStatusEnum::OFFLINE,
                    'message' => 'Live stream has stopped',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating stream key for '.$this->channel['username'].': '.$e->getMessage());
            $line = $e->getLine();
            $file = $e->getFile();
            Log::error('Error creating stream key for '.$this->channel['username'].': '.$e->getMessage().' in '.$file.' on line '.$line);
            WebhookHelper::updateChannel($this->channel['username'], [
                'live_status' => LiveStatusEnum::ERROR,
                'message' => 'Error creating stream key: '.$e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Write proxy configuration to file.
     */
    private function writeProxyConfig(string $proxy, string $name): void
    {
        $allowedProtocol = ['socks4', 'socks5', 'http'];
        $configPath = storage_path(self::PROXY_CONFIG_NAME.'/'.$name.'.conf');
        $protocol = parse_url($proxy, PHP_URL_SCHEME);
        if (! in_array($protocol, $allowedProtocol)) {
            $protocol = 'socks5';
        }
        $protocol = strtolower($protocol);
        $host = parse_url($proxy, PHP_URL_HOST);
        $port = parse_url($proxy, PHP_URL_PORT);
        $user = parse_url($proxy, PHP_URL_USER);
        $pass = parse_url($proxy, PHP_URL_PASS);
        if ($user && $pass) {
            $proxy = "$protocol $host $port $user $pass";
        } else {
            $proxy = "$protocol $host $port";
        }
        $config = "strict_chain\nproxy_dns\nremote_dns_subnet 224\n\n[ProxyList]\n$proxy";
        // Write proxy configuration to file.
        $file = fopen($configPath, 'w+');
        fwrite($file, $config);
        fclose($file);
    }

    /**
     * Handle a job failure.
     * @param \Throwable $ex
     * @return void
     */
    public function failed(\Throwable $ex): void
    {
        Log::error('Error creating stream key for '.$this->channel['username'].': '.$ex->getMessage());
        WebhookHelper::updateChannel($this->channel['username'], [
            'live_status' => LiveStatusEnum::ERROR,
            'message' => 'Error creating stream key: '.$ex->getMessage(),
        ]);
    }
}
