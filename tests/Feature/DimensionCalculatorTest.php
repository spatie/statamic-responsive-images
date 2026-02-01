<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\Dimensions;
use Spatie\ResponsiveImages\DimensionCalculator;
use Spatie\ResponsiveImages\Responsive;
use Spatie\ResponsiveImages\Source;
use Spatie\ResponsiveImages\Tags\ResponsiveTag;
use Statamic\Assets\Asset;
use Statamic\Tags\Parameters;

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
        ->calculateForBreakpoint($source)
        ->map(function ($dimension) {
            return $dimension->width;
        })
        ->toArray();
}

it('can calculate the optimized widths from an asset', function () {
    Storage::fake('public');

    $asset = test()->uploadTestImageToTestContainer();

    $breakpoint = new Breakpoint($asset, 'default', 0, []);

    $widths = getWidths($asset, $breakpoint);

    expect($widths)->toEqual([
        0 => 340,
        1 => 284,
        2 => 237,
    ]);

    $smallAsset = test()->uploadTestImageToTestContainer(test()->getSmallTestJpg());

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

it('can calculate the optimized widths for different dimensions with a custom threshold', function () {
    config()->set('statamic.responsive-images.dimension_calculator_threshold', 0.25);

    $stubbedAsset = stubAsset(2400, 1800, 3000 * 1024);
    $breakpoint = new Breakpoint($stubbedAsset, 'default', 0, []);

    $widths = getWidths($stubbedAsset, $breakpoint);

    expect($widths)->toEqual([
        0 => 2400,
        1 => 1200,
        2 => 600,
        3 => 300,
        4 => 150,
    ]);
});

it('filters out widths to be less than max width specified in config', function() {
    config()->set('statamic.responsive-images.max_width', 300);

    $asset = test()->uploadTestImageToTestContainer();

    $breakpoint = new Breakpoint($asset, 'default', 0, []);

    expect(getWidths($asset, $breakpoint))->toEqualCanonicalizing([237, 284]);
});

it('filters out widths to be less than max width specified in glide width param', function() {
    $asset = test()->uploadTestImageToTestContainer();

    $breakpoint = new Breakpoint($asset, 'default', 0, ['glide:width' => 300]);

    expect(getWidths($asset, $breakpoint))->toEqualCanonicalizing([237, 284]);
});

test('max width from glide width param takes precedence over config when filtering widths', function() {
    config()->set('statamic.responsive-images.max_width', 250);

    $asset = test()->uploadTestImageToTestContainer();

    $breakpoint = new Breakpoint($asset, 'default', 0, ['glide:width' => 300]);

    expect(getWidths($asset, $breakpoint))->toEqualCanonicalizing([237, 284]);
});

it('returns one dimension with equal width of max width when all dimensions have been filtered out', function () {
    config()->set('statamic.responsive-images.max_width', 25);

    $asset = test()->uploadTestImageToTestContainer();

    $breakpoint = new Breakpoint($asset, 'default', 0, []);

    $widths = getWidths($asset, $breakpoint);

    expect($widths)->toHaveCount(1);
    expect($widths[0])->toBe(25);
});

it('uses custom dimension calculator', function () {
    $this->mock(DimensionCalculator::class, function ($mock) {
        $mock->shouldReceive('calculateForBreakpoint')->andReturn(collect([new Dimensions(100, 100)]));
        $mock->shouldReceive('calculateForImgTag')->andReturn(new Dimensions(100, 100));
        $mock->shouldReceive('calculateForPlaceholder')->andReturn(new Dimensions(100, 100));
    });

    $asset = test()->uploadTestImageToTestContainer();

    $responsive = new Responsive($asset, new Parameters(['placeholder' => false, 'webp' => false]));

    expect(
        $responsive->defaultBreakpoint()->sources()->first()->toArray()['srcSet']
    )->toContain('w=100&h=100');
});

test('ResponsiveDimensionCalculator returns correct height for img tag without specifying ratio', function () {
    $asset = test()->uploadTestImageToTestContainer();

    $breakpoint = new Breakpoint($asset, 'default', 0, []);

    $calculatedDimensions = app(DimensionCalculator::class)->calculateForImgTag($breakpoint);

    expect($calculatedDimensions->getHeight())->toEqual(280);
});

test('ResponsiveDimensionCalculator returns correct height for img tag when specifying ratio', function () {
    $asset = test()->uploadTestImageToTestContainer();

    $breakpoint = new Breakpoint($asset, 'default', 0, ['ratio' => 2 / 1]);

    $calculatedDimensions = app(DimensionCalculator::class)->calculateForImgTag($breakpoint);

    expect($calculatedDimensions->getHeight())->toEqual(170);
});

test('ResponsiveDimensionCalculator does not exceed original image dimensions when switching aspect ratios', function () {
    // Test case from GitHub issue #257
    // Original image: 3000x1688px (landscape, ratio ≈ 1.78)
    // Custom ratio: 9/16 = 0.5625 (portrait)
    $stubbedAsset = stubAsset(3000, 1688, 5000 * 1024);
    
    $breakpoint = new Breakpoint($stubbedAsset, 'default', 0, ['ratio' => 9 / 16]);
    
    $calculatedDimensions = app(DimensionCalculator::class)->calculateForImgTag($breakpoint);
    
    // Dimensions should not exceed original image dimensions
    expect($calculatedDimensions->getWidth())->toBeLessThanOrEqual(3000);
    expect($calculatedDimensions->getHeight())->toBeLessThanOrEqual(1688);
    
    // With ratio 9/16, height should be constrained to 1688, width should be 1688 * (9/16) ≈ 950
    expect($calculatedDimensions->getHeight())->toBe(1688);
    expect($calculatedDimensions->getWidth())->toBe(950);
});

test('ResponsiveDimensionCalculator constrains dimensions correctly when switching from portrait to landscape', function () {
    // Original image: 1688x3000px (portrait)
    // Custom ratio: 16/9 = 1.78 (landscape)
    $stubbedAsset = stubAsset(1688, 3000, 5000 * 1024);
    
    $breakpoint = new Breakpoint($stubbedAsset, 'default', 0, ['ratio' => 16 / 9]);
    
    $calculatedDimensions = app(DimensionCalculator::class)->calculateForImgTag($breakpoint);
    
    // Dimensions should not exceed original image dimensions
    expect($calculatedDimensions->getWidth())->toBeLessThanOrEqual(1688);
    expect($calculatedDimensions->getHeight())->toBeLessThanOrEqual(3000);
    
    // With ratio 16/9, width should be constrained to 1688, height should be 1688 / (16/9) ≈ 950
    expect($calculatedDimensions->getWidth())->toBe(1688);
    expect($calculatedDimensions->getHeight())->toBe(950);
});

test('ResponsiveDimensionCalculator calculateDimensions does not exceed original image dimensions', function () {
    // Test the calculateDimensions method (used for breakpoints)
    $stubbedAsset = stubAsset(3000, 1688, 5000 * 1024);
    $breakpoint = new Breakpoint($stubbedAsset, 'default', 0, ['ratio' => 9 / 16]);
    $source = new Source($breakpoint);
    
    $dimensions = app(DimensionCalculator::class)->calculateForBreakpoint($source);
    
    // The first (largest) dimension should not exceed original image dimensions
    $firstDimension = $dimensions->first();
    expect($firstDimension->getWidth())->toBeLessThanOrEqual(3000);
    expect($firstDimension->getHeight())->toBeLessThanOrEqual(1688);
    
    // All dimensions should respect the constraint
    $dimensions->each(function ($dimension) {
        expect($dimension->getWidth())->toBeLessThanOrEqual(3000);
        expect($dimension->getHeight())->toBeLessThanOrEqual(1688);
    });
});