<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Commands\GenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Jobs\GenerateGlideImageJob;
use Spatie\ResponsiveImages\Tests\TestCase;
use Statamic\Facades\Asset;
use Statamic\Facades\Stache;

class GenerateResponsiveVersionsCommandTest extends TestCase
{
    /** @var \Statamic\Assets\Asset */
    private $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->asset = $this->assetContainer->makeAsset($path)->upload($file);

        Stache::clear();
    }

    /** @test * */
    public function it_requires_caching_to_be_set()
    {
        config()->set('statamic.assets.image_manipulation.cache', false);

        $this->artisan(GenerateResponsiveVersionsCommand::class)
            ->expectsOutput('Caching is not enabled for image manipulations, generating them will have no benefit.')
            ->assertExitCode(0);
    }

    /** @test * */
    public function it_dispatches_jobs_for_all_assets_that_are_images()
    {
        Queue::fake();
        config()->set('statamic.assets.image_manipulation.cache', true);

        $this->artisan(GenerateResponsiveVersionsCommand::class)
            ->expectsOutput("Generating responsive image versions for 1 assets.")
            ->assertExitCode(0);

        Queue::assertPushed(GenerateGlideImageJob::class, 6);
    }

    /** @test * */
    public function it_dispatches_less_jobs_when_webp_is_disabled()
    {
        Queue::fake();
        config()->set('statamic.assets.image_manipulation.cache', true);
        config()->set('statamic.responsive-images.webp', false);

        $this->artisan(GenerateResponsiveVersionsCommand::class)
            ->expectsOutput("Generating responsive image versions for 1 assets.")
            ->assertExitCode(0);

        Queue::assertPushed(GenerateGlideImageJob::class, 3);
    }

    /** @test * */
    public function it_dispatches_more_jobs_when_avif_and_webp_is_enabled()
    {
        Queue::fake();
        config()->set('statamic.assets.image_manipulation.cache', true);
        config()->set('statamic.responsive-images.webp', true);
        config()->set('statamic.responsive-images.avif', true);

        $this->artisan(GenerateResponsiveVersionsCommand::class)
            ->expectsOutput("Generating responsive image versions for 1 assets.")
            ->assertExitCode(0);

        Queue::assertPushed(GenerateGlideImageJob::class, 9);
    }
}
