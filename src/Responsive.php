<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;
use League\Glide\Server;
use Statamic\Assets\Asset;
use Statamic\Facades\Image;
use Statamic\Facades\URL;
use Statamic\Imaging\GlideImageManipulator;
use Statamic\Imaging\ImageGenerator;
use Statamic\Tags\Tags;

class Responsive extends Tags
{
    /** @var \League\Glide\Server */
    private $server;

    /** @var \Statamic\Imaging\ImageGenerator */
    private $imageGenerator;

    /** @var \Spatie\ResponsiveImages\WidthCalculator */
    private $widthCalculator;

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

        /** @var Asset $asset */
        $asset = Asset::find($this->context->get($tag));

        if ($asset->extension() === "svg") {
            return view('responsive-images::responsiveImage', [
                'attributeString' => $this->getAttributeString(),
                'src' => $asset->url(),
                'srcSet' => '',
                'srcSetWebp' => '',
                'width' => $asset->width(),
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
            ])->render();
        }

        return view('responsive-images::responsiveImage', [
            'attributeString' => $this->getAttributeString(),
            'src' => $asset->url(),
            'srcSet' => $this->buildSrcSet($widths, $asset),
            'srcSetWebp' => $includeWebp ? $this->buildSrcSet($widths, $asset, null, 'webp') : null,
            'width' => $asset->width(),
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
            'height' => $asset->height(),
            'image' => $base64Placeholder,
        ])->render();
    }

    private function getManipulator(Asset $asset): GlideImageManipulator
    {
        return Image::manipulate($asset);
    }

    private function getAttributeString(): string
    {
        return collect($this->params)
            ->except(['placeholder', 'webp'])
            ->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ');
    }

    private function buildSrcSet(Collection $widths, Asset $asset, string $placeholder = null, string $format = null): string
    {
        return $widths
            ->map(function (int $width) use ($asset, $format) {
                $manipulator = $this->getManipulator($asset);

                if ($format) {
                    $manipulator->setParam('fm', $format);
                }

                $src = URL::makeRelative($manipulator->width($width)->build());

                return "{$src} {$width}w";
            })
            ->when($placeholder, function (Collection $widths) use ($placeholder) {
                return $widths->prepend($placeholder);
            })
            ->implode(', ');
    }
}
