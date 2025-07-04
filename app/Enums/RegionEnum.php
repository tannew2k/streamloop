<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static US()
 * @method static static VN()
 */
final class RegionEnum extends Enum
{
    const US = 'us';
    const VN = 'vn';
}
