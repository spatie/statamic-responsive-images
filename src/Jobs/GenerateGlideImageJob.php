<?php

namespace Spatie\ResponsiveImages\Jobs;

use Statamic\Facades\Image;
use Statamic\Facades\URL;

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

        if (config('statamic.responsive-images.return_absolute_url', false)) {
            return URL::makeAbsolute($manipulator->build());
        }

        return URL::makeRelative($manipulator->build());
    }
}
