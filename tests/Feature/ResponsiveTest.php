<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\Exceptions\InvalidAssetException;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFieldtype;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Facades\Asset;
use Statamic\Facades\Stache;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Tags\Parameters;

beforeEach(function () {
    $this->asset = $this->uploadTestImageToTestContainer();
    Stache::clear();
});

it('can initialize using an asset', function () {
    $responsive = new Responsive($this->asset, new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('throws on a zero width or height image', function () {
    $file = new UploadedFile($this->getZeroWidthTestSvg(), 'zerowidthtest.svg');
    $path = ltrim('/' . $file->getClientOriginalName(), '/');
    $asset = $this->assetContainer->makeAsset($path)->upload($file);

    new Responsive($asset, new Parameters());
})->throws(InvalidAssetException::class);

it('can initialize using the assets path', function () {
    $responsive = new Responsive($this->asset->resolvedPath(), new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('can initialize using the assets url', function () {
    $responsive = new Responsive($this->asset->url(), new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('can initialize using an argument asset', function () {
    $responsive = new Responsive($this->asset->toAugmentedArray(), new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('can initialize using a value', function () {
    $value = new Value($this->asset);

    $responsive = new Responsive($value, new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('can initialize using a collection value', function () {
    $value = new Value(new Collection([$this->asset]));

    $responsive = new Responsive($value, new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('can initialize using a query builder', function () {
    $value = new Value(Asset::query()->where('container', $this->assetContainer->handle()));

    $responsive = new Responsive($value, new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('can initialize using a string value', function () {
    $value = new Value($this->asset->resolvedPath(), 'url');

    $responsive = new Responsive($value, new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('can initialize using a string url value', function () {
    $value = new Value($this->asset->url(), 'url');

    $responsive = new Responsive($value, new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it('can initialize using values from the fieldtype', function () {
    $fieldtype = new ResponsiveFieldtype();

    $field = new Field('image', [
        'breakpoints' => [],
        'use_breakpoints' => false,
        'container' => $this->asset->containerHandle(),
        'allow_uploads' => true,
        'allow_ratio' => true,
        'allow_fit' => true,
    ]);
    $fieldtype->setField($field);

    $value = new Value([
        'src' => $this->asset->path(),
    ], 'image', $fieldtype);

    $responsive = new Responsive($value, new Parameters());

    expect($responsive->asset->id())->toEqual($this->asset->id());
});

it("throws if it can't find an asset", function () {
    new Responsive('doesnt-exist', new Parameters());
})->throws(AssetNotFoundException::class);

it('can generate a set of breakpoints for an asset', function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'ratio' => 1,
        'lg:ratio' => 1.5,
    ]));

    expect(
        $responsive->breakPoints()->toArray()
    )->toEqual([
        ['asset' => $this->asset, 'label' => 'lg', 'minWidth' => 1024, 'parameters' => ['ratio' => 1.5], 'widthUnit' => 'px'],
        ['asset' => $this->asset, 'label' => 'default', 'minWidth' => 0, 'parameters' => ['ratio' => 1], 'widthUnit' => 'px'],
    ]);
});

it('can parse a basic fraction', function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'ratio' => 1,
        'lg:ratio' => '1 / 2',
    ]));

    expect(
        $responsive->breakPoints()->toArray()
    )->toEqual([
        ['asset' => $this->asset, 'label' => 'lg', 'minWidth' => 1024, 'parameters' => ['ratio' => 1 / 2], 'widthUnit' => 'px'],
        ['asset' => $this->asset, 'label' => 'default', 'minWidth' => 0, 'parameters' => ['ratio' => 1.0], 'widthUnit' => 'px'],
    ]);
});

it("uses the default asset ratio if a default isn't provided", function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'lg:ratio' => 1.5,
    ]));

    expect(
        $responsive->breakPoints()->toArray()
    )->toEqual([
        ['asset' => $this->asset, 'label' => 'lg', 'minWidth' => 1024, 'parameters' => ['ratio' => 1.5], 'widthUnit' => 'px'],
        ['asset' => $this->asset, 'label' => 'default', 'minWidth' => 0, 'parameters' => ['ratio' => 1.2142857142857142], 'widthUnit' => 'px'],
    ]);
});

test('unknown breakpoints get ignored', function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'lg:ratio' => 1.5,
        'bla:ratio' => 2,
    ]));

    expect(
        $responsive->breakPoints()->toArray()
    )->toEqual([
        ['asset' => $this->asset, 'label' => 'lg', 'minWidth' => 1024, 'parameters' => ['ratio' => 1.5, 'bla:ratio' => 2], 'widthUnit' => 'px'],
        ['asset' => $this->asset, 'label' => 'default', 'minWidth' => 0, 'parameters' => ['bla:ratio' => 2], 'widthUnit' => 'px'],
    ]);
});

it('can retrieve the default breakpoint', function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'lg:ratio' => 1.5,
    ]));

    expect(
        $responsive->defaultBreakpoint()->toArray()
    )->toEqual([
        'asset' => $this->asset,
        'label' => 'default',
        'minWidth' => 0,
        'parameters' => [
            'ratio' => 1.2142857142857142,
        ],
        'widthUnit' => 'px'
    ]);
});

it('can retrieve the height of an image for a ratio', function () {
    $responsive = new Responsive($this->asset, new Parameters());

    expect($responsive->assetHeight())->toEqual(280.0);
});

it('can retrieve the height of an image for a breakpoint ratio', function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'lg:ratio' => 2 / 1,
    ]));

    expect($responsive->assetHeight('lg'))->toEqual(170.0); // Width = 340
});

it('asset height is `null` for a non-existing breakpoint', function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'lg:ratio' => 2 / 1,
    ]));

    expect($responsive->assetHeight('bla'))->toEqual(null);
});

