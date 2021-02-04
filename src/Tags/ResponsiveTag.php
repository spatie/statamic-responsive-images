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
        $responsive->tag = 'responsive:url';
        $responsive->setParameters($parameters);

        return $responsive->__call('responsive:url', []);
    }

    public function __call($method, $args): string
    {
        $tag = explode(':', $this->tag, 2)[1];
        $assetParam = $this->context->get($tag)
            ? $this->context->get($tag)
            : $this->params->get('src');

        try {
            $responsive = new Responsive($assetParam, $this->params);
        } catch (AssetNotFoundException $e) {
            return '';
        }

        if ($responsive->asset->extension() === "svg") {
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
                    'srcSetWebp' => $this->includeWebp()
                        ? $breakpoint->getSrcSet($includePlaceholder, 'webp')
                        : null,
                ];
            });

        return view('responsive-images::responsiveImage', [
            'attributeString' => $this->getAttributeString(),
            'placeholder' => $includePlaceholder,
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
                if (Str::contains($name, array_merge(['placeholder', 'webp', 'ratio', 'glide:', 'default:'], $breakpointPrefixes))) {
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

    private function includeWebp(): bool
    {
        $includeWebp = $this->params->get('webp');

        if (isset($includeWebp)) {
            return $includeWebp;
        }

        return config('statamic.responsive-images.webp', true);
    }
}
