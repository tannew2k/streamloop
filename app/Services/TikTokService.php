<?php

namespace App\Services;

use App\Enums\StreamLoopHeadersEnum;
use App\Enums\StreamLoopParamsEnum;
use App\Enums\StreamLoopQueriesEnum;
use App\Helpers\WebhookHelper;
use App\Models\Channel;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException as GlobalInvalidArgumentException;
use LogicException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use App\Enums\LiveStatusEnum;

class TikTokService
{
    // Properties
    private string $username = '';

    private array $channel = [];

    private bool $isDebug = false;

    // Base URL
    private string $baseUrl = '';

    public function __construct()
    {
        $this->baseUrl = 'https://webcast16-normal-useast5.tiktokv.us';
        /** @disregard */
        if (app()->environment('local')) {
            $this->isDebug = true; // Enable debug mode
        }
    }

    /**
     * Get the value of channel
     */
    public function getChannel(): array
    {
        return $this->channel;
    }

    /**
     * Get the value of username
     *
     * @return Collection<array-key, mixed>|array
     */
    public static function fetchGameTags(): array|Collection
    {
        $url = 'https://webcast16-normal-c-useast2a.tiktokv.com/webcast/room/hashtag/list/';
        try {
            $response = Http::get($url);
            $game_tags = $response->json()['data']['game_tag_list'];

            return collect($game_tags)->mapWithKeys(function ($game) {
                return [$game['id'] => $game['show_name']];
            });
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return [];
        }
    }

    /**
     * Get the server URL
     */
    public function getServerUrl(): string
    {
        Log::info('Getting server URL with proxy: '.$this->channel['proxy']);
        $url = 'https://tnc16-platform-useast1a.tiktokv.com/get_domains/v4/?aid=8311&ttwebview_version=1130022001';
        if ($this->channel['proxy']) {
            $response = Http::withOptions([
                'retry' => 3, // Retry 3 times if the request fails
                'proxy' => $this->channel['proxy'],
                'verify' => false,
                'timeout' => 10, // Timeout in seconds
            ])->get($url);
        } else {
            $response = Http::get($url);
        }
        foreach ($response['data']['ttnet_dispatch_actions'] as $data) {
            if (isset($data['param']) && isset($data['param']['strategy_info']) && isset($data['param']['strategy_info']['webcast-normal.tiktokv.com'])) {
                $server_url = $data['param']['strategy_info']['webcast-normal.tiktokv.com'];

                return "https://{$server_url}";
            }
        }

        return '';
    }

    /**
     * Get the value of username
     *
     * @return self
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Get channel info
     *
     * @return self
     */
    public function getChannelInfo(): static
    {
        $channel = Channel::whereUsername($this->username)->firstOrFail();
    $this->channel = $channel->toArray();
        return $this;
    }

