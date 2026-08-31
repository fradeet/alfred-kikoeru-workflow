<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Connectors;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;

final class KikoeruConnector extends Connector
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
