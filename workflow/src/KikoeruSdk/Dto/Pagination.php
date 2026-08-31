<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

final class Pagination
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $pageSize,
        public readonly int $totalCount,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $currentPage = $data['currentPage'] ?? null;
        $pageSize = $data['pageSize'] ?? null;
        $totalCount = $data['totalCount'] ?? null;

        if (!is_int($currentPage) || !is_int($pageSize) || !is_int($totalCount)) {
            throw new \UnexpectedValueException('The Kikoeru pagination response must contain integer currentPage, pageSize, and totalCount fields.');
        }

        return new self($currentPage, $pageSize, $totalCount);
    }
}
