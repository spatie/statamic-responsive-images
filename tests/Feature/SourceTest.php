<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\Source;
use Statamic\Console\Commands\GlideClear;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Facades\Blink;

beforeEach(function () {
    Storage::disk('assets')->delete('*');
    $this->asset = $this->uploadTestImageToTestContainer();
    Stache::clear();
});

it('can build an image', function () {
    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);
    $source = new Source($breakpoint);

    expect(
        $source->buildImageJob(100)->handle()
    )->toContain('?q=90&fit=crop-50-50&w=100');
});

it('can build an image with parameters', function () {
    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);
    $source = new Source($breakpoint);

    expect(
        $source->buildImageJob(100, null,'webp')->handle()
    )->toContain('?fm=webp&q=90&fit=crop-50-50&w=100');
});

it("doesn't crash with a `null` ratio", function () {
    $breakpoint = new Breakpoint($this->asset, 'default', 0, [
        'ratio' => null,
    ]);

    $source = new Source($breakpoint);

    $source->getSrcSet();
})->expectNotToPerformAssertions();

it('does not generate image url with crop focus when auto crop is disabled', function () {
    config()->set('statamic.assets.auto_crop', false);

    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);

    $source = new Source($breakpoint);

    expect(
        $source->buildImageJob(100, 100, 'webp')->handle()
    )->toContain('?fm=webp&q=90&w=100&h=100',);
});

it('does not generate image url with crop focus when a `glide:fit` param is provided', function () {
    $breakpoint = new Breakpoint($this->asset, 'default', 0, ['glide:fit' => 'fill']);

    $source = new Source($breakpoint);

    expect(
        $source->buildImageJob(100, 100, 'webp')->handle()
    )->toContain('?fit=fill&fm=webp&q=90&w=100&h=100');
});

it('uses crop focus value from assets metadata', function () {
    $metaDataPath = $this->asset->metaPath();

    // Get original metadata that was generated when the asset was uploaded
    $metaData = YAML::file(
        Storage::disk('assets')->path($metaDataPath)
    )->parse();

    // Set some focus value
    $metaData['data'] = [
        'focus' => '29-71-3.6'
    ];

    // Dump the YAML data back into the metadata yaml file
    Storage::disk('assets')->put($metaDataPath, YAML::dump($metaData));

    // Flush the cache so Statamic is not using outdated metadata
    Cache::flush();

    // Fetch the asset from the container again, triggering metadata hydration
    $asset = $this->assetContainer->asset('test.jpg');

    $breakpoint = new Breakpoint($asset, 'default', 0, []);
    $source = new Source($breakpoint);

    expect(
        $source->buildImageJob(100)->handle()
    )->toContain('?q=90&fit=crop-29-71-3.6&w=100');
});

it('generates absolute url when using custom filesystem with custom url for glide cache', function () {
    config(['filesystems.disks.absolute_test' => [
        'driver' => 'local',
        'root' => __DIR__ . '/tmp',
        'url' => 'https://responsive.test/test',
    ]]);

    config([
        'statamic.assets.image_manipulation.cache' => 'absolute_test',
    ]);

    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);
    $source = new Source($breakpoint);

    expect(
        $source->buildImageJob(100)->handle()
    )->toStartWith('https://responsive.test/');
});

it('generates absolute url when force enabled through config', function () {
    config([
        'statamic.responsive-images.force_absolute_urls' => true,
    ]);

    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);
    $source = new Source($breakpoint);

    expect(
        $source->buildImageJob(100)->handle()
    )->toStartWith('http://localhost/');
});

it('generates relative url when absolute urls are disabled through config', function () {
    config([
        'statamic.responsive-images.force_absolute_urls' => false,
    ]);

    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);
    $source = new Source($breakpoint);

    expect(
        $source->buildImageJob(100)->handle()
    )->toStartWith('/img/asset/');
});

it('determines mimetype from pre-determined source formats', function () {
    config()->set('statamic.responsive-images.avif', true);
    config()->set('statamic.responsive-images.webp', true);

    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);

    $expectedMimeTypes = [
        'webp' => 'image/webp',
        'avif' => 'image/avif'
    ];

    $breakpoint->sources()->filter(function ($source) {
        return $source->getFormat() !== 'original';
    })->each(function ($source) use($expectedMimeTypes) {
        expect($source->getMimeType())->toBe($expectedMimeTypes[$source->getFormat()]);
    });
});

it('determines mimetype from asset for original source format', function () {
    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);

    $source = $breakpoint->sources()->first(function ($source) {
        return $source->getFormat() === 'original';
    });

    expect($source->getMimeType())->toBe('image/jpeg');
});