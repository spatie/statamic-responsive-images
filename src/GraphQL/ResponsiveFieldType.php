<?php

namespace Spatie\ResponsiveImages\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Type;
use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Value;
use Statamic\Tags\Parameters;

class ResponsiveFieldType extends Type
{
    const NAME = 'ResponsiveType';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'breakpoints' => [
                'type' => GraphQL::listOf(
                    GraphQL::nonNull(
                        GraphQL::type(BreakpointType::NAME)
                    )
                ),
                'resolve' => function (array $field, array $args, ?array $context, ResolveInfo $info) {
                    $field = array_map(function ($value) {
                        if ($value instanceof Value) {
                            return $value->value();
                        }

                        return $value;
                    }, $field);

                    try {
                        $responsive = new Responsive($field['src'], new Parameters($field));

                        return $responsive->breakPoints()->map(function (Breakpoint $breakpoint) {
                            return $breakpoint->toGql([
                                'placeholder' => config('statamic.responsive-images.placeholder'),
                            ]);
                        })->toArray();
                    } catch (AssetNotFoundException $e) {
                        logger()->error($e->getMessage());

                        return null;
                    }
                },
            ],
            'responsive' => ResponsiveField::class,
        ];
    }
}
