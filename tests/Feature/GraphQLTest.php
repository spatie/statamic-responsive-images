<?php

use Facades\Statamic\Fields\BlueprintRepository;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFieldtype;
use Statamic\Facades\Blueprint;
use Spatie\ResponsiveImages\Tests\Factories\EntryFactory;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;
use function Spatie\Snapshots\assertMatchesSnapshot;

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
                        mediaWidthUnit
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
    config()->set('statamic.responsive-images.webp', false);
    config()->set('statamic.responsive-images.placeholder', false);

    // Without this line, the GraphQL query will fail because the fieldtype is not registered somehow.
    ResponsiveFieldtype::register();

    $article = Blueprint::makeFromFields([
        'hero' => [
            'type' => 'responsive',
            'container' => 'test_container',
            'max_files' => 1,
            'use_breakpoints' => true,
            'allow_ratio' => false,
            'allow_fit' => true,
            'restrict' => false,
            'allow_uploads' => true,
            'display' => 'Hero image',
            'icon' => 'assets',
            'listable' => 'hidden',
            'instructions_position' => 'above',
            'visibility' => 'visible',
        ],
    ]);

    BlueprintRepository::partialMock()->shouldReceive('in')->with('collections/blog')->andReturn(collect([
        'article' => $article->setHandle('article'),
    ]));

    (new EntryFactory)->collection('blog')->id('1')->data([
        'title' => 'Responsive Images addon is awesome',
        'hero' => [
            'src' => 'test_container::test.jpg',
        ],
    ])->create();

    $query = '
        {
            entry(id: "1") {
                title
                ... on Entry_Blog_Article {
                    hero {
                        breakpoints {
                            asset {
                                path
                            }
                            label
                            minWidth
                            widthUnit
                            ratio
                            sources {
                                format
                                mimeType
                                minWidth
                                mediaWidthUnit
                                mediaString
                                srcSet
                            }
                        }
                    }
                }
            }
        }
    ';

    $response = $this
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $output = json_decode($response, true)['data']['entry'];

    assertMatchesSnapshot($output);
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