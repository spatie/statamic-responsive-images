<?php

namespace Spatie\ResponsiveImages;

class Dimensions
{
    public function __construct(
        public int $width,
        public int $height,
    ) {
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function toArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
