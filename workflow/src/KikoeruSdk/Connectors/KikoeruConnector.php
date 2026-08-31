<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Connectors;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\PaginationPlugin\PagedPaginator;
use Workflow\KikoeruSdk\Dto\ListReviewsResponse;
use Workflow\KikoeruSdk\Dto\ReviewWork;

final class KikoeruConnector extends Connector implements HasPagination
{
    private readonly string $apiBaseUrl;

    public function __construct(string $apiBaseUrl, private readonly ?string $token = null)
    {
        $apiBaseUrl = rtrim(trim($apiBaseUrl), '/');

        if (false === filter_var($apiBaseUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('The Kikoeru API base URL must be a valid URL.');
        }

        $scheme = parse_url($apiBaseUrl, PHP_URL_SCHEME);

        if ('http' !== $scheme && 'https' !== $scheme) {
            throw new \InvalidArgumentException('The Kikoeru API base URL must use HTTP or HTTPS.');
        }

        if (null !== $token && '' === trim($token)) {
            throw new \InvalidArgumentException('The Kikoeru API token must not be empty.');
        }

        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function resolveBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    public function paginate(Request $request): PagedPaginator
    {
        return new class(connector: $this, request: $request) extends PagedPaginator {
            protected function isLastPage(Response $response): bool
            {
                $page = $this->page($response);

                return [] === $page->works
                    || $page->pagination->currentPage * $page->pagination->pageSize >= $page->pagination->totalCount;
            }

            /** @return list<ReviewWork> */
            protected function getPageItems(Response $response, Request $request): array
            {
                return $this->page($response)->works;
            }

            protected function getTotalPages(Response $response): int
            {
                $pagination = $this->page($response)->pagination;

                if (0 === $pagination->pageSize) {
                    return 1;
                }

                return max(1, (int) ceil($pagination->totalCount / $pagination->pageSize));
            }

            private function page(Response $response): ListReviewsResponse
            {
                $data = $response->json();

                if (array_is_list($data)) {
                    throw new \UnexpectedValueException('The Kikoeru review-list response must be an object.');
                }

                return ListReviewsResponse::fromArray($data);
            }
        };
    }

    /** @return array<string, string> */
    #[\Override]
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json, text/plain',
            'Content-Type' => 'application/json',
            'User-Agent' => 'alfred-kikoeru-workflow/1.0',
        ];
    }

    #[\Override]
    protected function defaultAuth(): ?Authenticator
    {
        return null === $this->token ? null : new TokenAuthenticator($this->token);
    }
}