test('can toggle formats between all breakpoints', function () {
    config()->set('statamic.responsive-images.webp', false);
    config()->set('statamic.responsive-images.avif', false);

    $responsive = new Responsive($this->asset, new Parameters([
        'ratio' => 1,
        'avif' => true,
        'sm:ratio' => 1,
        'sm:avif' => false,
        'md:ratio' => 1,
        'md:avif' => true,
        'lg:ratio' => 1,
        'lg:avif' => false,
        'xl:ratio' => 1,
        'xl:avif' => true,
        '2xl:ratio' => 1,
    ]));

    $breakpointsWithSources = $responsive->breakPoints()->map(function (Breakpoint $breakpoint) {
        $breakpointArr = $breakpoint->toArray();
        $breakpointArr['sources'] = collect($breakpoint->getSources()->toArray());
        return $breakpointArr;
    });


    $avifPerBreakpoint = [
        'default' => true,
        'sm' => false,
        'md' => true,
        'lg' => false,
        'xl' => true,
        '2xl' => true
    ];

    foreach ($avifPerBreakpoint as $breakpointLabel => $isAvifEnabled) {
        expect(
            $breakpointsWithSources
                ->where('label', $breakpointLabel)
                ->first()['sources']
                ->where('format', 'avif')
                ->count() === 1
        )->toBe($isAvifEnabled);
    }
});

test('can toggle placeholder in srcsets between all breakpoints', function () {
    config()->set('statamic.responsive-images.webp', false);
    config()->set('statamic.responsive-images.avif', false);

    $responsive = new Responsive($this->asset, new Parameters([
        'ratio' => 1,
        'placeholder' => true,
        'sm:ratio' => 1,
        'sm:placeholder' => false,
        'md:ratio' => 1,
        'md:placeholder' => true,
        'lg:ratio' => 1,
    ]));

    $breakpointsWithSources = $responsive->breakPoints()->map(function (Breakpoint $breakpoint) {
        $breakpointArr = $breakpoint->toArray();
        $breakpointArr['sources'] = collect($breakpoint->getSources()->toArray());
        return $breakpointArr;
    });

    $avifPerBreakpoint = [
        'default' => true,
        'sm' => false,
        'md' => true,
        'lg' => true,
    ];

    foreach ($avifPerBreakpoint as $breakpointLabel => $isPlaceholderOutput) {
        $srcset = $breakpointsWithSources
            ->where('label', $breakpointLabel)
            ->first()['sources']
            ->first()['srcSet'];

        preg_match('/data:image\/svg\+xml;base64,(.*) 32w/', $srcset, $svgMatches);

        expect(isset($svgMatches[1]))->toBe($isPlaceholderOutput);
    }
});