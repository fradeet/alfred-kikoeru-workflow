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
use Workflow\KikoeruSdk\Auth\LoginCredentials;
use Workflow\KikoeruSdk\Auth\UserSession;
use Workflow\KikoeruSdk\Auth\UserSessionCache;
use Workflow\KikoeruSdk\Dto\CurrentUserResponse;
use Workflow\KikoeruSdk\Dto\ListReviewsResponse;
use Workflow\KikoeruSdk\Dto\LoginResponse;
use Workflow\KikoeruSdk\Dto\ReviewWork;
use Workflow\KikoeruSdk\Exceptions\LoginFailedException;
use Workflow\KikoeruSdk\Requests\GetCurrentUserRequest;
use Workflow\KikoeruSdk\Requests\LoginRequest;

final class KikoeruConnector extends Connector implements HasPagination
{
    private readonly string $apiBaseUrl;

    public function __construct(
        string $apiBaseUrl,
        private readonly ?string $token = null,
        private readonly ?LoginCredentials $credentials = null,
        private readonly ?UserSessionCache $sessionCache = null,
    ) {
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

        if ((null === $credentials) !== (null === $sessionCache)) {
            throw new \InvalidArgumentException('Kikoeru login credentials and the user-session cache must be configured together.');
        }

        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function resolveBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    public function userSession(): UserSession
    {
        [$credentials, $sessionCache] = $this->authenticationConfiguration();

        return $sessionCache->get($this->apiBaseUrl, $credentials->name)
            ?? $this->login($credentials, $sessionCache);
    }

    /**
     * Send an authenticated request, refreshing an expired login and retrying once.
     *
     * @throws LoginFailedException when login fails or the retried request is still unauthorized
     */
    public function sendAuthenticated(Request $request): Response
    {
        [$credentials, $sessionCache] = $this->authenticationConfiguration();
        $response = $this->sendWithSession($request, $this->userSession());

        if (!$this->isUnauthorized($response)) {
            return $response;
        }

        $sessionCache->forget($this->apiBaseUrl, $credentials->name);
        $response = $this->sendWithSession($request, $this->login($credentials, $sessionCache));

        if ($this->isUnauthorized($response)) {
            $sessionCache->forget($this->apiBaseUrl, $credentials->name);

            throw new LoginFailedException('Kikoeru login failed after refreshing the expired user session.');
        }

        return $response;
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

    /** @return array{LoginCredentials, UserSessionCache} */
    private function authenticationConfiguration(): array
    {
        if (null === $this->credentials || null === $this->sessionCache) {
            throw new \LogicException('Kikoeru login credentials and the user-session cache are required for authenticated requests.');
        }

        return [$this->credentials, $this->sessionCache];
    }

    private function login(LoginCredentials $credentials, UserSessionCache $sessionCache): UserSession
    {
        try {
            $connector = new self($this->apiBaseUrl);
            $loginResponse = $connector->send(new LoginRequest($credentials->name, $credentials->password));

            if ($loginResponse->failed()) {
                throw new LoginFailedException(sprintf('Kikoeru login returned HTTP %d.', $loginResponse->status()));
            }

            $login = $loginResponse->dto();

            if (!$login instanceof LoginResponse) {
                throw new \UnexpectedValueException('The Kikoeru login response did not create a LoginResponse DTO.');
            }

            $currentUserResponse = (new self($this->apiBaseUrl, $login->token))->send(new GetCurrentUserRequest());

            if ($currentUserResponse->failed()) {
                throw new LoginFailedException(sprintf('Loading the Kikoeru user returned HTTP %d.', $currentUserResponse->status()));
            }

            $currentUser = $currentUserResponse->dto();

            if (!$currentUser instanceof CurrentUserResponse) {
                throw new \UnexpectedValueException('The Kikoeru current-user response did not create a CurrentUserResponse DTO.');
            }

            $session = new UserSession($login->token, $currentUser->user, $currentUser->auth);
            $sessionCache->put($this->apiBaseUrl, $credentials->name, $session);

            return $session;
        } catch (LoginFailedException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new LoginFailedException('Unable to log in to Kikoeru.', 0, $exception);
        }
    }

    private function sendWithSession(Request $request, UserSession $session): Response
    {
        return $this->authenticate(new TokenAuthenticator($session->token))->send($request);
    }

    private function isUnauthorized(Response $response): bool
    {
        return 401 === $response->status();
    }
}
