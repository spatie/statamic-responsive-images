<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Facades\Statamic\Imaging\GlideServer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\Responsive;
use Spatie\ResponsiveImages\Tests\TestCase;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Stache;
use Statamic\Fields\Value;
use Statamic\Tags\Parameters;

class ResponsiveTest extends TestCase
{
    /** @var \Statamic\Assets\Asset */
    private $asset;

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

        Stache::clear();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory(__DIR__ . '/tmp');
    }

    /** @test * */
    public function it_can_initialize_using_an_asset()
    {
        $responsive = new Responsive($this->asset, new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }

    /** @test * */
    public function it_can_initialize_using_the_assets_path()
    {
        $responsive = new Responsive($this->asset->resolvedPath(), new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }

    /** @test * */
    public function it_can_initialize_using_the_assets_url()
    {
        $responsive = new Responsive($this->asset->url(), new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }

    /** @test * */
    public function it_can_initialize_using_a_value()
    {
        $value = new Value($this->asset);

        $responsive = new Responsive($value, new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }

    /** @test * */
    public function it_can_initialize_using_a_collection_value()
    {
        $value = new Value(new Collection([$this->asset]));

        $responsive = new Responsive($value, new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }

    /** @test * */
    public function it_throws_if_it_cant_find_an_asset()
    {
        $this->expectException(AssetNotFoundException::class);

        new Responsive('doesnt-exist', new Parameters());
    }

    /** @test * */
    public function it_can_generate_a_set_of_breakpoints_for_an_asset()
    {
        $responsive = new Responsive($this->asset, new Parameters([
            'ratio' => 1,
            'lg:ratio' => 1.5,
        ]));

        $this->assertEquals([
            ['asset' => $this->asset, 'label' => 'lg', 'value' => 1024, 'unit' => 'px', 'media' => '(min-width: 1024px)', 'parameters' => ['ratio' => 1.5]],
            ['asset' => $this->asset, 'label' => 'default', 'value' => 0, 'unit' => 'px', 'media' => '', 'parameters' => ['ratio' => 1]],
        ], $responsive->breakPoints()->toArray());
    }

    /** @test * */
    public function it_can_parse_a_basic_fraction()
    {
        $responsive = new Responsive($this->asset, new Parameters([
            'ratio' => 1,
            'lg:ratio' => '1 / 2',
        ]));

        $this->assertEquals([
            ['asset' => $this->asset, 'label' => 'lg', 'value' => 1024, 'parameters' => ['ratio' => 1 / 2], 'unit' => 'px', 'media' => '(min-width: 1024px)'],
            ['asset' => $this->asset, 'label' => 'default', 'value' => 0, 'parameters' => ['ratio' => 1.0], 'unit' => 'px', 'media' => ''],
        ], $responsive->breakPoints()->toArray());
    }

    /** @test * */
    public function it_uses_the_default_asset_ratio_if_a_default_isnt_provided()
    {
        $responsive = new Responsive($this->asset, new Parameters([
            'lg:ratio' => 1.5,
        ]));

        $this->assertEquals([
            ['asset' => $this->asset, 'label' => 'lg', 'value' => 1024, 'parameters' => ['ratio' => 1.5], 'unit' => 'px', 'media' => '(min-width: 1024px)'],
            ['asset' => $this->asset, 'label' => 'default', 'value' => 0, 'parameters' => ['ratio' => 1.2142857142857142], 'unit' => 'px', 'media' => ''],
        ], $responsive->breakPoints()->toArray());
    }

    /** @test * */
    public function unknown_breakpoints_get_ignored()
    {
        $responsive = new Responsive($this->asset, new Parameters([
            'lg:ratio' => 1.5,
            'bla:ratio' => 2,
        ]));

        $this->assertEquals([
            ['asset' => $this->asset, 'label' => 'lg', 'value' => 1024, 'parameters' => ['ratio' => 1.5, 'bla:ratio' => 2], 'unit' => 'px', 'media' => '(min-width: 1024px)'],
            ['asset' => $this->asset, 'label' => 'default', 'value' => 0, 'parameters' => ['ratio' => 1.2142857142857142, 'bla:ratio' => 2], 'unit' => 'px', 'media' => ''],
        ], $responsive->breakPoints()->toArray());
    }

    /** @test * */
    public function it_can_retrieve_the_default_breakpoint()
    {
        $responsive = new Responsive($this->asset, new Parameters([
            'lg:ratio' => 1.5,
        ]));

        $this->assertEquals([
            'asset' => $this->asset,
            'label' => 'default',
            'value' => 0,
            'parameters' => [
                'ratio' => 1.2142857142857142,
            ],
            'unit' => 'px',
            'media' => ''
        ], $responsive->defaultBreakpoint()->toArray());
    }

    /** @test * */
    public function it_can_retrieve_the_height_of_an_image_for_a_ratio()
    {
        $responsive = new Responsive($this->asset, new Parameters());

        $this->assertEquals(280.0, $responsive->assetHeight());
    }

    /** @test * */
    public function it_can_retrieve_the_height_of_an_image_for_a_breakpoint_ratio()
    {
        $responsive = new Responsive($this->asset, new Parameters([
            'lg:ratio' => 2 / 1,
        ]));

        // Width = 340
        $this->assertEquals(170.0, $responsive->assetHeight('lg'));
    }

    /** @test * */
    public function it_returns_null_for_a_non_existent_breakpoint()
    {
        $responsive = new Responsive($this->asset, new Parameters([
            'lg:ratio' => 2 / 1,
        ]));

        $this->assertEquals(null, $responsive->assetHeight('bla'));
    }
}
