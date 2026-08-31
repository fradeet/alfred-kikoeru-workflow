<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * Top-level response returned by an Alfred Script Filter.
 *
 * A response must contain an items array, which may be empty. Session
 * variables are passed back to later runs of the same Script Filter session.
 *
 * @author fradeet
 *
 * @see https://www.alfredapp.com/help/workflows/inputs/script-filter/json/ Alfred Script Filter JSON format
 *
 * @property list<AlfredSFItem>        $items         Result rows displayed by Alfred.
 * @property null|array<string, mixed> $variables     Session variables available to downstream objects
 *                                                    and subsequent runs.
 * @property null|string               $rerun         Automatic rerun interval. Alfred expects a JSON number from 0.1 to 5.0 seconds.
 * @property null|AlfredSFCache        $cache         Result-cache configuration, available in Alfred 5.5 and later.
 * @property null|bool                 $skipknowledge Preserve the supplied order instead of applying Alfred's learned result ordering.
 *
 * @version 1.0.1
 */
class AlfredSF extends AlfredSFBase
{
    /**
     * @param list<AlfredSFItem>        $items
     * @param null|array<string, mixed> $variables
     */
    public function __construct(
        public array $items,
        public ?array $variables = null,
        public ?string $rerun = null,
        public ?AlfredSFCache $cache = null,
        public ?bool $skipknowledge = null,
    ) {}
}
