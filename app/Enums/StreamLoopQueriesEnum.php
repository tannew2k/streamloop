<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class StreamLoopQueriesEnum extends Enum
{
    // 'title=Lets%%20Go%%20LIVE%%21&live_studio=1&gen_replay=true&chat_auth=1&cover_uri=musically-maliva-obj%%2F1594805258216454&close_room_when_close_stream=false&hashtag_id=3&game_tag_id=0&screenshot_cover_status=1&chat_sub_only_auth=2&multi_stream_scene=0&gift_auth=1&chat_l2=1&star_comment_switch=true&multi_stream_source=1'
    const CREATE_STREAM_KEY = [
        // title, hashtag_id, cover_uri
        'title' => 'Lets Go LIVE!',
        'live_studio' => '1',
        'gen_replay' => 'true',
        'chat_auth' => '1',
        'cover_uri' => '',
        'close_room_when_close_stream' => 'false',
        'hashtag_id' => '3',
        'game_tag_id' => '0',
        'screenshot_cover_status' => '1',
        'chat_sub_only_auth' => '2',
        'live_sub_only' => '0',
        'multi_stream_scene' => '0',
        'gift_auth' => '1',
        'chat_l2' => '1',
        'star_comment_switch' => 'true',
        'multi_stream_source' => '1',
    ];
}
