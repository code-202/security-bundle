<?php

namespace Code202\Security\Bridge\OpenApi\Attributes;

use Attribute;
use OpenApi\Attributes as OAA;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class PostBody extends OAA\RequestBody
{
    public function __construct(object|string|null $item)
    {
        parent::__construct(
            null,
            null,
            null,
            null,
            [
                new OAA\MediaType(
                    'application/x-www-form-urlencoded',
                    new OAA\Schema($item),
                ),
                new OAA\MediaType(
                    'multipart/form-data',
                    new OAA\Schema($item),
                ),
                new OAA\MediaType(
                    'application/json',
                    new OAA\Schema($item),
                ),
            ]
        );
    }
}
