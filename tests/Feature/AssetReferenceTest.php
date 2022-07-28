<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponsiveImages\Tests\TestCase;
use Statamic\Facades\Stache;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Tags\Parameters;

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
        $asset->move('folder1');

        $this->expectException(InvalidAssetException::class);

        new Responsive($asset, new Parameters());
    }
    
    /** @test * */
    public function it_can_rename_an_image()
    {   
        $asset->rename('test1.jpg');
        
        $this->expectException(InvalidAssetException::class);

        new Responsive($asset, new Parameters());
    }
    
    /** @test * */
    public function it_can_move_a_folder()
    {
        $this->assetContainer->assetFolder('folder1')->move('folder2');

        $this->expectException(InvalidAssetException::class);

        new Responsive($asset, new Parameters());
    }
    
    /** @test * */
    public function it_can_rename_a_folder()
    {
        $this->assetContainer->assetFolder('folder1')->rename('folder3');

        $this->expectException(InvalidAssetException::class);

        new Responsive($asset, new Parameters());
    }
    
}
