<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

final class ListReviewsResponse
{
    /** @param list<ReviewWork> $works */
    public function __construct(
        public readonly array $works,
        public readonly Pagination $pagination,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $works = $data['works'] ?? null;
        $pagination = $data['pagination'] ?? null;

        if (!is_array($works) || !array_is_list($works)) {
            throw new \UnexpectedValueException('The Kikoeru review-list response field "works" must be a list.');
        }

        if (!is_array($pagination) || array_is_list($pagination)) {
            throw new \UnexpectedValueException('The Kikoeru review-list response field "pagination" must be an object.');
        }

        $workDtos = [];

        foreach ($works as $work) {
            if (!is_array($work) || array_is_list($work)) {
                throw new \UnexpectedValueException('Every Kikoeru review-list work must be an object.');
            }

            $workDtos[] = ReviewWork::fromArray($work);
        }

        return new self($workDtos, Pagination::fromArray($pagination));
    }
}
