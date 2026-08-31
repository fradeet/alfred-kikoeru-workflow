<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/** What happens to the Text View input field after actioning it. */
enum AlfredTVBehaviourInputField: string
{
    /** Erase the input field text. */
    case Clear = 'clear';

    /** Select the input field text. */
    case Select = 'select';
}
