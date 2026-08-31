<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Dto;

use Workflow\KikoeruSdk\Enums\ReviewProgress;

final class ReviewWork
{
    /**
     * @param array<mixed>       $circle
     * @param array<mixed>       $rateCountDetail
     * @param null|array<mixed>  $rank
     * @param list<array<mixed>> $voiceActors
     * @param list<array<mixed>> $tags
     * @param array<mixed>       $additionalProperties
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly int $circleId,
        public readonly string $circleName,
        public readonly array $circle,
        public readonly bool $nsfw,
        public readonly ?string $release,
        public readonly ?int $downloadCount,
        public readonly ?int $price,
        public readonly ?int $reviewCount,
        public readonly ?int $rateCount,
        public readonly float|int|null $rateAverage,
        public readonly array $rateCountDetail,
        public readonly ?array $rank,
        public readonly array $voiceActors,
        public readonly array $tags,
        public readonly ?int $userRating,
        public readonly ?string $reviewText,
        public readonly ?ReviewProgress $progress,
        public readonly ?string $updatedAt,
        public readonly string $userName,
        public readonly array $additionalProperties,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $id = self::requiredInt($data, 'id');
        $title = self::requiredString($data, 'title');
        $circleId = self::requiredInt($data, 'circle_id');
        $circleName = self::requiredString($data, 'name');
        $circle = self::requiredObject($data, 'circle');
        $nsfw = $data['nsfw'] ?? null;
        $release = self::nullableString($data, 'release');
        $downloadCount = self::nullableInt($data, 'dl_count');
        $price = self::nullableInt($data, 'price');
        $reviewCount = self::nullableInt($data, 'review_count');
        $rateCount = self::nullableInt($data, 'rate_count');
        $rateAverage = $data['rate_average_2dp'] ?? null;
        $rateCountDetail = self::requiredArray($data, 'rate_count_detail');
        $rank = self::nullableArray($data, 'rank');
        $voiceActors = self::requiredListOfObjects($data, 'vas');
        $tags = self::requiredListOfObjects($data, 'tags');
        $userRating = self::nullableInt($data, 'userRating');
        $reviewText = self::nullableString($data, 'review_text');
        $updatedAt = self::nullableString($data, 'updated_at');
        $userName = self::requiredString($data, 'user_name');

        if (!is_bool($nsfw)) {
            throw self::invalidField('nsfw', 'a boolean');
        }

        if (null !== $rateAverage && !is_int($rateAverage) && !is_float($rateAverage)) {
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

        $knownFields = [
            'id', 'title', 'circle_id', 'name', 'circle', 'nsfw', 'release', 'dl_count',
            'price', 'review_count', 'rate_count', 'rate_average_2dp', 'rate_count_detail',
            'rank', 'vas', 'tags', 'userRating', 'review_text', 'progress', 'updated_at', 'user_name',
        ];

        return new self(
            $id,
            $title,
            $circleId,
            $circleName,
            $circle,
            $nsfw,
            $release,
            $downloadCount,
            $price,
            $reviewCount,
            $rateCount,
            $rateAverage,
            $rateCountDetail,
            $rank,
            $voiceActors,
            $tags,
            $userRating,
            $reviewText,
            $progress,
            $updatedAt,
            $userName,
            array_diff_key($data, array_flip($knownFields)),
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
