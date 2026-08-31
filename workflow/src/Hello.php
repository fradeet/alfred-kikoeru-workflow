<?php

declare(strict_types=1);

namespace Workflow;

final class Hello
{
    public function __invoke(string ...$names): string
    {
        if ([] === $names) {
            return 'Hello Alfred';
        }

        return 'Hello '.implode(' ', $names);
    }
}
