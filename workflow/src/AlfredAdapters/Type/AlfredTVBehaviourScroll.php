<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/** Where the Text View scrolls after receiving a response. */
enum AlfredTVBehaviourScroll: string
{
    /** Scroll to the start of the new response. */
    case Auto = 'auto';

    /** Scroll to the start of the Text View. */
    case Start = 'start';

    /** Scroll to the end of the Text View. */
    case End = 'end';
}
