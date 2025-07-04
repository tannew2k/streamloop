<?php

/**
 * TikTok configuration
 */
$config = [
    'browser_version' => '',
    'webcast_sdk_version' => '582',
    'version_code' => '0.58.2',
    'timezone_name' => 'America/New_York',
    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) TikTokLIVEStudio/0.58.2 Chrome/108.0.5359.215 Electron/22.3.18-tt.8.release.main.31 TTElectron/22.3.18-tt.8.release.main.31 Safari/537.36',
    'sec_ch_ua' => '"Not?A_Brand";v="8", "Chromium";v="108"',
    'webhook_secret' => 'StreamLoop@123',
    'worker_csk_url' => env('WORKER_CSK_URL', 'http://127.0.0.1:31415'),
];
// Set the browser version
$config['browser_version'] = preg_replace('/^Mozilla\//', '', $config['user_agent']);

return $config;
