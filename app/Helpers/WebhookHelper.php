<?php

namespace App\Helpers;

use GuzzleHttp\Exception\GuzzleException;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WebhookHelper
{
    private static $instance;
    private $url = '';
    private $isSuccessCalled = false;
    private $isFailedCalled = false;
    private $data = [];
    private $webhookSecret;
    private $message = '';

    private function __construct()
    {
        $this->webhookSecret = Config::get('tiktok.webhook_secret');
        $this->url = env('WEBHOOK_URL', env('APP_URL') . '/api/webhook');
    }

    /**
     * Call the WebhookHelper
     *
     * @param array $data
     * @return WebhookHelper
     */
    public static function call($data = [])
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        self::$instance->data = $data;
        return self::$instance;
    }

    /**
     * Set the webhook secret
     *
     * @param string $webhookSecret
     * @return WebhookHelper
     */
    public function withMessage($message)
    {
        $this->data['message'] = $message;
        return $this;
    }

    public function __destruct()
    {
        if (!$this->isSuccessCalled && !$this->isFailedCalled) {
            if ($this->message !== '') {
                $this->sendMessage(false, $this->message, $this->data);
            } else {
                $this->sendMessage(false, 'No message', $this->data);
            }
        }
    }

    /**
     * Send a success message
     */
    public function success()
    {
        $this->isSuccessCalled = true;
        if ($this->message !== '') {
            $this->sendMessage(true, $this->message, $this->data);
            return;
        }
        $this->sendMessage(true, 'Success message', $this->data);
    }

    /**
     * Send a failed message
     */
    public function failed()
    {
        $this->isFailedCalled = true;
        if ($this->message !== '') {
            $this->sendMessage(true, $this->message, $this->data);
            return;
        }
        $this->sendMessage(false, 'Failed message', $this->data);
    }

    /**
     * Send a message
     *
     * @param boolean $status
     * @param string $message
     * @param array $data
     */
    private function sendMessage($status, $message, $data)
    {
        $payload = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
        
        $response = Http::withHeaders([
            'Authorization' => $this->webhookSecret
        ])->post(self::$url, $payload);
        
        if ($response->failed()) {
            Log::error('Error sending webhook message', [
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ]);
        }
    }

    /**
     * Update the channel
     * @param array $data
     * @return void
     * @throws InvalidArgumentException
     * @throws GuzzleException
     * @throws Exception
     */
    public static function updateChannel(string $username, array $data)
    {
        $data['username'] = $username;
        $data['worker_name'] = env('WORKER_ID');
        $client = new \GuzzleHttp\Client([
            'base_uri' => env('APP_URL'),
            'timeout' => 10,
        ]);
        $response = $client->request('POST', "/api/webhook/channels", [
            'json' => [
                'data' => $data,
            ],
            'headers' => [
                'Authorization' => Config::get('tiktok.webhook_secret'),
            ],
        ]);
        $jsonString = json_encode($data, JSON_PRETTY_PRINT);
        Log::info('Channel updated: ' . PHP_EOL . $jsonString);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to update channel');
        }
    }
}
