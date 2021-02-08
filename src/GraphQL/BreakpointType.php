<?php

namespace Spatie\ResponsiveImages\GraphQL;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetInterface;

class BreakpointType extends \Rebing\GraphQL\Support\Type
{
    protected $attributes = [
        'name' => 'Responsive Breakpoint',
    ];

    public function fields(): array
    {
        return [
            'asset' => [
                'type' => Type::nonNull(GraphQL::type(AssetInterface::NAME)),
                'description' => 'The asset.',
            ],
            'label' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The breakpoint label.',
            ],
            'value' => [
                'type' => Type::int(),
                'description' => 'The min-width of this breakpoint.',
            ],
            'unit' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The unit (px by default) of the breakpoint.',
            ],
            'ratio' => [
                'type' => Type::nonNull(Type::float()),
                'description' => 'The image ratio on this breakpoint.',
            ],
            'mediaString' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Picture source media string',
            ],
            'srcSet' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The srcSet string for this breakpoint',
            ],
            'srcSetWebp' => [
                'type' => Type::string(),
                'description' => 'The webp srcSet string for this breakpoint, if webp is enabled',
            ],
            'placeholder' => [
                'type' => Type::string(),
                'description' => 'The placeholder',
            ],
        ];
    }
}
