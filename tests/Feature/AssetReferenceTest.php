<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Tests\TestCase;
use Statamic\Facades\Stache;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Tags\Parameters;
use Spatie\ResponsiveImages\Exceptions\InvalidAssetException;

class AssetReferenceTest extends TestCase
{
    /** @var \Statamic\Assets\Asset */
    private $asset;

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
    
}
