<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Facades\Statamic\Imaging\GlideServer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\Responsive;
use Spatie\ResponsiveImages\ResponsiveTag;
use Spatie\ResponsiveImages\Tests\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Parse;
use Statamic\Facades\Stache;
use Statamic\Fields\Value;

class ResponsiveTagTest extends TestCase
{
    use MatchesSnapshots;

    /** @var \Statamic\Assets\Asset */
    private $asset;

    /** @var \Statamic\Assets\Asset */
    private $svgAsset;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
            'url' => '/test',
        ]]);

        /** @var \Statamic\Assets\AssetContainer $assetContainer */
        $assetContainer = (new AssetContainer)
            ->handle('test_container')
            ->disk('test')
            ->save();

        Storage::disk('test')->delete('*');

        $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->asset = $assetContainer->makeAsset($path)->upload($file);

        $svg = new UploadedFile($this->getTestJpg(), 'test.svg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->svgAsset = $assetContainer->makeAsset($path)->upload($svg);

        Stache::clear();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory(__DIR__ . '/tmp');
    }

    /** @test */
    public function it_generates_responsive_images()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset));
    }

    /** @test */
    public function it_generates_no_conversions_for_svgs()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->svgAsset));
    }

    /** @test */
    public function it_returns_an_empty_string_if_the_asset_isnt_found()
    {
        $this->assertEquals('', ResponsiveTag::render('doesnt-exist'));
    }

    /** @test */
    public function it_generates_responsive_images_with_parameters()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset, [
            'ratio' => 1,
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_with_breakpoint_parameters()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset, [
            'ratio' => 1,
            'lg:ratio' => 1.5,
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_without_webp()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset, [
            'webp' => false,
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_with_breakpoints_without_webp()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset, [
            'webp' => false,
            'lg:ratio' => 1,
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_without_a_placeholder()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset, [
            'placeholder' => false,
        ]));
    }

    /** @test */
    public function it_can_add_custom_glide_parameters()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset, [
            'glide:blur' => 10,
        ]));
    }

    /** @test */
    public function it_adds_custom_parameters_to_the_attribute_string()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset, [
            'alt' => 'Some alt tag',
        ]));
    }

    /** @test */
    public function a_glide_width_parameter_counts_as_max_width()
    {
        $this->assertMatchesSnapshot(ResponsiveTag::render($this->asset, [
            'glide:width' => '50',
        ]));
    }
}
