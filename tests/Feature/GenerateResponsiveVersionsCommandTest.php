<?php

use Illuminate\Support\Facades\Queue;
use Spatie\ResponsiveImages\Commands\GenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Jobs\GenerateGlideImageJob;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->asset = $this->uploadTestImageToTestContainer();
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

    $pushedJobs = Queue::pushed(GenerateGlideImageJob::class);

    $noFormatJobCount = $pushedJobs->filter(function ($job) {
        return !isset($job->getParams()['fm']);
    })->count();

    $webpFormatJobCount = $pushedJobs->filter(function ($job) {
        return isset($job->getParams()['fm']) && $job->getParams()['fm'] === 'webp';
    })->count();

    expect($noFormatJobCount)->toBe(3);
    expect($webpFormatJobCount)->toBe(3);
    Queue::assertPushed(GenerateGlideImageJob::class, 6);
});

it('dispatches less jobs when webp is disabled', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', true);
    config()->set('statamic.responsive-images.webp', false);

    $this->artisan(GenerateResponsiveVersionsCommand::class)
        ->expectsOutput("Generating responsive image versions for 1 assets.")
        ->assertExitCode(0);

    $pushedJobs = Queue::pushed(GenerateGlideImageJob::class);

    $noFormatJobCount = $pushedJobs->filter(function ($job) {
        return !isset($job->getParams()['fm']);
    })->count();

    $webpFormatJobCount = $pushedJobs->filter(function ($job) {
        return isset($job->getParams()['fm']) && $job->getParams()['fm'] === 'webp';
    })->count();

    expect($noFormatJobCount)->toBe(3);
    expect($webpFormatJobCount)->toBe(0);
    Queue::assertPushed(GenerateGlideImageJob::class, 3);
});

it('dispatches more jobs when avif and webp is enabled', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', true);
    config()->set('statamic.responsive-images.webp', true);
    config()->set('statamic.responsive-images.avif', true);

    $this->artisan(GenerateResponsiveVersionsCommand::class)
        ->expectsOutput("Generating responsive image versions for 1 assets.")
        ->assertExitCode(0);

    $pushedJobs = Queue::pushed(GenerateGlideImageJob::class);

    $noFormatJobCount = $pushedJobs->filter(function ($job) {
        return !isset($job->getParams()['fm']);
    })->count();

    $webpFormatJobCount = $pushedJobs->filter(function ($job) {
        return isset($job->getParams()['fm']) && $job->getParams()['fm'] === 'webp';
    })->count();

    $avifFormatJobCount = $pushedJobs->filter(function ($job) {
        return isset($job->getParams()['fm']) && $job->getParams()['fm'] === 'avif';
    })->count();

    expect($noFormatJobCount)->toBe(3);
    expect($webpFormatJobCount)->toBe(3);
    expect($avifFormatJobCount)->toBe(3);
    Queue::assertPushed(GenerateGlideImageJob::class, 9);
});

it('can skip excluded containers', function () {
    Queue::fake();

    config()->set('statamic.assets.image_manipulation.cache', true);
    config()->set('statamic.responsive-images.excluded_containers', ['test_container']);

    $this
        ->artisan(GenerateResponsiveVersionsCommand::class)
        ->expectsOutput("Generating responsive image versions for 0 assets.")
        ->assertExitCode(0);

    Queue::assertNotPushed(GenerateGlideImageJob::class);
});
