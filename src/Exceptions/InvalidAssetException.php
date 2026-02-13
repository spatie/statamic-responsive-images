<?php

namespace Spatie\ResponsiveImages\Exceptions;

use Exception;
use Statamic\Assets\Asset;

class InvalidAssetException extends Exception
{
    public static function zeroWidthOrHeight(Asset $asset): self
    {
        return new self("Asset {$asset->id()} has 0 width or height. Cannot create responsive variants.");
    }
}
