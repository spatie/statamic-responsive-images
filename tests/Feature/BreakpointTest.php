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
use Spatie\ResponsiveImages\Breakpoint;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Stache;
use Statamic\Fields\Value;
use Statamic\Tags\Parameters;

class BreakpointTest extends TestCase
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
    public function it_can_build_an_image()
    {
        $responsive = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringContainsString(
            '?w=100',
            $responsive->buildImage(100)
        );
    }

    /** @test * */
    public function it_can_build_an_image_with_parameters()
    {
        $responsive = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringContainsString(
            '?fm=webp&w=100',
            $responsive->buildImage(100, ['fm' => 'webp'])
        );
    }

    /** @test * */
    public function it_can_build_an_image_with_a_ratio()
    {
        $responsive = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringContainsString(
            '?fm=webp&w=100&h=100',
            $responsive->buildImage(100, ['fm' => 'webp'], 1.0)
        );
    }
}
