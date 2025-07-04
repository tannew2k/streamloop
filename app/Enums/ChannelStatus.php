<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ACTIVE()
 * @method static static INACTIVE()
 */
final class ChannelStatus extends Enum
{
    const ACTIVE = 1;

    const INACTIVE = 0;
}
