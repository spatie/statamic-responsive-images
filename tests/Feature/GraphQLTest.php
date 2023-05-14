<?php

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Route;
use Spatie\ResponsiveImages\AssetNotFoundException;
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

function createEntryWithField($additionalFieldConfig = [], $additionalEntryData = null)
{
    $article = Blueprint::makeFromFields([
        'hero' => array_merge([
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
        ], $additionalFieldConfig),
    ]);

    BlueprintRepository::partialMock()->shouldReceive('in')->with('collections/blog')->andReturn(collect([
        'article' => $article->setHandle('article'),
    ]));

    (new EntryFactory)->collection('blog')->id('1')->data([
        'title' => 'Responsive Images addon is awesome',
        'hero' => $additionalEntryData === null ? ['src' => 'test.jpg'] : $additionalEntryData,
    ])->create();
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

    createEntryWithField();

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

test('responsive field accepts responsive fieldtype data', function () {
    createEntryWithField();

    $query = '
        {
            entry(id: "1") {
                title
                ... on Entry_Blog_Article {
                    hero {
                        responsive(placeholder: false, webp: false) {
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
            }
        }
    ';

    $response = $this
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $output = json_decode($response, true)['data']['entry'];

    assertMatchesSnapshot($output);
});

it('accepts glide parameters just like responsive tag would', function () {
    $query = '
            {
                asset(id: "test_container::test.jpg") {
                    responsive(glide_filter: "greyscale", lg_glide_filter: "greyscale", webp: false, placeholder: false) {
                        sources {
                            srcSet
                        }
                    }
                }
            }
        ';

    $response = $this
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $response = json_decode($response, true)['data'];

    expect($response['asset']['responsive'][0]['sources'][0]['srcSet'])->toContain('?filt=greyscale');
    expect($response['asset']['responsive'][1]['sources'][0]['srcSet'])->toContain('?filt=greyscale');
});

it('fails silently and returns null when asset is not found when using fieldtype data', function () {
    createEntryWithField([], ['src' => 'not-exist.jpg']);

    $query = '
        {
            entry(id: "1") {
                title
                ... on Entry_Blog_Article {
                    hero {
                        breakpoints {
                            sources {
                                srcSet
                            }
                        }
                    }
                }
            }
        }
    ';

    $response = $this
        ->withoutExceptionHandling()
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $response = json_decode($response, true);

    expect(isset($response['errors']))->toBeFalse();
    expect($response['data']['entry']['hero']['breakpoints'])->toBeNull();
});

it('fails silently and returns null when asset is not found when using responsive field on asset', function () {
    test()->createEntryWithField(
        ['type' => 'assets'],
        'not-exist.jpg'
    );

    $query = '
        {
            entry(id: "1") {
                title
                ... on Entry_Blog_Article {
                    hero {
                        responsive(placeholder: false, webp: false) {
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
            }
        }
    ';

    $response = $this
        ->withoutExceptionHandling()
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $response = json_decode($response, true);

    expect(isset($response['errors']))->toBeFalse();
    expect($response['data']['entry']['hero'])->toBeNull();
});

it('missing lg breakpoint asset uses default breakpoint asset instead', function () {
    test()->createEntryWithField(
        [
            'breakpoints' => [
                'lg'
            ]
        ],
        [
            'src' => 'test.jpg',
            'lg:src' => 'not-exist.jpg',
        ]
    );

    $query = '
        {
            entry(id: "1") {
                title
                ... on Entry_Blog_Article {
                    hero {
                        breakpoints {
                            label
                            sources {
                                srcSet
                            }
                        }
                    }
                }
            }
        }
    ';

    $response = $this
        ->withoutExceptionHandling()
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $response = json_decode($response, true);

    expect(isset($response['errors']))->toBeFalse();
    expect($response['data']['entry']['hero']['breakpoints'][0]['sources'][0]['srcSet'])
        ->toEqual($response['data']['entry']['hero']['breakpoints'][1]['sources'][0]['srcSet']);
});

// https://github.com/spatie/statamic-responsive-images/issues/214
it('returns null for breakpoints if src is not set in a newly made collection', function () {
    test()->createEntryWithField([], []);

    $query = '
        {
            entry(id: "1") {
                title
                ... on Entry_Blog_Article {
                    hero {
                        breakpoints {
                            label
                            sources {
                                srcSet
                            }
                        }
                    }
                }
            }
        }
    ';

    $response = $this
        ->withoutExceptionHandling()
        ->postJson('/graphql/', ['query' => $query])
        ->getContent();

    $response = json_decode($response, true);

    expect(isset($response['errors']))->toBeFalse();
    expect($response['data']['entry']['hero']['breakpoints'])->toBeNull();
});