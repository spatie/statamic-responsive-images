<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Tests\TestCase;
use Spatie\ResponsiveImages\Breakpoint;
use Statamic\Console\Commands\GlideClear;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Facades\Blink;

class BreakpointTest extends TestCase
{
    /** @var \Statamic\Assets\Asset */
    private $asset;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::disk('test')->delete('*');

        $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->asset = $this->assetContainer->makeAsset($path)->upload($file);
        Stache::clear();
    }

    /** @test * */
    public function it_can_build_an_image()
    {
        $responsive = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringContainsString(
            '?q=90&fit=crop-50-50&w=100',
            $responsive->buildImageJob(100)->handle()
        );
    }

    /** @test * */
    public function it_can_build_an_image_with_parameters()
    {
        $responsive = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringContainsString(
            '?fm=webp&q=90&fit=crop-50-50&w=100',
            $responsive->buildImageJob(100, 'webp')->handle()
        );
    }

    /** @test * */
    public function it_can_build_an_image_with_a_ratio()
    {
        $responsive = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringContainsString(
            '?fm=webp&q=90&fit=crop-50-50&w=100&h=100',
            $responsive->buildImageJob(100, 'webp', 1.0)->handle()
        );
    }

    /** @test * */
    public function it_doesnt_crash_with_a_null_ratio()
    {
        $this->expectNotToPerformAssertions();

        $breakpoint = new Breakpoint($this->asset, 'default', 0, [
            'ratio' => null,
        ]);

        $breakpoint->getSrcSet();
    }

    /** @test * */
    public function it_does_not_generate_image_url_with_crop_focus_when_auto_crop_is_disabled()
    {
        config()->set('statamic.assets.auto_crop', false);

        $breakpoint = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringEndsWith(
            '?fm=webp&q=90&w=100&h=100',
            $breakpoint->buildImageJob(100, 'webp', 1.0)->handle()
        );
    }

    /** @test * */
    public function it_does_not_generate_image_url_with_crop_focus_when_a_glide_fit_param_is_provided()
    {
        $breakpoint = new Breakpoint($this->asset, 'default', 0, ['glide:fit' => 'fill']);

        $this->assertStringEndsWith(
            '?fit=fill&fm=webp&q=90&w=100&h=100',
            $breakpoint->buildImageJob(100, 'webp', 1.0)->handle()
        );
    }

    /** @test * */
    public function it_uses_crop_focus_value_from_assets_metadata()
    {
        $metaDataPath = $this->asset->metaPath();

        // Get original metadata that was generated when the asset was uploaded
        $metaData = YAML::file(
            Storage::disk('test')->path($metaDataPath)
        )->parse();

        // Set some focus value
        $metaData['data'] = [
            'focus' => '29-71-3.6'
        ];

        // Dump the YAML data back into the metadata yaml file
        Storage::disk('test')->put($metaDataPath, YAML::dump($metaData));

        // Flush the cache so Statamic is not using outdated metadata
        Cache::flush();

        // Fetch the asset from the container again, triggering metadata hydration
        $asset = $this->assetContainer->asset('test.jpg');

        $breakpoint = new Breakpoint($asset, 'default', 0, []);

        $this->assertStringEndsWith(
            '?q=90&fit=crop-29-71-3.6&w=100',
            $breakpoint->buildImageJob(100 )->handle()
        );
    }

    /**
     * @test
     */
    public function it_generates_placeholder_data_url_when_toggling_cache_from_on_to_off()
    {
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
        $responsive = new Breakpoint($this->asset, 'default', 0, []);
        $firstPlaceholder = $responsive->placeholder();

        /**
         * We use Blink cache for placeholder generation that we need to clear just in case
         * @see https://statamic.dev/extending/blink-cache
         * @see Breakpoint::placeholderSvg()
         */
        Blink::store()->flush();

        Config::set('statamic.assets.image_manipulation.cache', false);

        // Once again, because we are running in the same session, we need Glide server instance to be forgotten
        // so that it uses different Filesystem that depends on the statamic.assets.image_manipulation.cache value
        App::forgetInstance(\League\Glide\Server::class);

        $cacheDiskPathAfter = \Statamic\Facades\Glide::cacheDisk()->getConfig()['root'];

        // Generate placeholder again
        $responsive = new Breakpoint($this->asset, 'default', 0, []);
        $secondPlaceholder = $responsive->placeholder();

        $this->assertEquals($firstPlaceholder, $secondPlaceholder);
        $this->assertNotEquals($cacheDiskPathBefore, $cacheDiskPathAfter);
    }
}
