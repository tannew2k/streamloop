<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static GAMING()
 * @method static static MUSIC()
 * @method static static CHAT_INTERVIEW()
 * @method static static BEAUTY_FASHION()
 * @method static static DANCE()
 * @method static static FITNESS_SPORTS()
 * @method static static FOOD()
 * @method static static NEWS_EVENT()
 * @method static static EDUCATION()
 */
final class StreamLoopTopicsEnum extends Enum
{
    const GAMING = 5;

    const MUSIC = 6;

    const CHAT_INTERVIEW = 42;

    const BEAUTY_FASHION = 9;

    const DANCE = 3;

    const FITNESS_SPORTS = 13;

    const FOOD = 4;

    const NEWS_EVENT = 43;

    const EDUCATION = 45;
}
