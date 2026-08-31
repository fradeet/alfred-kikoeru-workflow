<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

final class HealthCheckResponse
{
    private const EXPECTED_STATUS = 'OK';

    private function __construct(public readonly string $status) {}

    public static function fromString(string $body): self
    {
        $status = trim($body);

        if (self::EXPECTED_STATUS !== $status) {
            throw new \UnexpectedValueException('The Kikoeru health check response must be "OK".');
        }

        return new self($status);
    }

    public function isHealthy(): bool
    {
        return self::EXPECTED_STATUS === $this->status;
    }
}
