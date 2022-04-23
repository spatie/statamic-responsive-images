<?php

namespace Spatie\ResponsiveImages\Tags;

use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\Breakpoint;
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
                'height' => $responsive->assetHeight(),
                'asset' => $responsive->asset->toAugmentedArray(),
            ])->render();
        }

        $includePlaceholder = $this->includePlaceholder();

        $sources = $responsive->breakPoints()
            ->map(function (Breakpoint $breakpoint) use ($includePlaceholder) {
                return [
                    'media' => $breakpoint->getMediaString(),
                    'srcSet' => $breakpoint->getSrcSet($includePlaceholder),
                    'srcSetWebp' => $this->getSrcSetFromBreakpoint($breakpoint, 'webp', $includePlaceholder),
                    'srcSetAvif' => $this->getSrcSetFromBreakpoint($breakpoint, 'avif', $includePlaceholder),
                    'placeholder' => $breakpoint->placeholder(),
                ];
            });

        return view('responsive-images::responsiveImage', [
            'attributeString' => $this->getAttributeString(),
            'includePlaceholder' => $includePlaceholder,
            'placeholder' => $sources->last()['placeholder'],
            'src' => $responsive->asset->url(),
            'sources' => $sources,
            'width' => $responsive->asset->width(),
            'height' => $responsive->assetHeight(),
            'asset' => $responsive->asset->toAugmentedArray(),
        ])->render();
    }

    private function getAttributeString(): string
    {
        $breakpointPrefixes = collect(array_keys(config('statamic.responsive-images.breakpoints')))
            ->map(function ($breakpoint) {
                return "{$breakpoint}:";
            })->toArray();

        return collect($this->params)
            ->reject(function ($value, $name) use ($breakpointPrefixes) {
                if (Str::contains($name, array_merge(['src', 'placeholder', 'webp', 'ratio', 'glide:', 'default:'], $breakpointPrefixes))) {
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

    private function getSrcSetFromBreakpoint(Breakpoint $breakpoint, string $format, bool $includePlaceholder): string|null
    {
        $isFormatIncluded = $this->params->has($format)
            ? $this->params->get($format)
            : config('statamic.responsive-images.' . $format, $format === 'webp');

        return $isFormatIncluded
            ? $breakpoint->getSrcSet($includePlaceholder, $format)
            : null;
    }
}
