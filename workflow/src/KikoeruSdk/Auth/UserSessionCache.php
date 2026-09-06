<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Auth;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class UserSessionCache
{
    private const CACHE_NAMESPACE = 'kikoeru_auth';

    private readonly CacheInterface $cache;

    public function __construct(string $userDataDirectory)
    {
        $userDataDirectory = rtrim(trim($userDataDirectory), DIRECTORY_SEPARATOR);

        if ('' === $userDataDirectory) {
            throw new \InvalidArgumentException('The user data directory must not be empty.');
        }

        $this->cache = new Psr16Cache(new FilesystemAdapter(
            namespace: self::CACHE_NAMESPACE,
            defaultLifetime: 0,
            directory: $userDataDirectory.DIRECTORY_SEPARATOR.'cache',
        ));
    }

    public function get(string $apiBaseUrl, string $username): ?UserSession
    {
        $value = $this->cache->get($this->key($apiBaseUrl, $username));

        if (!is_array($value)) {
            return null;
        }

        try {
            return UserSession::fromArray($value);
        } catch (\UnexpectedValueException) {
            $this->forget($apiBaseUrl, $username);

            return null;
        }
    }

    public function put(string $apiBaseUrl, string $username, UserSession $session): void
    {
        $this->cache->set($this->key($apiBaseUrl, $username), $session->toArray());
    }

    public function forget(string $apiBaseUrl, string $username): void
    {
        $this->cache->delete($this->key($apiBaseUrl, $username));
    }

    private function key(string $apiBaseUrl, string $username): string
    {
        return hash('sha256', rtrim(trim($apiBaseUrl), '/').'\0'.$username);
    }
}
