<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Enums;

enum UserGroup: string
{
    case Administrator = 'administrator';
    case User = 'user';
    case Guest = 'guest';
}
