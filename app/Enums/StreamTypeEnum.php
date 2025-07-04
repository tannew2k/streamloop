<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static LIVE_STUDIO()
 * @method static static MOBILE_1()
 * @method static static MOBILE_2()
 */
final class StreamTypeEnum extends Enum
{
    const LIVE_STUDIO = 0;
    const MOBILE_1 = 1;
    const MOBILE_2 = 2;
}
