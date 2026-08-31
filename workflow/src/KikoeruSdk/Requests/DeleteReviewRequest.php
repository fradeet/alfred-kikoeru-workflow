<?php

declare(strict_types=1);

namespace Workflow\KikoeruSdk\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Workflow\KikoeruSdk\Dto\MessageResponse;

final class DeleteReviewRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(private readonly int $workId) {}

    public function resolveEndpoint(): string
    {
        return '/review';
    }

    public function createDtoFromResponse(Response $response): MessageResponse
    {
        $data = $response->json();

        if (array_is_list($data)) {
            throw new \UnexpectedValueException('The Kikoeru delete-review response must be an object.');
        }

        return MessageResponse::fromArray($data);
    }

    /** @return array{work_id: int} */
    protected function defaultQuery(): array
    {
        return ['work_id' => $this->workId];
    }
}
