<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ADMIN()
 * @method static static USER()
 * @method static static MODERATOR()
 */
final class UserType extends Enum
{
    const ADMIN = 1;

    const MANAGER = 2;

    const USER = 3;
}
