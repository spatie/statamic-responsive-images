<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\DimensionCalculator;
use Spatie\ResponsiveImages\Source;
use Statamic\Assets\Asset;

function stubAsset(int $width, int $height, int $fileSize)
{
    $stubbedAsset = test()->createMock(Asset::class);
    $stubbedAsset->method('size')->willReturn($fileSize);
    $stubbedAsset->method('width')->willReturn($width);
    $stubbedAsset->method('height')->willReturn($height);
    return $stubbedAsset;
}

function getWidths(Asset $asset, Breakpoint $breakpoint): array
{
    $source = new Source($breakpoint);

    return app(DimensionCalculator::class)
        ->calculate($source)
        ->map(function ($dimension) {
            return $dimension->width;
        })
        ->toArray();
}

function createAsset(?string $filePath = null)
{
    if (!$filePath) $filePath = test()->getTestJpg();

    $file = new UploadedFile($filePath, 'test.jpg');
    $path = ltrim('/' . $file->getClientOriginalName(), '/');
    $asset = test()->assetContainer->makeAsset($path)->upload($file);
    return $asset;
}

it('can calculate the optimized widths from an asset', function () {
    Storage::fake('public');

    $asset = createAsset();

    $breakpoint = new Breakpoint($asset, 'default', 0, []);

    $widths = getWidths($asset, $breakpoint);

    expect($widths)->toEqual([
        0 => 340,
        1 => 284,
        2 => 237,
    ]);

    $smallAsset = createAsset(test()->getSmallTestJpg());

    $breakpoint = new Breakpoint($smallAsset, 'default', 0, []);

    $widths = getWidths($smallAsset, $breakpoint);

    expect($widths)->toEqual([
        0 => 150,
    ]);
});

it('can calculate the optimized widths for different dimensions', function () {
    $stubbedAsset = stubAsset(300, 200, 300 * 1024);
    $breakpoint = new Breakpoint($stubbedAsset, 'default', 0, []);

    $widths = getWidths($stubbedAsset, $breakpoint);

    expect($widths)->toEqual([
        0 => 300,
        1 => 250,
        2 => 210,
        3 => 175,
        4 => 147,
        5 => 122,
        6 => 102,
        7 => 86,
        8 => 72,
        9 => 60,
    ]);

    $stubbedAsset = stubAsset(2400, 1800, 3000 * 1024);
    $breakpoint = new Breakpoint($stubbedAsset, 'default', 0, []);

    $widths = getWidths($stubbedAsset, $breakpoint);

    expect($widths)->toEqual([
        0 => 2400,
        1 => 2007,
        2 => 1680,
        3 => 1405,
        4 => 1176,
        5 => 983,
        6 => 823,
        7 => 688,
        8 => 576,
        9 => 482,
        10 => 403,
        11 => 337,
        12 => 282,
        13 => 236,
        14 => 197,
        15 => 165,
    ]);

    $stubbedAsset = stubAsset(8200, 5500, 12000 * 1024);
    $breakpoint = new Breakpoint($stubbedAsset, 'default', 0, []);

    $widths = getWidths($stubbedAsset, $breakpoint);

    expect($widths)->toEqual([
        0 => 8200,
        1 => 6860,
        2 => 5740,
        3 => 4802,
        4 => 4017,
        5 => 3361,
        6 => 2812,
        7 => 2353,
        8 => 1968,
        9 => 1647,
        10 => 1378,
        11 => 1153,
        12 => 964,
        13 => 807,
        14 => 675,
        15 => 565,
        16 => 472,
        17 => 395,
        18 => 330,
        19 => 276,
    ]);
});

it('filters out widths to be less than max width specified in config', function() {
    config()->set('statamic.responsive-images.max_width', 300);

    $asset = createAsset();

    $breakpoint = new Breakpoint($asset, 'default', 0, []);

    expect(getWidths($asset, $breakpoint))->toEqualCanonicalizing([237, 284]);
});

it('filters out widths to be less than max width specified in glide width param', function() {
    $asset = createAsset();

    $breakpoint = new Breakpoint($asset, 'default', 0, ['glide:width' => 300]);

    expect(getWidths($asset, $breakpoint))->toEqualCanonicalizing([237, 284]);
});

test('max width from glide width param takes precedence over config when filtering widths', function() {
    config()->set('statamic.responsive-images.max_width', 250);

    $asset = createAsset();

    $breakpoint = new Breakpoint($asset, 'default', 0, ['glide:width' => 300]);

    expect(getWidths($asset, $breakpoint))->toEqualCanonicalizing([237, 284]);
});

it('returns one dimension with equal width of max width when all dimensions have been filtered out', function () {
    config()->set('statamic.responsive-images.max_width', 25);

    $asset = createAsset();

    $breakpoint = new Breakpoint($asset, 'default', 0, []);

    expect(getWidths($asset, $breakpoint))->toHaveCount(1);
});