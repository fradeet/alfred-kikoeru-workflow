<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Workflow\KikoeruSdk\Dto\CurrentUserResponse;

final class GetCurrentUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/auth/me';
    }

    public function createDtoFromResponse(Response $response): CurrentUserResponse
    {
        $data = $response->json();

        if (array_is_list($data)) {
            throw new \UnexpectedValueException('The Kikoeru current-user response must be an object.');
        }

        return CurrentUserResponse::fromArray($data);
    }
}
