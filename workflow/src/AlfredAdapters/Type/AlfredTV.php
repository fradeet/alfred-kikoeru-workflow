<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * Top-level response used to populate an Alfred Text View.
 *
 * The response contains the text shown in the view. Optional session variables
 * and rerun behaviour work in the same way as their Script Filter counterparts.
 *
 * @author fradeet
 *
 * @see https://www.alfredapp.com/help/workflows/user-interface/text/json/ Alfred Text View JSON format
 *
 * @property string                    $response     Text displayed in the Text View.
 * @property null|array<string, mixed> $variables    Session variables available to downstream objects
 *                                                   and subsequent runs.
 * @property null|float                $rerun        Automatic rerun interval from 0.1 to 5.0 seconds.
 * @property null|string               $footer       Text displayed in the window footer.
 * @property null|bool                 $actionoutput Close the Text View and send response to the next object.
 * @property null|AlfredTVBehaviour    $behaviour    How the view updates after each script rerun.
 *
 * @version 1.0.0
 */
class AlfredTV extends AlfredTVBase
{
    /** @param null|array<string, mixed> $variables */
    public function __construct(
        public string $response,
        public ?array $variables = null,
        public ?float $rerun = null,
        public ?string $footer = null,
        public ?bool $actionoutput = null,
        public ?AlfredTVBehaviour $behaviour = null,
    ) {}
}
