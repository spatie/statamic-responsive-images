<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Spatie\ResponsiveImages\Commands\GenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Jobs\GenerateGlideImageJob;
use Statamic\Facades\Stache;

beforeEach(function () {
    $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
    $path = ltrim('/' . $file->getClientOriginalName(), '/');
    $this->asset = $this->assetContainer->makeAsset($path)->upload($file);

    Stache::clear();
});

it('requires caching to be set')
    ->tap(fn () => config()->set('statamic.assets.image_manipulation.cache', false))
    ->artisan(GenerateResponsiveVersionsCommand::class)
    ->expectsOutput('Caching is not enabled for image manipulations, generating them will have no benefit.')
    ->assertExitCode(0);

it('dispatches jobs for all assets that are images', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', true);

    $this->artisan(GenerateResponsiveVersionsCommand::class)
        ->expectsOutput("Generating responsive image versions for 1 assets.")
        ->assertExitCode(0);

    Queue::assertPushed(GenerateGlideImageJob::class, 6);
});

it('dispatches less jobs when webp is disabled', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', true);
    config()->set('statamic.responsive-images.webp', false);

    $this->artisan(GenerateResponsiveVersionsCommand::class)
        ->expectsOutput("Generating responsive image versions for 1 assets.")
        ->assertExitCode(0);

    Queue::assertPushed(GenerateGlideImageJob::class, 3);
});

it('dispatches more jobs when avis and webp is enabled', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', true);
    config()->set('statamic.responsive-images.webp', true);
    config()->set('statamic.responsive-images.avif', true);

    $this->artisan(GenerateResponsiveVersionsCommand::class)
        ->expectsOutput("Generating responsive image versions for 1 assets.")
        ->assertExitCode(0);

    Queue::assertPushed(GenerateGlideImageJob::class, 9);
});
