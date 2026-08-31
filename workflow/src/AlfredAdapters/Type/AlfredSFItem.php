<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * A single result row in an Alfred Script Filter response.
 *
 * Only the title is required by Alfred. Supplying a stable UID lets Alfred
 * learn the result's ranking; omit it when the returned order must be kept.
 *
 * @property string                                                 $title        Primary text displayed in the result row; required and non-empty.
 * @property null|AlfredSFItemAction|array<array-key, mixed>|string $action       Universal Action content;
 *                                                                                overrides arg for that action.
 * @property null|list<string>|string                               $arg          Value passed to the connected workflow action when selected.
 * @property null|string                                            $autocomplete Text inserted into Alfred's search field when autocompleted.
 * @property null|AlfredSFItemIcon                                  $icon         Result icon; relative paths resolve from the workflow root.
 * @property null|string                                            $match        Text used instead of title when Alfred Filters Results is enabled.
 * @property null|array<string, array<string, mixed>>               $mods         Modifier-key overrides for cmd, alt, ctrl,
 *                                                                                shift, fn, or combinations.
 * @property null|string                                            $quicklookurl Quick Look URL or path; Alfred falls back to arg when omitted.
 * @property null|string                                            $subtitle     Secondary text displayed below the title.
 * @property null|AlfredSFItemType                                  $type         Controls whether Alfred treats the result as an item or file.
 * @property null|AlfredSFItemText                                  $text         Text used for Copy (Command-C) and Large Type (Command-L).
 * @property null|string                                            $uid          Stable identifier used by Alfred to learn result ordering.
 * @property null|array<string, mixed>                              $variables    Variables emitted when selected; item values override
 *                                                                                session values.
 * @property null|bool                                              $valid        Whether Return can action the item; defaults to true in Alfred.
 */
class AlfredSFItem extends AlfredSFBase
{
    /**
     * @param null|AlfredSFItemAction|array<array-key, mixed>|string $action
     * @param null|list<string>|string                               $arg
     * @param null|array<string, array<string, mixed>>               $mods
     * @param null|array<string, mixed>                              $variables
     */
    public function __construct(
        public string $title,
        public AlfredSFItemAction|array|string|null $action = null,
        public array|string|null $arg = null,
        public ?string $autocomplete = null,
        public ?AlfredSFItemIcon $icon = null,
        public ?string $match = null,
        public ?array $mods = null,
        public ?string $quicklookurl = null,
        public ?string $subtitle = null,
        public ?AlfredSFItemType $type = null,
        public ?AlfredSFItemText $text = null,
        public ?string $uid = null,
        public ?array $variables = null,
        public ?bool $valid = null,
    ) {}
}
