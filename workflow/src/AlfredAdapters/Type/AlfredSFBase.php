<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Type;

/**
 * Base value object for Alfred Script Filter JSON structures.
 *
 * Null properties are omitted from the JSON output; false, zero, and empty
 * values are retained because they can have meaning in Alfred's JSON format.
 */
class AlfredSFBase implements \JsonSerializable
{
    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        $properties = [];

        foreach (get_object_vars($this) as $name => $value) {
            if (is_string($name) && null !== $value) {
                $properties[$name] = $value;
            }
        }

        return $properties;
    }
}
