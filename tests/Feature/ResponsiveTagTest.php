<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFieldtype;
use Spatie\ResponsiveImages\Tags\ResponsiveTag;
use Statamic\Facades\Stache;
use Statamic\Fields\Field;
use Statamic\Fields\Value;

use function PHPUnit\Framework\assertFileExists;
use function Spatie\Snapshots\assertMatchesSnapshot;

function assertMatchesSnapshotWithoutSvg($value)
{
    $value = preg_replace('/data:image\/svg\+xml(.*) 32w/', '', $value);

    assertMatchesSnapshot($value);
}

beforeEach(function () {
    $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
    $path = ltrim('/' . $file->getClientOriginalName(), '/');
    $this->asset = $this->assetContainer->makeAsset($path)->upload($file);

    $file2 = new UploadedFile($this->getTestJpg(), 'test2.jpg');
    $path = ltrim('/' . $file2->getClientOriginalName(), '/');
    $this->asset2 = $this->assetContainer->makeAsset($path)->upload($file2);

    $svg = new UploadedFile($this->getTestSvg(), 'test.svg');
    $path = ltrim('/' . $file->getClientOriginalName(), '/');
    $this->svgAsset = $this->assetContainer->makeAsset($path)->upload($svg);

    $gif = new UploadedFile($this->getTestGif(), 'hackerman.gif');
    $path = ltrim('/' . $file->getClientOriginalName(), '/');
    $this->gifAsset = $this->assetContainer->makeAsset($path)->upload($gif);

    Stache::clear();
});

it('generates responsive images')
    ->tap(fn () =>  assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset)));

it('generates no conversions for svgs')
    ->tap(fn () => assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->svgAsset)));

it('generates no conversions for gifs')
    ->tap(fn () => assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->gifAsset)));

it("returns an emprt string if the asset isn't found")
    ->expect(fn () => ResponsiveTag::render('doesnt-exist'))
    ->toEqual('');

it('generates responsive images with parameters', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'ratio' => 1,
    ]));
});

it('generates responsive images with breaking parameters', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'ratio' => 1,
        'lg:ratio' => 1.5,
    ]));
});

it('generates responsive images without webp', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'webp' => false,
    ]));
});

test('the source image can change with breakpoints', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'ratio' => 1,
        'lg:src' => $this->asset2->url(),
        'lg:ratio' => 1.5,
    ]));
});

it('generates responsive images with breaking points without webp', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'webp' => false,
        'lg:ratio' => 1,
    ]));
});

it('generates responsive images without a placeholder', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'placeholder' => false,
    ]));
});

it('can add custom glide parameters', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'glide:blur' => 10,
    ]));
});

it('adds custom parameters to the attribute string', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'alt' => 'Some alt tag',
    ]));
});

it('uses an alt field on the asset', function () {
    $this->asset->data(['alt' => 'My asset alt tag']);
    $this->asset->save();

    assertFileExists(__DIR__ . "/../tmp/.meta/{$this->asset->filename()}.jpg.yaml");

    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset->url()));
});

test('a glide width parameter counts as max width', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'glide:width' => '50',
    ]));
});

it('generates responsive images in webp and avig formats', function () {
    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'webp' => true,
        'avif' => true
    ]));

    config()->set('statamic.responsive-images.avif', true);
    config()->set('statamic.responsive-images.webp', true);

    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset));
});

test('quality value is used from glide parameter instead of per format quality parameter', function () {
    $tagOutput = ResponsiveTag::render($this->asset, [
        'glide:quality' => '55',
        'quality:webp' => '56',
        'webp' => true
    ]);

    expect($tagOutput)
        ->not->toContain('?q=56&amp;fm=webp')
        ->toContain('?q=55&amp;fm=webp');

    $tagOutput = ResponsiveTag::render($this->asset, [
        'glide:q' => '55',
        'quality:webp' => '56',
        'webp' => true
    ]);

    expect($tagOutput)
        ->not->toContain('?q=56&amp;fm=webp')
        ->toContain('?q=55&amp;fm=webp');
});

test('quality param value is used over quality config value', function () {
    config()->set('statamic.responsive-images.quality.webp', 66);

    $tagOutput = ResponsiveTag::render($this->asset, [
        'quality:webp' => '56',
        'webp' => true
    ]);

    expect($tagOutput)
        ->not->toContain('?fm=webp&amp;q=66')
        ->toContain('?fm=webp&amp;q=56');
});

test('no quality is set', function () {
    config()->set('statamic.responsive-images.quality', []);

    $tagOutput = ResponsiveTag::render($this->asset, [
        'webp' => true,
        'avif' => true
    ]);

    expect($tagOutput)
        ->not->toContain('?fm=webp&amp;q=')
        ->not->toContain('?fm=avif&amp;q=');
});

test('format quality is set on breakpoints', function () {
    config()->set('statamic.responsive-images.quality', []);

    assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
        'webp' => false,
        'avif' => false,
        'quality:jpg' => 30,
        'md:quality:jpg' => 50,
        'lg:quality:jpg' => 70
    ]));
});

it('can render a responsive image with the directive', function () {
    $blade = <<<'blade'
            @responsive($asset)
        blade;

    assertMatchesSnapshotWithoutSvg(Blade::render($blade, [
        'asset' => $this->asset
    ]));
});

it('can render an art directed image with the directive', function () {
    $blade = <<<'blade'
            @responsive($asset)
        blade;

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

    $asset = new Value([
        'src' => $this->asset->path(),
    ], 'image', $fieldtype);

    assertMatchesSnapshotWithoutSvg(Blade::render($blade, [
        'asset' => $asset
    ]));
});

it('can render an art directed image as array with the directive', function () {
    $blade = <<<'blade'
            @responsive($asset)
        blade;

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

    $asset = new Value([
        'src' => $this->asset->path(),
    ], 'image', $fieldtype);

    assertMatchesSnapshotWithoutSvg(Blade::render($blade, [
        'asset' => $asset->value(),
    ]));
});
