<?php

namespace Spatie\ResponsiveImages\GraphQL;

use Rebing\GraphQL\Support\Type;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetInterface;

class BreakpointType extends Type
{
    public const string NAME = 'ResponsiveBreakpoint';

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
            'minWidth' => [
                'type' => GraphQL::int(),
                'description' => 'The min-width of this breakpoint.',
            ],
            'widthUnit' => [
                'type' => GraphQL::string(),
                'description' => 'The unit (px by default) of the breakpoint.',
            ],
            'ratio' => [
                'type' => GraphQL::float(),
                'description' => 'The image ratio on this breakpoint.',
            ],
            'sources' => [
                'type' => GraphQL::listOf(GraphQL::type(SourceType::NAME)),
                'description' => 'The sources for this breakpoint.',
            ],
            'placeholder' => [
                'type' => GraphQL::string(),
                'description' => 'The placeholder',
            ],
        ];
    }
}
