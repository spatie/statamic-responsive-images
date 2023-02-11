<?php

namespace Spatie\ResponsiveImages\GraphQL;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Field;
use Spatie\ResponsiveImages\AssetNotFoundException;
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
        return GraphQL::listOf(
            GraphQL::nonNull(
                GraphQL::type(BreakPointType::NAME)
            )
        );
    }

    public function args(): array
    {
        return ResponsiveGraphqlArguments::args();
    }

    protected function resolve(Asset|array $root, array $args)
    {
        $args = collect($args)->mapWithKeys(function ($value, $key) {
            return [str_replace('_', ':', $key) => $value];
        })->toArray();

        try {
            $responsive = new Responsive($root, new Parameters($args));
        } catch (AssetNotFoundException $e) {
            logger()->error($e->getMessage());
            return null;
        }

        return $responsive->breakPoints()->map(function (Breakpoint $breakpoint) use ($args) {
            return $breakpoint->toGql($args);
        })->toArray();
    }
}
