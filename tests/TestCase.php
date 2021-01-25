<?php

namespace Spatie\ResponsiveImages\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Statamic;

class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations;
    use WithFaker;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = $this->makeFaker();

        $this->setUpTempTestFiles();

        config()->set('statamic.assets.image_manipulation.driver', 'imagick');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Statamic\Providers\StatamicServiceProvider::class,
            \Wilderborn\Partyline\ServiceProvider::class,
            \Spatie\ResponsiveImages\ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'spatie/statamic-responsive-images' => [
                'id' => 'spatie/statamic-responsive-images',
                'namespace' => 'Spatie\\ResponsiveImages\\',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'routes', 'static_caching',
            'sites', 'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__ . "/../vendor/statamic/cms/config/{$config}.php"));
        }

        // Setting the user repository to the default flat file system
        $app['config']->set('statamic.users.repository', 'file');

        // Assume the pro edition within tests
        $app['config']->set('statamic.editions.pro', true);

        // Define config settings for all of our tests
        $app['config']->set("statamic.responsive-images", require(__DIR__ . "/../config/responsive-images.php"));
    }

    protected function setUpTempTestFiles()
    {
        $this->initializeDirectory($this->getTestFilesDirectory());
        File::copyDirectory(__DIR__.'/TestSupport/testfiles', $this->getTestFilesDirectory());
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }

        File::makeDirectory($directory, 0755, true);
    }

    public function getTempDirectory($suffix = ''): string
    {
        return __DIR__.'/TestSupport/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getTestFilesDirectory($suffix = ''): string
    {
        return $this->getTempDirectory().'/testfiles'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getTestJpg(): string
    {
        return $this->getTestFilesDirectory('test.jpg');
    }

    public function getSmallTestJpg(): string
    {
        return $this->getTestFilesDirectory('smallTest.jpg');
    }
}
