<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Gaming()
 * @method static static Music()
 * @method static static ChatAndInterview()
 * @method static static BeautyAndFashion()
 * @method static static Dance()
 * @method static static FitnessAndSports()
 * @method static static Food()
 * @method static static NewsAndEvent()
 * @method static static Education()
 */
final class HashTagEnum extends Enum
{
    const Gaming = 5;

    const Music = 6;

    const ChatAndInterview = 42;

    const BeautyAndFashion = 9;

    const Dance = 3;

    const FitnessAndSports = 13;

    const Food = 4;

    const NewsAndEvent = 43;

    const Shopping = 44;

    const Education = 45;
}
