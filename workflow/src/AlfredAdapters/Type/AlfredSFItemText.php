<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * Text used when the user copies a result or displays it in Large Type.
 *
 * @property string $copy      Text copied with Command-C.
 * @property string $largetype Text displayed with Command-L.
 */
class AlfredSFItemText extends AlfredSFBase
{
    public function __construct(
        public string $copy,
        public string $largetype,
    ) {}
}
