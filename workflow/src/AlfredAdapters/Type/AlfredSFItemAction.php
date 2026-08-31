<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * Typed Universal Action content for a result.
 *
 * Use auto to let Alfred infer the content type, or text, url, and file to
 * explicitly identify it.
 *
 * @property null|list<string>|string $text Text content.
 * @property null|string              $url  URL content.
 * @property null|string              $file File-path content.
 * @property null|string              $auto Content whose type Alfred should infer.
 */
class AlfredSFItemAction extends AlfredSFBase
{
    /** @param null|list<string>|string $text */
    public function __construct(
        public array|string|null $text = null,
        public ?string $url = null,
        public ?string $file = null,
        public ?string $auto = null,
    ) {}
}
