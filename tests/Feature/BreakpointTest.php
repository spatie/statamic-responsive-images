<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Tests\TestCase;
use Spatie\ResponsiveImages\Breakpoint;
use Statamic\Facades\Stache;

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
            '?w=100',
            $responsive->buildImageJob(100)->handle()
        );
    }

    /** @test * */
    public function it_can_build_an_image_with_parameters()
    {
        $responsive = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringContainsString(
            '?fm=webp&w=100',
            $responsive->buildImageJob(100, 'webp')->handle()
        );
    }

    /** @test * */
    public function it_can_build_an_image_with_a_ratio()
    {
        $responsive = new Breakpoint($this->asset, 'default', 0, []);

        $this->assertStringContainsString(
            '?fm=webp&w=100&h=100',
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
}
