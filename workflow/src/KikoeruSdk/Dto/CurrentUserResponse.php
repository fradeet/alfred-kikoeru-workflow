<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

final class CurrentUserResponse
{
    public function __construct(
        public readonly UserIdentity $user,
        public readonly bool $auth,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $user = $data['user'] ?? null;
        $auth = $data['auth'] ?? null;

        if (!is_array($user) || array_is_list($user)) {
            throw new \UnexpectedValueException('The Kikoeru current-user response field "user" must be an object.');
        }

        if (!is_bool($auth)) {
            throw new \UnexpectedValueException('The Kikoeru current-user response field "auth" must be a boolean.');
        }

        return new self(UserIdentity::fromArray($user), $auth);
    }
}
