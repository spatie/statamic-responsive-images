<?php

namespace Spatie\ResponsiveImages\Tags;

use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\DimensionCalculator;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

class ResponsiveTag extends Tags
{
    protected static $handle = 'responsive';

    public static function render(...$arguments): string
    {
        $asset = $arguments[0];
        $parameters = $arguments[1] ?? [];

        /** @var self $responsive */
        $responsive = app(self::class);
        $responsive->setContext(['url' => $asset]);
        $responsive->setParameters($parameters);

        return $responsive->wildcard('url');
    }

    public function wildcard(string $tag): string
    {
        $this->params->put('src', $this->context->get($tag));

        return $this->index();
    }

    public function index(): string
    {
        try {
            $responsive = new Responsive($this->params->get('src'), $this->params);
        } catch (AssetNotFoundException|NotFoundHttpException) {
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

        $width = $dimensions->width;
        $height = $dimensions->height;

        $src = app(GenerateImageJob::class, [
            'asset' => $responsive->asset,
            'params' => array_merge($this->getGlideParams(), ['width' => $width, 'height' => $height]),
        ])->handle();

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
            'hasSources' => $breakpoints->map(fn ($breakpoint) => $breakpoint->sources())->flatten()->isNotEmpty(),
        ])->render();
    }

    private function getGlideParams(): array
    {
        return collect($this->params)
            ->reject(fn ($value, $name) => ! str_starts_with($name, 'glide:'))
            ->mapWithKeys(fn ($value, $name) => [str_replace('glide:', '', $name) => $value])
            ->toArray();
    }

    private function getAttributeString(): string
    {
        $breakpointPrefixes = collect(array_keys(config('statamic.responsive-images.breakpoints')))
            ->map(fn ($breakpoint) => "{$breakpoint}:")
            ->toArray();

        $attributesToExclude = ['src', 'placeholder', 'webp', 'avif', 'ratio', 'glide:', 'default:', 'quality:'];

        return collect($this->params)
            ->reject(fn ($value, $name) => Str::contains($name, array_merge($attributesToExclude, $breakpointPrefixes)))
            ->map(fn ($value, $name) => "{$name}=\"{$value}\"")
            ->implode(' ');
    }

    private function includePlaceholder(): bool
    {
        return $this->params->has('placeholder')
            ? $this->params->get('placeholder')
            : config('statamic.responsive-images.placeholder', true);
    }
}
