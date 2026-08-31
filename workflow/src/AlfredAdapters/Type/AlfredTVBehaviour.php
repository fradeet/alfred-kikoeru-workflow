<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * Controls how an Alfred Text View updates and handles its input field.
 *
 * @property null|AlfredTVBehaviourResponse   $response   How new response text updates the existing content.
 * @property null|AlfredTVBehaviourScroll     $scroll     Where the view scrolls after an update.
 * @property null|AlfredTVBehaviourInputField $inputfield What happens to the input field after actioning it.
 */
class AlfredTVBehaviour extends AlfredTVBase
{
    public function __construct(
        public ?AlfredTVBehaviourResponse $response = null,
        public ?AlfredTVBehaviourScroll $scroll = null,
        public ?AlfredTVBehaviourInputField $inputfield = null,
    ) {}
}
