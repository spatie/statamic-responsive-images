<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Responsive;
use Spatie\ResponsiveImages\Tests\TestCase;
use Statamic\Facades;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Statamic\Tags\Parameters;

class AssetReferenceTest extends TestCase
{
    /** @var \Statamic\Assets\Asset */
    private $asset;

    private $responsiveFieldConfiguration = [
        'type' => 'responsive',
        'container' => 'test_container',
        'max_files' => 1,
        'use_breakpoints' => false,
        'allow_ratio' => false,
        'allow_fit' => true,
        'restrict' => false,
        'allow_uploads' => true,
        'display' => 'Avatar',
        'icon' => 'assets',
        'listable' => 'hidden',
        'instructions_position' => 'above',
        'visibility' => 'visible',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::disk('test')->delete('*');
        Storage::disk('test')->makeDirectory('/folder1');
        Storage::disk('test')->makeDirectory('/folder2');

        $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->asset = $this->assetContainer->makeAsset($path)->upload($file);

        Stache::clear();
    }

    protected function setInBlueprints($namespace, $blueprintContents)
    {
        $blueprint = tap(Facades\Blueprint::make('set-in-blueprints')->setContents($blueprintContents))->save();

        Facades\Blueprint::shouldReceive('in')->with($namespace)->andReturn(collect([$blueprint]));
    }

    /** @test * */
    public function it_can_move_an_image()
    {
        $this->asset->move('folder1');

        $responsive = new Responsive($this->asset, new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }
    
    /** @test * */
    public function it_can_rename_an_image()
    {   
        $this->asset->rename('test1.jpg');

        $responsive = new Responsive($this->asset, new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }
    
    /** @test * */
    public function it_can_move_a_folder()
    {
        $this->assetContainer->assetFolder('folder1')->move('folder2');

        $responsive = new Responsive($this->asset, new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }
    
    /** @test * */
    public function it_can_rename_a_folder()
    {
        $this->assetContainer->assetFolder('folder1')->rename('folder3');

        $responsive = new Responsive($this->asset, new Parameters());

        $this->assertEquals($this->asset->id(), $responsive->asset->id());
    }

    /** @test */
    public function collection_item_responsive_asset_reference_gets_updated_after_asset_rename()
    {
        // Create collection
        $collection = tap(Facades\Collection::make('articles'))->save();

        $blueprintContents = [
            'fields' => [
                [
                    'handle' => 'avatar',
                    'field' => $this->responsiveFieldConfiguration,
                ]
            ],
        ];

        // Create blueprint for collection
        $this->setInBlueprints('collections/articles', $blueprintContents);

        // Create entry in the collection
        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'avatar' => [
                'src' => 'test_container::test.jpg'
            ],
        ]))->save();

        $this->assertEquals('test_container::test.jpg', Arr::get($entry->get('avatar'), 'src'));

        $this->asset->rename('new-test2');

        $this->assertEquals('test_container::new-test2.jpg', Arr::get($entry->fresh()->get('avatar'), 'src'));
    }
}
