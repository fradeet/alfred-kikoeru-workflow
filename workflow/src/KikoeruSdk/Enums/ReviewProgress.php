<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Enums;

enum ReviewProgress: string
{
    case Marked = 'marked';
    case Listening = 'listening';
    case Listened = 'listened';
    case Replay = 'replay';
    case Postponed = 'postponed';
}
