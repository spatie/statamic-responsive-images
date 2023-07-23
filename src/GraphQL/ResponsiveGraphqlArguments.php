<?php

namespace Spatie\ResponsiveImages\GraphQL;

use GraphQL\Type\Definition\Type;

trait ResponsiveGraphqlArguments
{
    public static function args(): array
    {
        $defaultBreakpointArgs = [
            // Base, important args
            'ratio' => [
                'type' => Type::float(),
                'description' => 'The ratio of the image',
            ],
            'width' => [
                'type' => Type::int(),
                'description' => 'The maximum width of the image',
            ],
            'webp' => [
                'type' => Type::boolean(),
                'description' => 'Whether to generate WEBP images',
            ],
            'avif' => [
                'type' => Type::boolean(),
                'description' => 'Whether to generate AVIF images',
            ],
            'placeholder' => [
                'type' => Type::boolean(),
                'description' => 'Whether to generate and output placeholder string in the srcsets',
            ],
            'quality' => [
                'type' => Type::int(),
                'description' => 'The quality of the images',
            ],
        ];

        // https://statamic.dev/tags/glide#parameters
        // Not all have been included as some may cause unexpected images
        $glideArgs = [
            'glide_fit' => [
                'type' => Type::string(),
            ],
            'glide_crop' => [
                'type' => Type::string(),
            ],
            'glide_orient' => [
                'type' => Type::string(),
            ],
            'glide_flip' => [
                'type' => Type::string(),
            ],
            'glide_bg' => [
                'type' => Type::string(),
            ],
            'glide_blur' => [
                'type' => Type::int(),
            ],
            'glide_brightness' => [
                'type' => Type::string(),
            ],
            'glide_contrast' => [
                'type' => Type::string(),
            ],
            'glide_gamma' => [
                'type' => Type::float(),
            ],
            'glide_sharpen' => [
                'type' => Type::int(),
            ],
            'glide_pixelate' => [
                'type' => Type::int(),
            ],
            'glide_filter' => [
                'type' => Type::string(),
            ],
            'glide_mark' => [
                'type' => Type::string(),
            ],
            'glide_markw' => [
                'type' => Type::string(),
            ],
            'glide_markh' => [
                'type' => Type::string(),
            ],
            'glide_markfit' => [
                'type' => Type::string(),
            ],
            'glide_markx' => [
                'type' => Type::string(),
            ],
            'glide_marky' => [
                'type' => Type::string(),
            ],
            'glide_markpad' => [
                'type' => Type::string(),
            ],
            'glide_markpos' => [
                'type' => Type::string(),
            ],
            'glide_width' => [
                'type' => Type::int(),
            ],
        ];

        $defaultBreakpointArgs = array_merge($defaultBreakpointArgs, $glideArgs);

        $additionalBreakpointArgs = [];

        $unit = config('statamic.responsive-images.breakpoint_unit');

        foreach (config('statamic.responsive-images.breakpoints') as $breakpoint => $width) {
            foreach ($defaultBreakpointArgs as $argKey => $argConfig) {
                if (isset($argConfig['description'])) {
                    $argConfig['description'] = $argConfig['description'] . " for the {$breakpoint} ({$width}{$unit}) breakpoint";
                }

                $additionalBreakpointArgs["{$breakpoint}_{$argKey}"] = $argConfig;
            }
        }

        return array_merge($defaultBreakpointArgs, $additionalBreakpointArgs);
    }
}
