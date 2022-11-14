<?php

namespace Spatie\ResponsiveImages\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Assets\AssetContainer;
use Statamic\Console\Commands\GlideClear;
use Statamic\Extend\Manifest;
use Statamic\Facades\Asset;
use Statamic\Facades\Stache;
use Statamic\Statamic;

class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations;

    /** @var \Statamic\Assets\AssetContainer */
    protected $assetContainer;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTempTestFiles();

        $this->artisan(GlideClear::class);

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__ . '/tmp',
            'url' => '/test',
        ]]);

        config()->set('statamic.assets.image_manipulation.secure', false);

        /** @var \Statamic\Assets\AssetContainer $assetContainer */
        $this->assetContainer = (new AssetContainer)
            ->handle('test_container')
            ->disk('test')
            ->save();
    }

    protected function tearDown(): void
    {
        $this->assetContainer->delete();
        File::deleteDirectory(__DIR__ . '/tmp');
        Storage::disk('test')->delete('*');
        Asset::all()->each->delete();
        Stache::clear();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Rebing\GraphQL\GraphQLServiceProvider::class,
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

        $app['config']->set('statamic.assets.image_manipulation.driver', 'imagick');

        $app['config']->set('statamic.graphql.enabled', true);
        $app['config']->set('statamic.graphql.resources', [
            'collections' => true,
            'assets' => true,
        ]);
    }

    protected function setUpTempTestFiles()
    {
        $this->initializeDirectory($this->getTestFilesDirectory());
        File::copyDirectory(__DIR__ . '/TestSupport/testfiles', $this->getTestFilesDirectory());
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
        return __DIR__ . '/TestSupport/temp' . ($suffix == '' ? '' : '/' . $suffix);
    }

    public function getTestFilesDirectory($suffix = ''): string
    {
        return $this->getTempDirectory() . '/testfiles' . ($suffix == '' ? '' : '/' . $suffix);
    }

    public function getTestJpg(): string
    {
        return $this->getTestFilesDirectory('test.jpg');
    }

    public function getSmallTestJpg(): string
    {
        return $this->getTestFilesDirectory('smallTest.jpg');
    }

    public function getTestSvg(): string
    {
        return $this->getTestFilesDirectory('test.svg');
    }

    public function getZeroWidthTestSvg(): string
    {
        return $this->getTestFilesDirectory('zerowidth.svg');
    }

    public function getTestGif(): string
    {
        return $this->getTestFilesDirectory('hackerman.gif');
    }
}
