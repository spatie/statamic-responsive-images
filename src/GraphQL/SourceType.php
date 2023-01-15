<?php

namespace Spatie\ResponsiveImages\GraphQL;

use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetInterface;

class SourceType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'ResponsiveBreakpointSource';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'format' => [
                'type' => GraphQL::string(),
                'description' => 'The format for this sources srcset (e.g. original, webp, avif)',
            ],
            'mimeType' => [
                'type' => GraphQL::string(),
                'description' => 'The mime type for sources srcset',
            ],
            'minWidth' => [
                'type' => GraphQL::int(),
                'description' => 'The minimum starting width for this source',
            ],
            'mediaWidthUnit' => [
                'type' => GraphQL::string(),
                'description' => 'The unit for the min-width in media query',
            ],
            'mediaString' => [
                'type' => GraphQL::string(),
                'description' => 'The media string for this source',
            ],
            'srcSet' => [
                'type' => GraphQL::string(),
                'description' => 'The srcSet string for this source',
            ],
            'placeholder' => [
                'type' => GraphQL::string(),
                'description' => 'The placeholder',
            ],
        ];
    }
}
