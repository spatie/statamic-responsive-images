<?php

namespace Spatie\ResponsiveImages\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Statamic\Console\RunsInPlease;

class RegenerateResponsiveVersionsCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:responsive:regenerate';

    protected $description = 'Regenerate responsive images';

    public function handle(): void
    {
        if (! config('statamic.assets.image_manipulation.cache')) {
            $this->error('Caching is not enabled for image manipulations, generating them will have no benefit.');

            return;
        }

        $this->info('Clearing Glide cache...');

        Artisan::call('statamic:glide:clear');
        Artisan::call('statamic:responsive:generate');
    }
}
