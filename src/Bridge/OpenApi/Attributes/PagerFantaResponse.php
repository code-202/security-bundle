<?php

namespace Code202\Security\Bridge\OpenApi\Attributes;

use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class PagerFantaResponse extends OAA\Response
{
    public function __construct($item)
    {
        parent::__construct(
            null,
            200,
            'Pager',
            [],
            [
                new OA\MediaType([
                    'mediaType' => 'application/json',
                    'value' => [
                        new OA\Schema([
                            'allOf' => [
                                new OAA\Schema('#components/schemas/PagerFantaResponse'),
                                new OA\Schema([
                                    'properties' => [
                                        new OA\Property([
                                            'property' => 'results',
                                            'type' => 'array',
                                            'items' => new OA\Items(['ref' => $item]),
                                        ])
                                    ],
                                ])
                            ]
                        ])
                    ]
                ])
            ]
        );
    }
}
