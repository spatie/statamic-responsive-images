<?php

namespace Spatie\ResponsiveImages\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Assets\AssetContainer;
use Statamic\Console\Commands\GlideClear;
use Statamic\Extend\Manifest;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Statamic;

class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations;

    /** @var \Statamic\Assets\AssetContainer */
    public $assetContainer;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clean up from old tests
        File::deleteDirectory($this->getTempDirectory());

        $this->setUpTempTestFiles();

        $this->artisan(GlideClear::class);

        config(['filesystems.disks.assets' => [
            'driver' => 'local',
            'root' => $this->getTempDirectory('assets'),
            'url' => '/test',
        ]]);

        config()->set('statamic.assets.image_manipulation.secure', false);

        /** @var \Statamic\Assets\AssetContainer $assetContainer */
        $this->assetContainer = (new AssetContainer)
            ->handle('test_container')
            ->disk('assets')
            ->save();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->getTempDirectory());
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
                'namespace' => 'Spatie\\ResponsiveImages',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets',
            'cp',
            'forms',
            'routes',
            'static_caching',
            // 'sites',
            'stache',
            'system',
            'users',
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
        $app['config']->set('statamic.graphql.cache', false);
        $app['config']->set('statamic.graphql.resources', [
            'collections' => true,
            'assets' => true,
        ]);

        $app['config']->set('statamic.stache.stores.collections.directory', $this->getTempDirectory('/content/collections'));
        $app['config']->set('statamic.stache.stores.entries.directory', $this->getTempDirectory('/content/collections'));
        $app['config']->set('statamic.stache.stores.asset-containers.directory', $this->getTempDirectory( '/content/assets'));

        Statamic::booted(function () {
            Blueprint::setDirectory($this->getTempDirectory('/resources/blueprints'));
        });
    }

    protected function setUpTempTestFiles()
    {
        $this->initializeDirectory(__DIR__ . '/TestSupport/tmp');
        $this->initializeDirectory($this->getTestFilesDirectory());
        File::copyDirectory(__DIR__ . '/TestSupport/TestFiles', $this->getTestFilesDirectory());
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
        return __DIR__ . '/TestSupport/tmp' . ($suffix == '' ? '' : '/' . $suffix);
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

    public function setInBlueprints($namespace, $blueprintContents): void
    {
        $blueprint = tap(Blueprint::make('set-in-blueprints')->setContents($blueprintContents))->save();

        Blueprint::shouldReceive('in')->with($namespace)->andReturn(collect([$blueprint]));
    }

    public function createDummyCollectionEntry($blueprintConfiguration, $entryData)
    {
        // Create collection
        $collection = tap(Collection::make('articles'))->save();

        $blueprintContents = $blueprintConfiguration;

        // Create blueprint for collection
        $this->setInBlueprints('collections/articles', $blueprintContents);

        // Create entry in the collection
        return tap(Entry::make()->collection($collection)->data($entryData))->save();
    }

    public function uploadTestImageToTestContainer(?string $testImagePath = null, ?string $filename = 'test.jpg')
    {
        if ($testImagePath === null) {
            $testImagePath = test()->getTestJpg();
        }

        // Duplicate file because in Statamic 3.4 the source asset is deleted after upload
        $duplicateImagePath = preg_replace('/(\.[^.]+)$/', '-' . Carbon::now()->timestamp . '$1', $testImagePath);
        File::copy($testImagePath, $duplicateImagePath);

        $file = new UploadedFile($duplicateImagePath, $filename);
        $path = ltrim('/' . $file->getClientOriginalName(), '/');
        return $this->assetContainer->makeAsset($path)->upload($file);
    }
}
