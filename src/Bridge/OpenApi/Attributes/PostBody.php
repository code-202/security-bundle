<?php

namespace Code202\Security\Bridge\OpenApi\Attributes;

use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class PostBody extends OAA\RequestBody
{
    public function __construct($item)
    {
        parent::__construct(
            null,
            null,
            null,
            null,
            [
                new OA\MediaType([
                    'mediaType' => 'application/x-www-form-urlencoded',
                    'value' => [
                        new OAA\Schema($item),
                    ]
                ]),
                new OA\MediaType([
                    'mediaType' => 'multipart/form-data',
                    'value' => [
                        new OAA\Schema($item),
                    ]
                ]),
                new OA\MediaType([
                    'mediaType' => 'application/json',
                    'value' => [
                        new OAA\Schema($item),
                    ]
                ]),
            ]
        );
    }
}
