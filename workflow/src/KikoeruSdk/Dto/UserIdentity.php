<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

use Workflow\KikoeruSdk\Enums\UserGroup;

final class UserIdentity
{
    public function __construct(
        public readonly string $name,
        public readonly UserGroup $group,
    ) {
        if ('' === trim($name)) {
            throw new \UnexpectedValueException('The Kikoeru user name must not be empty.');
        }
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $name = $data['name'] ?? null;
        $group = $data['group'] ?? null;

        if (!is_string($name)) {
            throw new \UnexpectedValueException('The Kikoeru user name must be a string.');
        }

        if (!is_string($group)) {
            throw new \UnexpectedValueException('The Kikoeru user group must be a string.');
        }

        $userGroup = UserGroup::tryFrom($group);

        if (null === $userGroup) {
            throw new \UnexpectedValueException(sprintf('Unknown Kikoeru user group "%s".', $group));
        }

        return new self($name, $userGroup);
    }
}
