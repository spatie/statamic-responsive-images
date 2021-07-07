<?php

namespace Spatie\ResponsiveImages\GraphQL;

use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetInterface;

class BreakpointType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'ResponsiveBreakpoint';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'asset' => [
                'type' => GraphQL::type(AssetInterface::NAME),
                'description' => 'The asset.',
            ],
            'label' => [
                'type' => GraphQL::string(),
                'description' => 'The breakpoint label.',
            ],
            'value' => [
                'type' => GraphQL::int(),
                'description' => 'The min-width of this breakpoint.',
            ],
            'unit' => [
                'type' => GraphQL::string(),
                'description' => 'The unit (px by default) of the breakpoint.',
            ],
            'ratio' => [
                'type' => GraphQL::float(),
                'description' => 'The image ratio on this breakpoint.',
            ],
            'mediaString' => [
                'type' => GraphQL::string(),
                'description' => 'Picture source media string',
            ],
            'srcSet' => [
                'type' => GraphQL::string(),
                'description' => 'The srcSet string for this breakpoint',
            ],
            'srcSetWebp' => [
                'type' => GraphQL::string(),
                'description' => 'The webp srcSet string for this breakpoint, if webp is enabled',
            ],
            'placeholder' => [
                'type' => GraphQL::string(),
                'description' => 'The placeholder',
            ],
        ];
    }
}
