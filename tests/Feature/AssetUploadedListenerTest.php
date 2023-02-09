<?php

use Illuminate\Support\Facades\Queue;
use Spatie\ResponsiveImages\Jobs\GenerateGlideImageJob;

test('generate image jobs are dispatched when asset is uploaded', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', true);

    $this->asset = test()->uploadTestImageToTestContainer();

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

test('jobs are not generated when image manipulation cache is disabled', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', false);

    $this->asset = test()->uploadTestImageToTestContainer();

    Queue::assertPushed(GenerateGlideImageJob::class, 0);
});

test('jobs are not generated when generate on upload is disabled', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', true);
    config()->set('statamic.responsive-images.generate_on_upload', false);

    $this->asset = test()->uploadTestImageToTestContainer();

    Queue::assertPushed(GenerateGlideImageJob::class, 0);
});

test('jobs are not generated when uploading svg asset', function () {
    Queue::fake();
    config()->set('statamic.assets.image_manipulation.cache', true);

    $this->asset = test()->uploadTestImageToTestContainer(test()->getTestSvg(), 'test.svg');

    Queue::assertPushed(GenerateGlideImageJob::class, 0);
});