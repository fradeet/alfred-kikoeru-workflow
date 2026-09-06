<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Auth;

use Workflow\KikoeruSdk\Dto\UserIdentity;

final class UserSession
{
    public function __construct(
        public readonly string $token,
        public readonly UserIdentity $user,
        public readonly bool $auth,
    ) {
        if ('' === trim($token)) {
            throw new \UnexpectedValueException('The cached Kikoeru token must not be empty.');
        }
    }

    /** @return array{token: string, user: array{name: string, group: string}, auth: bool} */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'user' => [
                'name' => $this->user->name,
                'group' => $this->user->group->value,
            ],
            'auth' => $this->auth,
        ];
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $token = $data['token'] ?? null;
        $user = $data['user'] ?? null;
        $auth = $data['auth'] ?? null;

        if (!is_string($token)) {
            throw new \UnexpectedValueException('The cached Kikoeru token must be a string.');
        }

        if (!is_array($user) || array_is_list($user)) {
            throw new \UnexpectedValueException('The cached Kikoeru user must be an object.');
        }

        if (!is_bool($auth)) {
            throw new \UnexpectedValueException('The cached Kikoeru auth state must be a boolean.');
        }

        return new self($token, UserIdentity::fromArray($user), $auth);
    }
}
