<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class StreamLoopHeadersEnum extends Enum
{
    const LIVE_PERMISSION = [
        'Connection' => 'keep-alive',
        'Accept' => 'application/json, text/plain, */*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'en-US',
        'Connection' => 'keep-alive',
        'Sec-Fetch-Dest' => 'empty',
        'Sec-Fetch-Mode' => 'cors',
        'Sec-Fetch-Site' => 'cross-site',
        'User-Agent' => '',
        'X-SS-DP' => '',
        'X-SS-TC' => '',
        'sdk_aid' => '8311',
        'sec-ch-ua' => '"Not?A_Brand";v="8", "Chromium";v="108"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Windows"',
        'webcast-ntp-t0' => '',
        'x-tt-store-region' => 'us',
    ];

    const ABOUT_ME = [
        'Connection' => 'keep-alive',
        'Accept' => '*/*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'en-US',
        'Content-Type' => 'application/json',
        'Sec-Fetch-Dest' => 'empty',
        'Sec-Fetch-Mode' => 'cors',
        'Sec-Fetch-Site' => 'cross-site',
        'User-Agent' => '',
        'X-SS-DP' => '',
        'X-SS-TC' => '',
        'sec-ch-ua' => '"Not?A_Brand";v="8", "Chromium";v="108"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Windows"',
    ];

    const SCREENSHOT_COVER = [
        'Connection' => 'keep-alive',
        'Accept' => 'application/json, text/plain, */*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'en-US',
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        'Sec-Fetch-Dest' => 'empty',
        'Sec-Fetch-Mode' => 'cors',
        'Sec-Fetch-Site' => 'cross-site',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) TikTokLIVEStudio/0.57.0 Chrome/108.0.5359.215 Electron/22.3.18-tt.8.release.main.26 TTElectron/22.3.18-tt.8.release.main.26 Safari/537.36',
        'X-SS-DP' => '',
        'X-SS-TC' => '',
        'sdk_aid' => '8311',
        'sec-ch-ua' => '"Not?A_Brand";v="8", "Chromium";v="108"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Windows"',
        'webcast-ntp-t0' => '',
        'x-ss-stub' => '',
        'x-tt-store-region' => 'us',
    ];

    const CREATE_STREAM_KEY = [
        'Connection' => 'keep-alive',
        'Accept' => 'application/json, text/plain, */*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'en-US',
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        'Sec-Fetch-Dest' => 'empty',
        'Sec-Fetch-Mode' => 'cors',
        'Sec-Fetch-Site' => 'cross-site',
        'User-Agent' => '',
        'X-SS-DP' => '',
        'X-SS-TC' => '',
        'sdk_aid' => '8311',
        'sec-ch-ua' => '"Not?A_Brand";v="8", "Chromium";v="108"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Windows"',
        'webcast-ntp-t0' => '',
        'x-ss-stub' => '',
        'x-tt-store-region' => 'us',
    ];

    const END_LIVESTREAM = [
        'Connection' => 'keep-alive',
        'Accept' => 'application/json, text/plain, */*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'en-US',
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        'Sec-Fetch-Dest' => 'empty',
        'Sec-Fetch-Mode' => 'cors',
        'Sec-Fetch-Site' => 'cross-site',
        'User-Agent' => '',
        'X-SS-DP' => '',
        'X-SS-TC' => '',
        'sdk_aid' => '8311',
        'sec-ch-ua' => '"Not?A_Brand";v="8", "Chromium";v="108"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Windows"',
        'webcast-ntp-t0' => '',
        'x-ss-stub' => '',
        'x-tt-store-region' => 'us',
    ];
}
