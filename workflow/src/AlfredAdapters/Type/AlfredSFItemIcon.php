<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * Icon displayed alongside a Script Filter result.
 *
 * @property string      $path Image path, file path, or UTI depending on type.
 * @property null|string $type Use fileicon for a path's icon or filetype for a UTI; omit for an image path.
 */
class AlfredSFItemIcon extends AlfredSFBase
{
    public function __construct(
        public string $path,
        public ?string $type = null,
    ) {}
}
