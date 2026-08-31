<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

use Workflow\KikoeruSdk\Enums\ReviewProgress;

final class ReviewWork
{
    /**
     * @param array<mixed>       $circle
     * @param array<mixed>       $rate_count_detail
     * @param null|array<mixed>  $rank
     * @param list<array<mixed>> $vas
     * @param list<array<mixed>> $tags
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly int $circle_id,
        public readonly string $name,
        public readonly array $circle,
        public readonly bool $nsfw,
        public readonly array $rate_count_detail,
        public readonly array $vas,
        public readonly array $tags,
        public readonly string $user_name,
        public readonly ?string $release = null,
        public readonly ?int $dl_count = null,
        public readonly ?int $price = null,
        public readonly ?int $review_count = null,
        public readonly ?int $rate_count = null,
        public readonly float|int|null $rate_average_2dp = null,
        public readonly ?array $rank = null,
        public readonly ?int $userRating = null,
        public readonly ?string $review_text = null,
        public readonly ?ReviewProgress $progress = null,
        public readonly ?string $updated_at = null,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $id = self::requiredInt($data, 'id');
        $title = self::requiredString($data, 'title');
        $circle_id = self::requiredInt($data, 'circle_id');
        $name = self::requiredString($data, 'name');
        $circle = self::requiredObject($data, 'circle');
        $nsfw = $data['nsfw'] ?? null;
        $release = self::nullableString($data, 'release');
        $dl_count = self::nullableInt($data, 'dl_count');
        $price = self::nullableInt($data, 'price');
        $review_count = self::nullableInt($data, 'review_count');
        $rate_count = self::nullableInt($data, 'rate_count');
        $rate_average_2dp = $data['rate_average_2dp'] ?? null;
        $rate_count_detail = self::requiredArray($data, 'rate_count_detail');
        $rank = self::nullableArray($data, 'rank');
        $vas = self::requiredListOfObjects($data, 'vas');
        $tags = self::requiredListOfObjects($data, 'tags');
        $userRating = self::nullableInt($data, 'userRating');
        $review_text = self::nullableString($data, 'review_text');
        $updated_at = self::nullableString($data, 'updated_at');
        $user_name = self::requiredString($data, 'user_name');

        if (!is_bool($nsfw)) {
            throw self::invalidField('nsfw', 'a boolean');
        }

        if (null !== $rate_average_2dp && !is_int($rate_average_2dp) && !is_float($rate_average_2dp)) {
            throw self::invalidField('rate_average_2dp', 'a number or null');
        }

        $progressValue = self::nullableString($data, 'progress');
        $progress = null;

        if (null !== $progressValue) {
            $progress = ReviewProgress::tryFrom($progressValue);

            if (null === $progress) {
                throw new \UnexpectedValueException(sprintf('Unknown Kikoeru review progress "%s".', $progressValue));
            }
        }

        return new self(
            id: $id,
            title: $title,
            circle_id: $circle_id,
            name: $name,
            circle: $circle,
            nsfw: $nsfw,
            rate_count_detail: $rate_count_detail,
            vas: $vas,
            tags: $tags,
            user_name: $user_name,
            release: $release,
            dl_count: $dl_count,
            price: $price,
            review_count: $review_count,
            rate_count: $rate_count,
            rate_average_2dp: $rate_average_2dp,
            rank: $rank,
            userRating: $userRating,
            review_text: $review_text,
            progress: $progress,
            updated_at: $updated_at,
        );
    }

    /** @param array<mixed> $data */
    private static function requiredInt(array $data, string $field): int
    {
        $value = $data[$field] ?? null;

        if (!is_int($value)) {
            throw self::invalidField($field, 'an integer');
        }

        return $value;
    }

    /** @param array<mixed> $data */
    private static function requiredString(array $data, string $field): string
    {
        $value = $data[$field] ?? null;

        if (!is_string($value)) {
            throw self::invalidField($field, 'a string');
        }

        return $value;
    }

    /** @param array<mixed> $data */
    private static function nullableInt(array $data, string $field): ?int
    {
        $value = $data[$field] ?? null;

        if (null !== $value && !is_int($value)) {
            throw self::invalidField($field, 'an integer or null');
        }

        return $value;
    }

    /** @param array<mixed> $data */
    private static function nullableString(array $data, string $field): ?string
    {
        $value = $data[$field] ?? null;

        if (null !== $value && !is_string($value)) {
            throw self::invalidField($field, 'a string or null');
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private static function requiredArray(array $data, string $field): array
    {
        $value = $data[$field] ?? null;

        if (!is_array($value)) {
            throw self::invalidField($field, 'an array');
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private static function requiredObject(array $data, string $field): array
    {
        $value = self::requiredArray($data, $field);

        if (array_is_list($value)) {
            throw self::invalidField($field, 'an object');
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return null|array<mixed>
     */
    private static function nullableArray(array $data, string $field): ?array
    {
        $value = $data[$field] ?? null;

        if (null !== $value && !is_array($value)) {
            throw self::invalidField($field, 'an array or null');
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return list<array<mixed>>
     */
    private static function requiredListOfObjects(array $data, string $field): array
    {
        $value = self::requiredArray($data, $field);

        if (!array_is_list($value)) {
            throw self::invalidField($field, 'a list of objects');
        }

        foreach ($value as $item) {
            if (!is_array($item) || array_is_list($item)) {
                throw self::invalidField($field, 'a list of objects');
            }
        }

        return $value;
    }

    private static function invalidField(string $field, string $expected): \UnexpectedValueException
    {
        return new \UnexpectedValueException(sprintf('The Kikoeru review work field "%s" must be %s.', $field, $expected));
    }
}
