<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Auth;

final class LoginCredentials
{
    public function __construct(
        public readonly string $name,
        public readonly string $password,
    ) {
        if (strlen($name) < 5) {
            throw new \InvalidArgumentException('The Kikoeru username must contain at least 5 characters.');
        }

        if (strlen($password) < 5) {
            throw new \InvalidArgumentException('The Kikoeru password must contain at least 5 characters.');
        }
    }
}
