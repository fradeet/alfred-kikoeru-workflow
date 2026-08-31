<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Workflow\KikoeruSdk\Dto\HealthCheckResponse;

final class HealthCheckRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/health';
    }

    public function createDtoFromResponse(Response $response): HealthCheckResponse
    {
        return HealthCheckResponse::fromString($response->body());
    }
}
