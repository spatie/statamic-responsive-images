<?php

namespace Spatie\ResponsiveImages\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Spatie\ResponsiveImages\Tests\TestCase;
use Statamic\Facades;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;

class AssetReferenceTest extends TestCase
{
    /** @var \Statamic\Assets\Asset */
    private $asset;

    private $responsiveFieldConfiguration = [
        'type' => 'responsive',
        'container' => 'test_container',
        'max_files' => 1,
        'use_breakpoints' => true,
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

    private $entryBlueprintWithSingleResponsiveField;

    protected function setUp(): void
    {
        parent::setUp();

        $file = new UploadedFile($this->getTestJpg(), 'test.jpg');
        $path = ltrim('/'.$file->getClientOriginalName(), '/');
        $this->asset = $this->assetContainer->makeAsset($path)->upload($file);

        Stache::clear();

        $this->entryBlueprintWithSingleResponsiveField = [
            'fields' => [
                [
                    'handle' => 'avatar',
                    'field' => $this->responsiveFieldConfiguration,
                ]
            ],
        ];
    }

    protected function setInBlueprints($namespace, $blueprintContents)
    {
        $blueprint = tap(Facades\Blueprint::make('set-in-blueprints')->setContents($blueprintContents))->save();

        Facades\Blueprint::shouldReceive('in')->with($namespace)->andReturn(collect([$blueprint]));
    }

    /**
     * @param $blueprintConfiguration
     * @param $entryData
     * @return \Statamic\Entries\Entry
     */
    protected function createDummyCollectionEntry($blueprintConfiguration, $entryData)
    {
        // Create collection
        $collection = tap(Facades\Collection::make('articles'))->save();

        $blueprintContents = $blueprintConfiguration;

        // Create blueprint for collection
        $this->setInBlueprints('collections/articles', $blueprintContents);

        // Create entry in the collection
        return tap(Facades\Entry::make()->collection($collection)->data($entryData))->save();
    }

    /** @test */
    public function asset_string_reference_gets_updated_after_asset_rename()
    {
        $entry = $this->createDummyCollectionEntry($this->entryBlueprintWithSingleResponsiveField, [
            'avatar' => [
                'src' => 'test_container::test.jpg',
                'ratio' => '16/9',
                'sm:src' => 'test_container::test.jpg',
                'sm:ratio' => '16/9',
            ],
        ]);

        $this->assertEquals('test_container::test.jpg', Arr::get($entry->get('avatar'), 'src'));

        $this->asset->rename('new-test2');

        $this->assertEquals('test_container::new-test2.jpg', Arr::get($entry->fresh()->get('avatar'), 'src'));
    }

    /** @test */
    public function asset_array_reference_gets_updated_after_asset_rename()
    {
        $startingAvatarData = [
            'src' => [
                'test_container::test.jpg'
            ],
            'sm:src' => [
                'test_container::test.jpg'
            ],
        ];

        $entry = $this->createDummyCollectionEntry($this->entryBlueprintWithSingleResponsiveField, [
            'avatar' => $startingAvatarData,
        ]);

        $this->assertEquals($startingAvatarData, $entry->get('avatar'));

        $this->asset->rename('new-test2');

        $this->assertEquals(
            [
                'src' => [
                    'test_container::new-test2.jpg'
                ],
                'sm:src' => [
                    'test_container::new-test2.jpg'
                ],
            ],
            $entry->fresh()->get('avatar')
        );
    }

    /** @test */
    public function asset_reference_gets_updated_in_replicator_set_after_asset_rename()
    {
        $blueprintContents = [
            'fields' => [
                [
                    'handle' => 'test_replicator_field',
                    'field' => [
                        'collapse' => false,
                        'previews' => true,
                        'sets' => [
                            'new_test_set' => [
                                'display' => 'New Test Set',
                                'fields' => [
                                    [
                                        'handle' => 'responsive_test_replicator',
                                        'field' => $this->responsiveFieldConfiguration,
                                    ],
                                ],
                            ],
                        ],
                        'display' => 'Test Replicator Field',
                        'type' => 'replicator',
                        'icon' => 'replicator',
                        'listable' => 'hidden',
                        'instructions_position' => 'above',
                        'visibility' => 'visible',
                    ],
                ]
            ]
        ];

        $entryData = [
            'test_replicator_field' => [
                [
                    'responsive_test_replicator' => [
                        'src' => [
                            'test_container::test.jpg'
                        ],
                    ],
                    'type' => 'new_test_set',
                    'enabled' => true,
                ],
            ],
        ];

        $entry = $this->createDummyCollectionEntry($blueprintContents, $entryData);

        $this->assertEquals(
            'test_container::test.jpg',
            Arr::get($entry->get('test_replicator_field'), '0.responsive_test_replicator.src.0')
        );

        $this->asset->rename('new-test2');

        $this->assertEquals(
            'test_container::new-test2.jpg',
            Arr::get($entry->fresh()->get('test_replicator_field'), '0.responsive_test_replicator.src.0')
        );
    }

    /** @test */
    public function asset_reference_gets_removed_after_asset_deletion()
    {
        $entry = $this->createDummyCollectionEntry($this->entryBlueprintWithSingleResponsiveField, [
            'avatar' => [
                'src' => 'test_container::test.jpg',
                'md:src' => 'test_container::test.jpg',
                'ratio' => '16/9',
                'md:ratio' => '16/9',
                'lg:src' => [
                    'test_container::test.jpg'
                ],
            ],
        ]);

        $this->assertEquals('test_container::test.jpg', Arr::get($entry->get('avatar'), 'src'));

        $this->asset->delete();

        $this->assertArrayNotHasKey('src', $entry->fresh()->data()->get('avatar'));
        $this->assertArrayNotHasKey('md:src', $entry->fresh()->data()->get('avatar'));
        $this->assertEmpty(Arr::get($entry->fresh()->data()->get('avatar'), 'lg:src'));
        $this->assertEquals('16/9', Arr::get($entry->fresh()->data()->get('avatar'), 'ratio'));
    }

    /** @test */
    public function asset_reference_stays_unchanged_after_asset_deletion_when_reference_updating_is_off()
    {
        config()->set('statamic.system.update_references', false);
        // Set up environment again because listeners in UpdateResponsiveReferences@subscribe depend on config value
        $this->setUp();

        $entry = $this->createDummyCollectionEntry($this->entryBlueprintWithSingleResponsiveField, [
            'avatar' => [
                'src' => 'test_container::test.jpg',
            ],
        ]);

        $this->assertEquals('test_container::test.jpg', Arr::get($entry->get('avatar'), 'src'));

        $this->asset->delete();

        $this->assertEquals('test_container::test.jpg', Arr::get($entry->fresh()->get('avatar'), 'src'));
    }
}
