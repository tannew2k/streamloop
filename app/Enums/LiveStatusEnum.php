<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Class LiveStatusEnum
 */
final class LiveStatusEnum extends Enum
{
    const OFFLINE = 'offline';

    const ONLINE = 'online';

    const CREATING = 'creating';

    const STARTING = 'starting';

    const DOWNLOADING = 'downloading';

    const ERROR = 'error';

    const STOPPING = 'stopping';
}
