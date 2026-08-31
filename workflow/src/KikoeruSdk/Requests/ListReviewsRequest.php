<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\Paginatable;
use Workflow\KikoeruSdk\Dto\ListReviewsResponse;
use Workflow\KikoeruSdk\Enums\ReviewProgress;
use Workflow\KikoeruSdk\Enums\SortDirection;

final class ListReviewsRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?int $page = null,
        private readonly ?string $order = null,
        private readonly ?SortDirection $sort = null,
        private readonly ?int $seed = null,
        private readonly ?ReviewProgress $filter = null,
    ) {
        if (null !== $page && $page < 1) {
            throw new \InvalidArgumentException('The Kikoeru review page must be at least 1.');
        }

        if (null !== $order && '' === trim($order)) {
            throw new \InvalidArgumentException('The Kikoeru review order must not be empty.');
        }
    }

    public function resolveEndpoint(): string
    {
        return '/review';
    }

    public function createDtoFromResponse(Response $response): ListReviewsResponse
    {
        $data = $response->json();

        if (array_is_list($data)) {
            throw new \UnexpectedValueException('The Kikoeru review-list response must be an object.');
        }

        return ListReviewsResponse::fromArray($data);
    }

    /** @return array<string, int|string> */
    protected function defaultQuery(): array
    {
        $query = [];

        if (null !== $this->page) {
            $query['page'] = $this->page;
        }

        if (null !== $this->order) {
            $query['order'] = $this->order;
        }

        if (null !== $this->sort) {
            $query['sort'] = $this->sort->value;
        }

        if (null !== $this->seed) {
            $query['seed'] = $this->seed;
        }

        if (null !== $this->filter) {
            $query['filter'] = $this->filter->value;
        }

        return $query;
    }
}
