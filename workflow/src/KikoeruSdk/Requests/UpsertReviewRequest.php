<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Workflow\KikoeruSdk\Dto\MessageResponse;
use Workflow\KikoeruSdk\Enums\ReviewProgress;

final class UpsertReviewRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly int $workId,
        private readonly ?int $rating = null,
        private readonly ?string $reviewText = null,
        private readonly ?ReviewProgress $progress = null,
        private readonly ?bool $starOnly = null,
        private readonly ?bool $progressOnly = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/review';
    }

    public function createDtoFromResponse(Response $response): MessageResponse
    {
        $data = $response->json();

        if (array_is_list($data)) {
            throw new \UnexpectedValueException('The Kikoeru upsert-review response must be an object.');
        }

        return MessageResponse::fromArray($data);
    }

    /** @return array<string, string> */
    protected function defaultQuery(): array
    {
        $query = [];

        if (null !== $this->starOnly) {
            $query['starOnly'] = $this->starOnly ? 'true' : 'false';
        }

        if (null !== $this->progressOnly) {
            $query['progressOnly'] = $this->progressOnly ? 'true' : 'false';
        }

        return $query;
    }

    /** @return array<string, int|string> */
    protected function defaultBody(): array
    {
        $body = ['work_id' => $this->workId];

        if (null !== $this->rating) {
            $body['rating'] = $this->rating;
        }

        if (null !== $this->reviewText) {
            $body['review_text'] = $this->reviewText;
        }

        if (null !== $this->progress) {
            $body['progress'] = $this->progress->value;
        }

        return $body;
    }
}
