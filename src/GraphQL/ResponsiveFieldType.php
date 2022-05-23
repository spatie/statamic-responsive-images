<?php

namespace Spatie\ResponsiveImages\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Value;
use Statamic\Tags\Parameters;

class ResponsiveFieldType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'ResponsiveType';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'breakpoints' => [
                'type' => GraphQL::listOf(GraphQL::type(BreakpointType::NAME)),
                'resolve' => function (array $field, array $args, ?array $context, ResolveInfo $info) {
                    $field = array_map(function ($value) {
                        if ($value instanceof Value) {
                            return $value->value();
                        }

                        return $value;
                    }, $field);

                    $responsive = new Responsive($field['src'], new Parameters($field));

                    return $responsive->breakPoints()->map(function (Breakpoint $breakpoint) {
                        return $breakpoint->toGql([
                            'webp' => config('statamic.responsive-images.webp'),
                            'avif' => config('statamic.responsive-images.avif'),
                            'placeholder' => config('statamic.responsive-images.placeholder'),
                        ]);
                    })->toArray();
                },
            ],
        ];
    }
}
