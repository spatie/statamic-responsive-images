<?php

namespace Spatie\ResponsiveImages\Tags;

use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\DimensionCalculator;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

class ResponsiveTag extends Tags
{
    protected static $handle = 'responsive';

    public static function render(...$arguments): string
    {
        $asset = $arguments[0];
        $parameters = $arguments[1] ?? [];

        /** @var \Spatie\ResponsiveImages\Tags\ResponsiveTag $responsive */
        $responsive = app(ResponsiveTag::class);
        $responsive->setContext(['url' => $asset]);
        $responsive->setParameters($parameters);

        return $responsive->wildcard('url');
    }

    public function wildcard($tag)
    {
        $this->params->put('src', $this->context->get($tag));

        return $this->index();
    }

    public function index()
    {
        try {
            $responsive = new Responsive($this->params->get('src'), $this->params);
        } catch (AssetNotFoundException $e) {
            return '';
        }

        if (in_array($responsive->asset->extension(), ['svg', 'gif'])) {
            return view('responsive-images::responsiveImage', [
                'attributeString' => $this->getAttributeString(),
                'src' => $responsive->asset->url(),
                'width' => $responsive->asset->width(),
                'height' => $responsive->asset->height(),
                'asset' => $responsive->asset->toAugmentedArray(),
                'hasSources' => false,
            ])->render();
        }

        $dimensions = app(DimensionCalculator::class)
            ->calculateForImgTag($responsive->defaultBreakpoint());

        $width = $dimensions->getWidth();
        $height = $dimensions->getHeight();

        $src = app(GenerateImageJob::class, ['asset' => $responsive->asset, 'params' => [
            'width' => $width,
            'height' => $height,
        ]])->handle();

        $includePlaceholder = $this->includePlaceholder();

        $breakpoints = $responsive->breakPoints();

        return view('responsive-images::responsiveImage', [
            'attributeString' => $this->getAttributeString(),
            'includePlaceholder' => $includePlaceholder,
            'src' => $src,
            'breakpoints' => $breakpoints,
            'width' => round($width),
            'height' => round($height),
            'asset' => $responsive->asset->toAugmentedArray(),
            'hasSources' => $responsive->breakPoints()->map(function ($breakpoint) {
                return $breakpoint->getSources();
            })->flatten()->count() > 0,
        ])->render();
    }

    private function getAttributeString(): string
    {
        $breakpointPrefixes = collect(array_keys(config('statamic.responsive-images.breakpoints')))
            ->map(function ($breakpoint) {
                return "{$breakpoint}:";
            })->toArray();

        $attributesToExclude = ['src', 'placeholder', 'webp', 'avif', 'ratio', 'glide:', 'default:', 'quality:'];

        return collect($this->params)
            ->reject(function ($value, $name) use ($breakpointPrefixes, $attributesToExclude) {
                if (Str::contains($name, array_merge($attributesToExclude, $breakpointPrefixes))) {
                    return true;
                }

                return false;
            })
            ->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ');
    }

    private function includePlaceholder(): bool
    {
        return $this->params->has('placeholder')
            ? $this->params->get('placeholder')
            : config('statamic.responsive-images.placeholder', true);
    }
}
