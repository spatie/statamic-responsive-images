<?php

namespace Rias\ResponsiveImages;

use League\Glide\Server;
use Statamic\Assets\Asset;
use Statamic\Facades\Image;
use Statamic\Facades\URL;
use Statamic\Imaging\GlideImageManipulator;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

class Responsive extends Tags
{
    public function __call($method, $args)
    {
        $tag = explode(':', $this->tag, 2)[1];
        $item = $this->context->get($tag);

        /** @var Asset $asset */
        $asset = Asset::find($item);

        $base64Svg = base64_encode($this->svg($asset));
        $placeholder = "data:image/svg+xml;base64,{$base64Svg} 32w";

        $widths = (new WidthCalculator())
            ->calculateWidthsFromAsset($asset)
            ->sort();

        $srcSet = $widths
            ->map(function (int $width) use ($asset) {
                $src = URL::makeRelative($this->getManipulator($asset)->width($width)->build());

                return "{$src} {$width}w";
            })
            ->prepend($placeholder)
            ->implode(', ');

        $srcSetWebp = $widths
            ->map(function (int $width) use ($asset) {
                $webpBuilder = $this->getManipulator($asset);
                $webpBuilder->setParam('fm', 'webp');
                $srcWebp = URL::makeRelative($webpBuilder->width($width)->build());

                return "{$srcWebp} {$width}w";
            })
            ->prepend($placeholder)
            ->implode(', ');

        $attributeString = collect($this->params)
            ->map(function ($value, $name) {
                return $name.'="'.$value.'"';
            })->implode(' ');

        return view('responsive-images::responsiveImageWithPlaceholder', [
            'attributeString' => $attributeString,
            'src' => $asset->url(),
            'srcSet' => $srcSet,
            'srcSetWebp' => $srcSetWebp,
            'width' => $asset->width(),
        ])->render();
    }

    private function svg(Asset $asset): string
    {
        $oldConfigValue = config('statamic.assets.image_manipulation.cache');
        config()->set('statamic.assets.image_manipulation.cache', true);

        // Make sure to build the image first, otherwise Glide won't find it
        $this->getManipulator($asset)->width(32)->blur(5)->build();

        /** @var Server $glideServer */
        $glideServer = app(Server::class);
        $placeholder = $glideServer->getImageAsBase64($asset->path(), ['w' => 32, 'blur' => 5]);

        config()->set('statamic.assets.image_manipulation.cache', $oldConfigValue);

        return view('responsive-images::placeholderSvg', [
            'width' => $asset->width(),
            'height' => $asset->height(),
            'image' => "data:image/jpeg;base64,{$placeholder}",
        ])->render();
    }

    private function getManipulator(Asset $asset): GlideImageManipulator
    {
        return Image::manipulate($asset);
    }
}
