<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFieldtype;
use Spatie\ResponsiveImages\Tags\ResponsiveTag;
use Spatie\ResponsiveImages\Tests\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Statamic\Facades\Asset;
use Statamic\Facades\Stache;
use Statamic\Fields\Field;
use Statamic\Fields\Value;

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

        $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->asset = $this->assetContainer->makeAsset($path)->upload($file);

        $file2 = new UploadedFile($this->getTestJpg(), 'test2.jpg');
        $path = ltrim('/'.$file2->getClientOriginalName(), '/');
        $this->asset2 = $this->assetContainer->makeAsset($path)->upload($file2);

        $svg = new UploadedFile($this->getTestSvg(), 'test.svg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->svgAsset = $this->assetContainer->makeAsset($path)->upload($svg);

        $gif = new UploadedFile($this->getTestGif(), 'hackerman.gif');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->gifAsset = $this->assetContainer->makeAsset($path)->upload($gif);

        Stache::clear();
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
    public function it_uses_an_alt_field_on_the_asset()
    {
        $this->asset->data(['alt' => 'My asset alt tag']);
        $this->asset->save();

        $this->assertFileExists(__DIR__ . "/../tmp/.meta/{$this->asset->filename()}.jpg.yaml");

        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset->url()));
    }

    /** @test */
    public function a_glide_width_parameter_counts_as_max_width()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'glide:width' => '50',
        ]));
    }

    /** @test */
    public function it_generates_responsive_images_in_webp_and_avif_formats()
    {
        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'webp' => true,
            'avif' => true
        ]));

        config()->set('statamic.responsive-images.avif', true);
        config()->set('statamic.responsive-images.webp', true);

        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset));
    }

    /** @test */
    public function quality_value_is_used_from_glide_parameter_instead_of_per_format_quality_parameter()
    {
        $tagOutput = ResponsiveTag::render($this->asset, [
            'glide:quality' => '55',
            'quality:webp' => '56',
            'webp' => true
        ]);

        $this->assertStringNotContainsString('?q=56&amp;fm=webp', $tagOutput);
        $this->assertStringContainsString('?q=55&amp;fm=webp', $tagOutput);

        $tagOutput = ResponsiveTag::render($this->asset, [
            'glide:q' => '55',
            'quality:webp' => '56',
            'webp' => true
        ]);

        $this->assertStringNotContainsString('?q=56&amp;fm=webp', $tagOutput);
        $this->assertStringContainsString('?q=55&amp;fm=webp', $tagOutput);
    }

    /** @test */
    public function quality_param_value_is_used_over_quality_config_value()
    {
        config()->set('statamic.responsive-images.quality.webp', 66);

        $tagOutput = ResponsiveTag::render($this->asset, [
            'quality:webp' => '56',
            'webp' => true
        ]);

        $this->assertStringNotContainsString('?fm=webp&amp;q=66', $tagOutput);
        $this->assertStringContainsString('?fm=webp&amp;q=56', $tagOutput);
    }

    /** @test */
    public function no_quality_parameter_is_set()
    {
        config()->set('statamic.responsive-images.quality', []);

        $tagOutput = ResponsiveTag::render($this->asset, [
            'webp' => true,
            'avif' => true
        ]);

        $this->assertStringNotContainsString('?fm=webp&amp;q=', $tagOutput);
        $this->assertStringNotContainsString('?fm=avif&amp;q=', $tagOutput);
    }

    /** @test */
    public function format_quality_is_set_on_breakpoints()
    {
        config()->set('statamic.responsive-images.quality', []);

        $this->assertMatchesSnapshotWithoutSvg(ResponsiveTag::render($this->asset, [
            'webp' => false,
            'avif' => false,
            'quality:jpg' => 30,
            'md:quality:jpg' => 50,
            'lg:quality:jpg' => 70
        ]));
    }

    /** @test * */
    public function it_can_render_a_responsive_image_with_the_directive()
    {
        $blade = <<<'blade'
            @responsive($asset)
        blade;

        $this->assertMatchesSnapshotWithoutSvg(Blade::render($blade, [
            'asset' => $this->asset
        ]));
    }

    /** @test * */
    public function it_can_render_an_art_directed_image_with_the_directive()
    {
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

        $this->assertMatchesSnapshotWithoutSvg(Blade::render($blade, [
            'asset' => $asset
        ]));
    }

    /** @test * */
    public function it_can_render_an_art_directed_image_as_array_with_the_directive()
    {
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

        $this->assertMatchesSnapshotWithoutSvg(Blade::render($blade, [
            'asset' => $asset->value(),
        ]));
    }

    private function assertMatchesSnapshotWithoutSvg($value)
    {
        $value = preg_replace('/data:image\/svg\+xml(.*) 32w/', '', $value);
        $this->assertMatchesSnapshot($value);
    }
}
