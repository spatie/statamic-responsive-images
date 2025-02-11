<?php

namespace Spatie\ResponsiveImages;

use Exception;

class AssetNotFoundException extends Exception
{
    public static function create($assetParam)
    {
        if (is_array($assetParam)) {
            $assetParam = $assetParam['url'] ?? '';
        }

        return new self("Could not find asset {$assetParam}");
    }
}
