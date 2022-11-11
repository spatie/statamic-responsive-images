<?php

use Illuminate\Http\UploadedFile;

use function Spatie\Snapshots\assertMatchesJsonSnapshot;

function assertMatchesJsonSnapshotWithoutSvg($value)
{
    $value = preg_replace('/data:image(.*?) 32w, /', '', $value);
    assertMatchesJsonSnapshot($value);
}

beforeEach(function () {
    $file = new UploadedFile($this->getTestJpg(), 'graphql.jpg');
    $path = ltrim('/' . $file->getClientOriginalName(), '/');
    $this->asset = $this->assetContainer->makeAsset($path)->upload($file);
});

test('graphql asset outputs avif srcset when enabled through config', function () {
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
    assertMatchesJsonSnapshotWithoutSvg($response->getContent());
});

test('graphql asset outs avif srcset when enabled through arguments', function () {
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
    assertMatchesJsonSnapshotWithoutSvg($response->getContent());
});
