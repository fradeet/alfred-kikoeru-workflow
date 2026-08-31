<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

final class LoginResponse
{
    public function __construct(public readonly string $token)
    {
        if ('' === trim($token)) {
            throw new \UnexpectedValueException('The Kikoeru login token must not be empty.');
        }
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $token = $data['token'] ?? null;

        if (!is_string($token)) {
            throw new \UnexpectedValueException('The Kikoeru login response must contain a string token.');
        }

        return new self($token);
    }
}
