<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Spatie\ResponsiveImages\Tests\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Statamic\Assets\Asset;

class GraphQLTest extends TestCase
{
    use MatchesSnapshots;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $file = new UploadedFile($this->getTestJpg(), 'graphql.jpg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->asset = $this->assetContainer->makeAsset($path)->upload($file);
    }

    private function assertMatchesJsonSnapshotWithoutSvg($value)
    {
        $value = preg_replace('/data:image(.*?) 32w, /', '', $value);
        $this->assertMatchesJsonSnapshot($value);
    }

    /** @test */
    public function graphql_asset_outputs_avif_srcset_when_enabled_through_config()
    {
        config()->set('statamic.responsive-images.avif', true);

        $query = '
            {
                asset(id: "test_container::graphql.jpg") {
                    responsive {
                      	srcSetAvif
                    }
                }
            }
        ';

        $response = $this->post('/graphql/', ['query' => $query]);
        $this->assertMatchesJsonSnapshotWithoutSvg($response->getContent());
    }

    /** @test */
    public function graphql_asset_outputs_avif_srcset_when_enabled_through_arguments()
    {
        config()->set('statamic.responsive-images.avif', false);

        $query = '
            {
                asset(id: "test_container::graphql.jpg") {
                    responsive(avif: true) {
                      	srcSetAvif
                    }
                }
            }
        ';

        $response = $this->post('/graphql/', ['query' => $query]);
        $this->assertMatchesJsonSnapshotWithoutSvg($response->getContent());
    }
}
