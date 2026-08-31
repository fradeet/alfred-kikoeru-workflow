<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * Configuration for Alfred's automatic Script Filter result cache.
 *
 * @property int       $seconds     Cache lifetime from 5 to 86400 seconds (24 hours).
 * @property null|bool $loosereload Show cached data first and refresh stale results in the background.
 */
class AlfredSFCache extends AlfredSFBase
{
    public function __construct(
        public int $seconds,
        public ?bool $loosereload = null,
    ) {}
}
