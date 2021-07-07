<?php


namespace Spatie\ResponsiveImages\GraphQL;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Field;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Assets\Asset;
use Statamic\Facades\GraphQL;
use Statamic\Tags\Parameters;

class ResponsiveField extends Field
{
    protected $attributes = [
        'description' => 'Create a responsive image',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(BreakPointType::NAME));
    }

    public function args(): array
    {
        $args = [
            'ratio' => [
                'type' => Type::float(),
                'description' => 'The ratio of the image',
            ],
            'width' => [
                'description' => 'The maximum width of the image',
                'type' => Type::int(),
            ],
            'webp' => [
                'type' => Type::boolean(),
                'defaultValue' => config('statamic.responsive-images.webp'),
            ],
            'placeholder' => [
                'type' => Type::boolean(),
                'defaultValue' => config('statamic.responsive-images.placeholder'),
            ],
        ];

        $unit = config('statamic.responsive-images.breakpoint_unit');
        foreach (config('statamic.responsive-images.breakpoints') as $key => $width) {
            $args["{$key}_ratio"] = [
                'type' => Type::float(),
                'description' => "The ratio for the {$key} ({$width}{$unit}) breakpoint of the image",
            ];
        }

        return $args;
    }

    protected function resolve(Asset $root, array $args)
    {
        $args = collect($args)->mapWithKeys(function ($value, $key) {
            return [str_replace('_', ':', $key) => $value];
        })->toArray();

        $responsive = new Responsive($root, new Parameters($args));

        return $responsive->breakPoints()->map(function (Breakpoint $breakpoint) use ($args) {
            return $breakpoint->toGql($args);
        })->toArray();
    }
}
