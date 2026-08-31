<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

final class MessageResponse
{
    public function __construct(public readonly string $message) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $message = $data['message'] ?? null;

        if (!is_string($message)) {
            throw new \UnexpectedValueException('The Kikoeru response field "message" must be a string.');
        }

        return new self($message);
    }
}
