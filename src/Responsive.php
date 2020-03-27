<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;
use League\Glide\Server;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Statamic\Assets\Asset;
use Statamic\Facades\Image;
use Statamic\Facades\URL;
use Statamic\Imaging\GlideImageManipulator;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

class Responsive extends Tags
{
    /** @var \League\Glide\Server */
    private $server;

    /** @var \Statamic\Imaging\ImageGenerator */
    private $imageGenerator;

    /** @var \Spatie\ResponsiveImages\WidthCalculator */
    private $widthCalculator;

    /** @var float|null */
    private $ratio;

    public function __construct(Server $server, ImageGenerator $imageGenerator, WidthCalculator $widthCalculator)
    {
        $this->server = $server;
        $this->imageGenerator = $imageGenerator;
        $this->widthCalculator = $widthCalculator;
    }

    public function __call($method, $args)
    {
        $tag = explode(':', $this->tag, 2)[1];
        $includePlaceholder = $this->params['placeholder'] ?? true;
        $includeWebp = $this->params['webp'] ?? true;
        $this->ratio = $this->params['ratio'] ?? null;

        if (Str::contains($this->ratio, '/')) {
            [$width, $height] = explode('/', $this->ratio);
            $this->ratio = (int) $width / (int) $height;
        }

        /** @var Asset $asset */
        $asset = Asset::find($this->context->get($tag));

        if ($asset->extension() === "svg") {
            return view('responsive-images::responsiveImage', [
                'attributeString' => $this->getAttributeString(),
                'src' => $asset->url(),
                'srcSet' => '',
                'srcSetWebp' => '',
                'width' => $asset->width(),
                'height' => $this->ratio ? $asset->width() / (float) $this->ratio : $asset->height(),
            ])->render();
        }

        $widths = $this->widthCalculator->calculateWidthsFromAsset($asset)->sort();

        if ($includePlaceholder) {
            $base64Svg = base64_encode($this->svg($asset));
            $placeholder = "data:image/svg+xml;base64,{$base64Svg} 32w";

            return view('responsive-images::responsiveImageWithPlaceholder', [
                'attributeString' => $this->getAttributeString(),
                'src' => $asset->url(),
                'srcSet' => $this->buildSrcSet($widths, $asset, $placeholder),
                'srcSetWebp' => $includeWebp ? $this->buildSrcSet($widths, $asset, $placeholder, 'webp') : null,
                'width' => $asset->width(),
                'height' => $this->ratio ? $asset->width() / (float) $this->ratio : $asset->height(),
            ])->render();
        }

        return view('responsive-images::responsiveImage', [
            'attributeString' => $this->getAttributeString(),
            'src' => $asset->url(),
            'srcSet' => $this->buildSrcSet($widths, $asset),
            'srcSetWebp' => $includeWebp ? $this->buildSrcSet($widths, $asset, null, 'webp') : null,
            'width' => $asset->width(),
            'height' => $this->ratio ? $asset->width() / (float) $this->ratio : $asset->height(),
        ])->render();
    }

    private function svg(Asset $asset): string
    {
        $path = $this->imageGenerator->generateByAsset($asset, [
            'w' => 32,
            'blur' => 5,
        ]);

        $source = base64_encode($this->server->getCache()->read($path));
        $base64Placeholder = "data:{$this->server->getCache()->getMimetype($path)};base64,{$source}";

        return view('responsive-images::placeholderSvg', [
            'width' => $asset->width(),
            'height' => $this->ratio ? $asset->width() / (float) $this->ratio : $asset->height(),
            'image' => $base64Placeholder,
        ])->render();
    }

    private function getAttributeString(): string
    {
        return collect($this->params)
            ->except(['placeholder', 'webp', 'ratio'])
            ->reject(function ($value, $name) {
                return Str::contains($name, 'glide:');
            })
            ->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ');
    }

    private function buildSrcSet(Collection $widths, Asset $asset, string $placeholder = null, string $format = null): string
    {
        $params = $this->getGlideParams();

        if ($format) {
            $params['fm'] = $format;
        }

        /* We don't want any heights specified other than our own */
        unset($params['height']);
        unset($params['h']);

        return $widths
            /* If a width is specified, consider it a max width */
            ->when(isset($params['width']) || isset($params['w']), function ($widths) use ($params) {
                return $widths->filter(function (int $width) use ($params) {
                    return $width <= $params['width'] ?? $params['w'];
                });
            })
            ->map(function (int $width) use ($params, $asset) {
                return "{$this->buildImage($asset, $width)} {$width}w";
            })
            ->when($placeholder, function (Collection $widths) use ($placeholder) {
                return $widths->prepend($placeholder);
            })
            ->implode(', ');
    }

    private function getGlideParams(): array
    {
        return collect($this->params)
            ->filter(function ($value, $name) {
                return Str::contains($name, 'glide:');
            })
            ->mapWithKeys(function ($value, $name) {
                return [str_replace('glide:', '', $name) => $value];
            })
            ->toArray();
    }

    private function buildImage(Asset $asset, int $width)
    {
        $params['width'] = $width;

        if ($this->ratio) {
            $params['height'] = $width / (float) $this->ratio;
        }

        return URL::makeRelative((new GenerateImageJob($asset, $params))->handle());
    }
}
