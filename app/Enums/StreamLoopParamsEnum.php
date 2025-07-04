<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class StreamLoopParamsEnum extends Enum
{
    const LIVE_PERMISSION = [
        'aid' => '8311',
        'app_name' => 'tiktok_live_studio',
        'device_id' => '',
        'install_id' => '',
        'channel' => 'studio',
        'version_code' => '', // required
        'device_platform' => 'windows',
        'timezone_name' => '', // required
        'screen_width' => '1920',
        'screen_height' => '1080',
        'browser_language' => 'en-US',
        'browser_platform' => 'Win32',
        'browser_name' => 'Mozilla',
        'browser_version' => '', // required
        'language' => 'en',
        'app_language' => 'en',
        'webcast_language' => 'en',
        'priority_region' => 'us',
        'webcast_sdk_version' => '570', // required
        'live_mode' => '6',
    ];

    const SCREENSHOT_COVER = [
        'aid' => '8311',
        'app_name' => 'tiktok_live_studio',
        'device_id' => '',
        'install_id' => '',
        'channel' => 'studio',
        'version_code' => '', // required
        'device_platform' => 'windows',
        'timezone_name' => '', // required
        'screen_width' => '1920',
        'screen_height' => '1080',
        'browser_language' => 'en-US',
        'browser_platform' => 'Win32',
        'browser_name' => 'Mozilla',
        'browser_version' => '', // required
        'language' => 'en',
        'app_language' => 'en',
        'webcast_language' => 'en',
        'priority_region' => 'us',
        'webcast_sdk_version' => '570', // required
        'live_mode' => '6',
    ];

    const CREATE_STREAM_KEY = [
        'aid' => '8311',
        'app_name' => 'tiktok_live_studio',
        'device_id' => '',
        'install_id' => '',
        'channel' => 'studio',
        'version_code' => '', // required
        'device_platform' => 'windows',
        'timezone_name' => '', // required
        'screen_width' => '1920',
        'screen_height' => '1080',
        'browser_language' => 'en-US',
        'browser_platform' => 'Win32',
        'browser_name' => 'Mozilla',
        'browser_version' => '', // required
        'language' => 'en',
        'app_language' => 'en',
        'webcast_language' => 'en',
        'priority_region' => 'us',
        'webcast_sdk_version' => '570', // required
        'live_mode' => '6',
    ];

    const END_LIVESTREAM = [
        'aid' => '8311',
        'app_name' => 'tiktok_live_studio',
        'device_id' => '',
        'install_id' => '',
        'channel' => 'studio',
        'version_code' => '', // required
        'device_platform' => 'windows',
        'timezone_name' => '', // required
        'screen_width' => '1920',
        'screen_height' => '1080',
        'browser_language' => 'en-US',
        'browser_platform' => 'Win32',
        'browser_name' => 'Mozilla',
        'browser_version' => '', // required
        'language' => 'en',
        'app_language' => 'en',
        'webcast_language' => 'en',
        'priority_region' => 'us',
        'webcast_sdk_version' => '570', // required
        'live_mode' => '6',
    ];
}
