<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk;

use Saloon\Http\Request;
use Saloon\Http\Response;
use Workflow\KikoeruSdk\Auth\LoginCredentials;
use Workflow\KikoeruSdk\Auth\UserSession;
use Workflow\KikoeruSdk\Auth\UserSessionCache;
use Workflow\KikoeruSdk\Connectors\KikoeruConnector;
use Workflow\KikoeruSdk\Dto\CurrentUserResponse;
use Workflow\KikoeruSdk\Dto\LoginResponse;
use Workflow\KikoeruSdk\Exceptions\LoginFailedException;
use Workflow\KikoeruSdk\Requests\GetCurrentUserRequest;
use Workflow\KikoeruSdk\Requests\LoginRequest;

final class KikoeruClient
{
    private readonly string $apiBaseUrl;

    public function __construct(
        string $apiBaseUrl,
        private readonly LoginCredentials $credentials,
        private readonly UserSessionCache $sessionCache,
    ) {
        $this->apiBaseUrl = (new KikoeruConnector($apiBaseUrl))->resolveBaseUrl();
    }

    public function userSession(): UserSession
    {
        return $this->sessionCache->get($this->apiBaseUrl, $this->credentials->name)
            ?? $this->login();
    }

    /**
     * Send an authenticated request, refreshing an expired login and retrying once.
     *
     * @throws LoginFailedException when login fails or the retried request is still unauthorized
     */
    public function sendAuthenticated(Request $request): Response
    {
        $session = $this->userSession();
        $response = $this->sendWithSession($request, $session);

        if (!$this->isUnauthorized($response)) {
            return $response;
        }

        $this->forgetSession();
        $session = $this->login();
        $response = $this->sendWithSession($request, $session);

        if ($this->isUnauthorized($response)) {
            $this->forgetSession();

            throw new LoginFailedException('Kikoeru login failed after refreshing the expired user session.');
        }

        return $response;
    }

    private function login(): UserSession
    {
        try {
            $connector = new KikoeruConnector($this->apiBaseUrl);
            $loginResponse = $connector->send(
                new LoginRequest($this->credentials->name, $this->credentials->password),
            );

            if ($loginResponse->failed()) {
                throw new LoginFailedException(sprintf('Kikoeru login returned HTTP %d.', $loginResponse->status()));
            }

            $login = $loginResponse->dto();

            if (!$login instanceof LoginResponse) {
                throw new \UnexpectedValueException('The Kikoeru login response did not create a LoginResponse DTO.');
            }

            $authenticatedConnector = new KikoeruConnector($this->apiBaseUrl, $login->token);
            $currentUserResponse = $authenticatedConnector->send(new GetCurrentUserRequest());

            if ($currentUserResponse->failed()) {
                throw new LoginFailedException(sprintf('Loading the Kikoeru user returned HTTP %d.', $currentUserResponse->status()));
            }

            $currentUser = $currentUserResponse->dto();

            if (!$currentUser instanceof CurrentUserResponse) {
                throw new \UnexpectedValueException('The Kikoeru current-user response did not create a CurrentUserResponse DTO.');
            }

            $session = new UserSession($login->token, $currentUser->user, $currentUser->auth);
            $this->sessionCache->put($this->apiBaseUrl, $this->credentials->name, $session);

            return $session;
        } catch (LoginFailedException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new LoginFailedException('Unable to log in to Kikoeru.', 0, $exception);
        }
    }

    private function sendWithSession(Request $request, UserSession $session): Response
    {
        return (new KikoeruConnector($this->apiBaseUrl, $session->token))->send($request);
    }

    private function isUnauthorized(Response $response): bool
    {
        return 401 === $response->status();
    }

    private function forgetSession(): void
    {
        $this->sessionCache->forget($this->apiBaseUrl, $this->credentials->name);
    }
}
