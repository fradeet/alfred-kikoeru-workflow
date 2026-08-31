<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/** How new response text updates the existing Text View content. */
enum AlfredTVBehaviourResponse: string
{
    /** Replace all content with the new response. */
    case Replace = 'replace';

    /** Add the new response to the bottom of the view. */
    case Append = 'append';

    /** Add the new response to the top of the view. */
    case Prepend = 'prepend';

    /** Replace the content produced by the previous response. */
    case ReplaceLast = 'replacelast';
}
