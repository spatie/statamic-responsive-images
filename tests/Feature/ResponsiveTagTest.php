<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Tags\ResponsiveTag;
use Spatie\ResponsiveImages\Tests\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Stache;

class ResponsiveTagTest extends TestCase
{
    use MatchesSnapshots;

    /** @var \Statamic\Assets\Asset */
    private $asset;

    /** @var \Statamic\Assets\Asset */
    private $asset2;

    /** @var \Statamic\Assets\Asset */
    private $svgAsset;

    /** @var \Statamic\Assets\Asset */
    private $gifAsset;

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

        $file = new UploadedFile($this->getTestJpg(), 'test2.jpg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->asset2 = $assetContainer->makeAsset($path)->upload($file);

        $svg = new UploadedFile($this->getTestSvg(), 'test.svg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->svgAsset = $assetContainer->makeAsset($path)->upload($svg);

        $gif = new UploadedFile($this->getTestGif(), 'hackerman.gif');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->gifAsset = $assetContainer->makeAsset($path)->upload($gif);

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
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset));
    }

    /** @test */
    public function it_generates_no_conversions_for_svgs()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->svgAsset));
    }

    /** @test */
    public function it_generates_no_conversions_for_gifs()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->gifAsset));
    }

    /** @test */
    public function it_returns_an_empty_string_if_the_asset_isnt_found()
    {
        $this->assertEquals('', ResponsiveTag::render('doesnt-exist'));
    }

    /** @test */
    public function it_generates_responsive_images_with_parameters()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'ratio' => 1,
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_with_breakpoint_parameters()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'ratio' => 1,
            'lg:ratio' => 1.5,
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_without_webp()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'webp' => false,
        ]));
    }

    /** @test */
    public function the_source_image_can_change_with_breakpoints()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'ratio' => 1,
            'lg:src' => $this->asset2->url(),
            'lg:ratio' => 1.5,
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_with_breakpoints_without_webp()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'webp' => false,
            'lg:ratio' => 1,
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_without_a_placeholder()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'placeholder' => false,
        ]));
    }

    /** @test */
    public function it_can_add_custom_glide_parameters()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'glide:blur' => 10,
        ]));
    }

    /** @test */
    public function it_adds_custom_parameters_to_the_attribute_string()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'alt' => 'Some alt tag',
        ]));
    }

    /** @test */
    public function a_glide_width_parameter_counts_as_max_width()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'glide:width' => '50',
        ]));
    }

    private function assertMatchesSnapshotWithoutSvg($value)
    {
        $value = preg_replace('/data:image\/svg\+xml(.*) 32w/', '', $value);
        $this->assertMatchesSnapshot($value);
    }
}
