<?php

namespace Code202\Security\Bridge\OpenApi\Attributes;

use Attribute;
use OpenApi\Attributes as OAA;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class PagerFantaResponse extends OAA\Response
{
    public function __construct(object|string|null $item)
    {
        parent::__construct(
            null,
            200,
            'Pager',
            [],
            [
                new OAA\MediaType(
                    'application/json',
                    new OAA\Schema(
                        allOf: [
                            new OAA\Schema('#components/schemas/PagerFantaResponse'),
                            new OAA\Schema(
                                properties: [
                                    new OAA\Property(
                                        property: 'results',
                                        type: 'array',
                                        items: new OAA\Items($item),
                                    ),
                                ],
                            ),
                        ],
                    )
                ),
            ]
        );
    }
}
