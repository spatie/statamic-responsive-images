<?php

use function Spatie\Snapshots\assertMatchesJsonSnapshot;

function assertMatchesJsonSnapshotWithoutSvg($value)
{
    $value = preg_replace('/data:image(.*?) 32w, /', '', $value);
    assertMatchesJsonSnapshot($value);
}

beforeEach(function () {
    $this->uploadTestImageToTestContainer();
});

test('responsive field returns data', function () {
    $query = '
        {
            asset(id: "test_container::test.jpg") {
                responsive(webp: false, placeholder: false) {
           			asset {
                        id
                    }
                    label
                    minWidth
                    widthUnit
                    ratio
                    sources {
                        format
                        mimeType
                        minWidth
                        mediaString
                        mediaString
                        srcSet
                    }
                }
            }
        }
    ';

    $response = $this
        ->postJson('/graphql/', ['query' => $query]);

    $contents = $response->getContent();

    assertMatchesJsonSnapshot($contents);
});

test('responsive field returns multiple breakpoints when specifying breakpoint ratios', function () {
    $query = '
        {
            asset(id: "test_container::test.jpg") {
                responsive(webp: false, placeholder: false, md_ratio: 1.5, lg_ratio: 2) {
                    label
                    minWidth
                }
            }
        }
    ';

    $response = $this
        ->postJson('/graphql/', ['query' => $query]);

    $contents = $response->getContent();

    expect(json_decode($contents, true)['data']['asset']['responsive'])->toHaveCount(3);
});


test('querying ResponsiveFieldType field resolves it to data', function () {
    // TODO: Having some issues with Responsive fieldtype being registered.
    // TODO: Streamline collection, it's blueprint, and entry creation
});

it('outputs avif sources when enabled through config', function () {
    config()->set('statamic.responsive-images.avif', true);

    $query = '
            {
                asset(id: "test_container::test.jpg") {
                    responsive {
                        sources {
                            format
                        }
                    }
                }
            }
        ';

    $response = $this
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $sources = json_decode($response, true)['data']['asset']['responsive'][0]['sources'];

    expect(collect($sources)->where('format', 'avif')->count())->toBe(1);
    expect(collect($sources)->where('format', 'webp')->count())->toBe(1);
    expect(collect($sources)->where('format', 'original')->count())->toBe(1);
});

it('outputs avif sources when enabled through arguments but disabled through config', function () {
    config()->set('statamic.responsive-images.avif', false);

    $query = '
            {
                asset(id: "test_container::test.jpg") {
                    responsive(avif: true) {
                        sources {
                            format
                        }
                    }
                }
            }
        ';

    $response = $this
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $sources = json_decode($response, true)['data']['asset']['responsive'][0]['sources'];

    expect(collect($sources)->where('format', 'avif')->count())->toBe(1);
    expect(collect($sources)->where('format', 'webp')->count())->toBe(1);
    expect(collect($sources)->where('format', 'original')->count())->toBe(1);
});

test('ratio is outputted if using ResponsiveDimensionCalculator', function () {
    $query = '
            {
                asset(id: "test_container::test.jpg") {
                    responsive {
                        ratio
                    }
                }
            }
        ';

    $response = $this->post('/graphql/', ['query' => $query]);
    assertMatchesJsonSnapshotWithoutSvg($response->getContent());
});