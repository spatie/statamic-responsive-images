<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\Exceptions\InvalidAssetException;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFieldtype;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Facades\Asset;
use Statamic\Facades\Stache;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Tags\Parameters;

beforeEach(function () {
    $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
    $path = ltrim('/' . $file->getClientOriginalName(), '/');
    $this->asset = $this->assetContainer->makeAsset($path)->upload($file);

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
        ['asset' => $this->asset, 'label' => 'lg', 'value' => 1024, 'unit' => 'px', 'media' => '(min-width: 1024px)', 'parameters' => ['ratio' => 1.5]],
        ['asset' => $this->asset, 'label' => 'default', 'value' => 0, 'unit' => 'px', 'media' => '', 'parameters' => ['ratio' => 1]],
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
        ['asset' => $this->asset, 'label' => 'lg', 'value' => 1024, 'parameters' => ['ratio' => 1 / 2], 'unit' => 'px', 'media' => '(min-width: 1024px)'],
        ['asset' => $this->asset, 'label' => 'default', 'value' => 0, 'parameters' => ['ratio' => 1.0], 'unit' => 'px', 'media' => ''],
    ]);
});

it("uses the default asset ratio if a default isn't provided", function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'lg:ratio' => 1.5,
    ]));

    expect(
        $responsive->breakPoints()->toArray()
    )->toEqual([
        ['asset' => $this->asset, 'label' => 'lg', 'value' => 1024, 'parameters' => ['ratio' => 1.5], 'unit' => 'px', 'media' => '(min-width: 1024px)'],
        ['asset' => $this->asset, 'label' => 'default', 'value' => 0, 'parameters' => ['ratio' => 1.2142857142857142], 'unit' => 'px', 'media' => ''],
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
        ['asset' => $this->asset, 'label' => 'lg', 'value' => 1024, 'parameters' => ['ratio' => 1.5, 'bla:ratio' => 2], 'unit' => 'px', 'media' => '(min-width: 1024px)'],
        ['asset' => $this->asset, 'label' => 'default', 'value' => 0, 'parameters' => ['bla:ratio' => 2], 'unit' => 'px', 'media' => ''],
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
        'value' => 0,
        'parameters' => [
            'ratio' => 1.2142857142857142,
        ],
        'unit' => 'px',
        'media' => ''
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

it("returns `null` for a non-existing breakpoint", function () {
    $responsive = new Responsive($this->asset, new Parameters([
        'lg:ratio' => 2 / 1,
    ]));

    expect($responsive->assetHeight('bla'))->toEqual(null);
});