    /**
     * Set the value of channel
     * @param array $channel
     * @return self
     */
    public function setChannel(array $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Build the query
     */
    public static function buildQuery(array $params): string
    {
        $query = http_build_query($params, '', '&');

        return str_replace(['%28', '%29', '%2C'], ['(', ')', ','], $query);
    }

    /**
     * Get the value of cookies
     *
     * @throws InvalidArgumentException
     * @throws GlobalInvalidArgumentException
     * @throws LogicException
     */
    public function postLivePermission(): bool
    {
        $overrideParams = [
            'version_code' => config('tiktok.version_code'),
            'timezone_name' => config('tiktok.timezone_name'),
            'browser_version' => config('tiktok.browser_version'),
            'webcast_sdk_version' => config('tiktok.webcast_sdk_version'),
            'device_id' => '',
            'install_id' => '',
        ];
        $newParams = array_merge(StreamLoopParamsEnum::LIVE_PERMISSION, $overrideParams);
        $overrideHeaders = [
            'User-Agent' => config('tiktok.user_agent'),
            'Cookie' => $this->channel['cookies'],
        ];
        $newHeaders = array_merge(StreamLoopHeadersEnum::LIVE_PERMISSION, $overrideHeaders);
        $query = self::buildQuery($newParams);
        // Send the request
        $response = Http::logWhen($this->isDebug)
            ->baseUrl($this->baseUrl)
            ->withHeaders($newHeaders)
            ->withOptions([
                'retry' => 3, // Retry 3 times if the request fails
                'proxy' => $this->channel['proxy'],
                'verify' => false,
                'timeout' => 10, // Timeout in seconds
            ])
            ->post('/webcast/game/live_permission/judge_activity_permission/?'.$query, null);
        $jsonData = $response->json();
        $statusCode = isset($jsonData['status_code']) ? $jsonData['status_code'] : 1;
        if ($statusCode !== 0) {
            Log::error('Failed to get live permission', [
                'response' => $jsonData,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Update about me
     *
     * @throws InvalidArgumentException
     * @throws GlobalInvalidArgumentException
     * @throws LogicException
     */
    public function postAboutMe(): bool
    {
        $overrideHeaders = [
            'User-Agent' => config('tiktok.user_agent'),
            'Cookie' => $this->channel['cookies'],
        ];
        $newHeaders = array_merge(StreamLoopHeadersEnum::ABOUT_ME, $overrideHeaders);
        // Send the request
        $response = Http::logWhen($this->isDebug)
            ->baseUrl($this->baseUrl)
            ->withHeaders($newHeaders)
            ->withOptions([
                'retry' => 3, // Retry 3 times if the request fails
                'proxy' => $this->channel['proxy'],
                'verify' => false,
                'timeout' => 10, // Timeout in seconds
            ])
            ->asJson()
            ->post('/webcast/anchor/about_me/update/?aid=8311', ['method_type' => 2]);
        $jsonData = $response->json();
        $statusCode = $jsonData['status_code'];
        if ($statusCode !== 0) {
            Log::error('Failed to update about me', [
                'response' => $jsonData,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Post screenshot cover
     *
     * @throws InvalidArgumentException
     * @throws GlobalInvalidArgumentException
     * @throws LogicException
     */
    public function postScreenshotCover(): bool
    {
        $postData = ['status' => 1];
        $postDataString = self::buildQuery($postData);
        $overrideHeaders = [
            'Cookie' => $this->channel['cookies'],
            'User-Agent' => config('tiktok.user_agent'),
            'x-ss-stub' => md5($postDataString),
        ];
        $newHeaders = array_merge(StreamLoopHeadersEnum::SCREENSHOT_COVER, $overrideHeaders);
        $overrideParams = [
            'version_code' => config('tiktok.version_code'),
            'timezone_name' => config('tiktok.timezone_name'),
            'browser_version' => config('tiktok.browser_version'),
            'webcast_sdk_version' => config('tiktok.webcast_sdk_version'),
            'device_id' => '',
            'install_id' => '',
        ];
        $newParams = array_merge(StreamLoopParamsEnum::SCREENSHOT_COVER, $overrideParams);
        $query = self::buildQuery($newParams);
        // Send the request
        $response = Http::logWhen($this->isDebug)
            ->baseUrl($this->baseUrl)
            ->withHeaders($newHeaders)
            ->withOptions([
                'retry' => 3, // Retry 3 times if the request fails
                'proxy' => $this->channel['proxy'],
                'verify' => false,
                'timeout' => 10, // Timeout in seconds
            ])
            // application/x-www-form-urlencoded; charset=UTF-8
            ->asForm()
            ->post('/webcast/room/screenshot_cover/update/?'.$query, $postData);
        $jsonData = $response->json();
        $statusCode = $jsonData['status_code'];
        if ($statusCode !== 0) {
            Log::error('Failed to update screenshot cover', [
                'response' => $jsonData,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Post create stream key
     * @return false|array
     * @throws BindingResolutionException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws GlobalInvalidArgumentException
     * @throws LogicException
     */
    public function postCreateStreamKeyV2()
    {
        $baseUrl = config('tiktok.worker_csk_url');
        $data = [
            'title' => $this->channel['title'],
            'category' => $this->channel['hash_tag_id'],
            'streaming_type' => $this->channel['stream_type'],
            'proxy' => $this->channel['proxy'],
            'cookies' => $this->channel['cookies'],
            'region' => $this->channel['region'],
        ];
        $client = new Client([
            'base_uri' => $baseUrl,
            'timeout' => 60,
            'verify' => false,
        ]);
        if (!empty($this->channel['install_id']) && !empty($this->channel['device_id']) && !empty($this->channel['openduid'])) {
            // Use existing device_id, install_id, openudid
            $data['install_id'] = $this->channel['install_id'];
            $data['device_id'] = $this->channel['device_id'];
            $data['openudid'] = $this->channel['openudid'];
            $request = $client->request('POST', '/streamkey', [
                'json' => $data,
            ]);
        } else {
            // Generate new device_id, install_id, openudid
            $request = $client->request('POST', '/streamkey/new', [
                'json' => $data,
            ]);
        }
        $content = $request->getBody()->getContents();
        $jsonData = json_decode($content, true);
        if (isset($jsonData['error'])) {
            Log::error('Failed to get stream key', [
                'response' => $data,
            ]);
            return false;
        }
        $device = $jsonData['device'];
        $device_id = $device['device_id'];
        $install_id = $device['install_id'];
        $openudid = $device['openudid'];
        WebhookHelper::updateChannel($this->channel['username'], [
            'device_id' => $device_id,
            'install_id' => $install_id,
            'openudid' => $openudid,
        ]);
        // Update channel info
        $stream = $jsonData['stream'];
        $stream_key = $stream['key'];
        $stream_url = $stream['url'];
        return [
            'stream_server' => $stream_url,
            'stream_key' => $stream_key,
        ];
    }

    
    /**
     * Run the service
     *
     * @throws InvalidArgumentException
     * @throws GlobalInvalidArgumentException
     * @throws LogicException
     */
    public function run(array $channel): array|bool
    {
        // Get channel info
        $this->setChannel($channel);

        // // Get server URL
        // $this->baseUrl = $this->getServerUrl();

        // // Set base URL
        // Log::info('Base URL', ['base_url' => $this->baseUrl]);

        // // Get live permission
        // if ($this->postLivePermission() === false) {
        //     Log::error('Failed to get live permission');

        //     return false;
        // }
        // Update about me
        // if ($this->postAboutMe() === false) {
        //     Log::error('Failed to update about me');

        //     return false;
        // }
        // // Update screenshot cover
        // if ($this->postScreenshotCover() === false) {
        //     Log::error('Failed to update screenshot cover');

        //     return false;
        // }

        // Create stream key
        $result = $this->postCreateStreamKeyV2();
        if ($result === false) {
            Log::error('Failed to get stream key');

            return false;
        }
        return $result;
    }
}
