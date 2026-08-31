<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/** How Alfred should interpret and validate a result item. */
enum AlfredSFItemType: string
{
    /** A normal, non-file result. */
    case Default = 'default';

    /** A file result; Alfred verifies that the path exists before displaying it. */
    case File = 'file';

    /** A file result for which Alfred skips the path-existence check. */
    case FileSkipcheck = 'file:skipcheck';
}
