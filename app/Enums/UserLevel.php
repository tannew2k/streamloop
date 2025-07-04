<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static FREE()
 * @method static static BRONZE()
 * @method static static SILVER()
 * @method static static GOLD()
 */
final class UserLevel extends Enum
{
    const FREE = 0;

    const BRONZE = 1;

    const SILVER = 2;

    const GOLD = 3;
}
