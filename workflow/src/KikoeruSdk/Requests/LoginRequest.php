<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Workflow\KikoeruSdk\Dto\LoginResponse;

final class LoginRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $name,
        private readonly string $password,
    ) {
        if (strlen($name) < 5) {
            throw new \InvalidArgumentException('The Kikoeru username must contain at least 5 characters.');
        }

        if (strlen($password) < 5) {
            throw new \InvalidArgumentException('The Kikoeru password must contain at least 5 characters.');
        }
    }

    public function resolveEndpoint(): string
    {
        return '/auth/me';
    }

    public function createDtoFromResponse(Response $response): LoginResponse
    {
        $data = $response->json();

        if (array_is_list($data)) {
            throw new \UnexpectedValueException('The Kikoeru login response must be an object.');
        }

        return LoginResponse::fromArray($data);
    }

    /** @return array{name: string, password: string} */
    protected function defaultBody(): array
    {
        return [
            'name' => $this->name,
            'password' => $this->password,
        ];
    }
}
