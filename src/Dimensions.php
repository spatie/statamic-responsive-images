<?php

namespace Spatie\ResponsiveImages;

class Dimensions
{
    /**
     * @var int Width in pixels
     */
    public int $width;

    /**
     * @var int Height in pixels
     */
    public int $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    public function setHeight(int $height)
    {
        $this->height = $height;
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