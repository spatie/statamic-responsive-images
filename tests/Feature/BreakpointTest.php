<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Breakpoint;
use Statamic\Console\Commands\GlideClear;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Facades\Blink;

beforeEach(function () {
    Storage::disk('assets')->delete('*');
    $this->asset = test()->uploadTestImageToTestContainer();
    Stache::clear();
});

it('generates placeholder data url when toggling cache form on to off', function () {
    /**
     * Clear regular cache and both Glide path cache storages
     * @see: https://statamic.dev/image-manipulation#path-cache-store
     */
    Config::set('statamic.assets.image_manipulation.cache', false);
    $this->artisan(GlideClear::class);
    Config::set('statamic.assets.image_manipulation.cache', true);
    $this->artisan(GlideClear::class);

    // Glide server has already initialized in service container, we clear it so the cache config value gets read.
    App::forgetInstance(\League\Glide\Server::class);

    $cacheDiskPathBefore = \Statamic\Facades\Glide::cacheDisk()->getConfig()['root'];

    // Generate placeholder
    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);
    $firstPlaceholder = $breakpoint->placeholderSrc();

    /**
     * We use Blink cache for placeholder generation that we need to clear just in case
     * @see https://statamic.dev/extending/blink-cache
     * @see Breakpoint::placeholder()
     */
    Blink::store()->flush();

    Config::set('statamic.assets.image_manipulation.cache', false);

    // Once again, because we are running in the same session, we need Glide server instance to be forgotten
    // so that it uses different Filesystem that depends on the statamic.assets.image_manipulation.cache value
    App::forgetInstance(\League\Glide\Server::class);

    $cacheDiskPathAfter = \Statamic\Facades\Glide::cacheDisk()->getConfig()['root'];

    // Generate placeholder again
    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);
    $secondPlaceholder = $breakpoint->placeholderSrc();

    expect($secondPlaceholder)->toEqual($firstPlaceholder)
        ->and($cacheDiskPathAfter)->not->toEqual($cacheDiskPathBefore);
});

it("doesn't crash when the placeholder image cannot be read", function () {
    $breakpoint = new Breakpoint($this->asset, 'default', 0, []);

    // Generate placeholder to trigger caching
    $breakpoint->placeholderSrc();

    // Forget cached files
    $pathPrefix = \Statamic\Imaging\ImageGenerator::assetCachePathPrefix($this->asset);

    \Statamic\Facades\Glide::server()->deleteCache($pathPrefix.'/'.$this->asset->path());

    Blink::store()->flush();

    // Generate new placeholder
    $breakpoint->placeholderSrc();
})->expectNotToPerformAssertions();