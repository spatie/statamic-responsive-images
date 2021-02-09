<?php

namespace Spatie\ResponsiveImages\Jobs;

use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Image;
use Statamic\Facades\URL;
use Statamic\Imaging\GlideImageManipulator;

class GenerateGlideImageJob extends GenerateImageJob
{
    protected function imageUrl(): string
    {
        $manipulator = Image::manipulate($this->asset);

        foreach ($this->params as $param => $value) {
            if (is_array($value)) {
                $value = $value['value'] ?? $value[0] ?? null;
            }

            $manipulator->$param($value);
        }

        return URL::makeRelative($manipulator->build());
    }
}
