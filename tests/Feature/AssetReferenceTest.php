<?php

use Statamic\Facades\Stache;
use Statamic\Support\Arr;

beforeEach(function () {
    $this->responsiveFieldConfiguration = [
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

    $this->asset = $this->uploadTestImageToTestContainer();

    Stache::clear();

    $this->entryBlueprintWithSingleResponsiveField = [
        'fields' => [
            [
                'handle' => 'avatar',
                'field' => $this->responsiveFieldConfiguration,
            ]
        ],
    ];
});

test('asset string reference gets updated after asset rename', function () {
    $entry = test()->createDummyCollectionEntry($this->entryBlueprintWithSingleResponsiveField, [
        'avatar' => [
            'src' => 'test_container::test.jpg',
            'ratio' => '16/9',
            'sm:src' => 'test_container::test.jpg',
            'sm:ratio' => '16/9',
        ],
    ]);

    expect(Arr::get($entry->get('avatar'), 'src'))->toEqual('test_container::test.jpg');

    $this->asset->rename('new-test2');

    expect(Arr::get($entry->fresh()->get('avatar'), 'src'))->toEqual('test_container::new-test2.jpg');
});

test('asset array reference gets updated after asset rename', function () {
    $startingAvatarData = [
        'src' => [
            'test_container::test.jpg'
        ],
        'sm:src' => [
            'test_container::test.jpg'
        ],
    ];

    $entry = test()->createDummyCollectionEntry($this->entryBlueprintWithSingleResponsiveField, [
        'avatar' => $startingAvatarData,
    ]);

    expect($entry->get('avatar'))->toEqual($startingAvatarData);

    $this->asset->rename('new-test2');

    expect($entry->fresh()->get('avatar'))->toEqual([
        'src' => [
            'test_container::new-test2.jpg'
        ],
        'sm:src' => [
            'test_container::new-test2.jpg'
        ],
    ]);
});

test('asset reference gets updated in replicator set after asset rename', function () {
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

    $entry = test()->createDummyCollectionEntry($blueprintContents, $entryData);

    expect(
        Arr::get($entry->get('test_replicator_field'), '0.responsive_test_replicator.src.0')
    )->toEqual('test_container::test.jpg');

    $this->asset->rename('new-test2');

    expect(
        Arr::get($entry->fresh()->get('test_replicator_field'), '0.responsive_test_replicator.src.0')
    )->toEqual('test_container::new-test2.jpg');
});

test('asset reference gets removed after asset deletion', function () {
    $entry = test()->createDummyCollectionEntry($this->entryBlueprintWithSingleResponsiveField, [
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

    expect(
        Arr::get($entry->get('avatar'), 'src')
    )->toEqual('test_container::test.jpg');

    $this->asset->delete();

    expect($entry->fresh()->data()->get('avatar'))->not->toHaveKey('src')
        ->and($entry->fresh()->data()->get('avatar'))->not->toHaveKey('md:src')
        ->and(Arr::get($entry->fresh()->data()->get('avatar'), 'lg:src'))->toBeEmpty()
        ->and(Arr::get($entry->fresh()->data()->get('avatar'), 'ratio'))->toEqual('16/9');
});

test('asset reference stays unchanged after asset deletion when reference updating is off', function () {
    config()->set('statamic.system.update_references', false);
    // Set up environment again because listeners in UpdateResponsiveReferences@subscribe depend on config value
    $this->setUp();
    // Re-upload because setUp() deletes asset that we uploaded in beforeEach()
    $this->asset = $this->uploadTestImageToTestContainer();

    $entry = test()->createDummyCollectionEntry($this->entryBlueprintWithSingleResponsiveField, [
        'avatar' => [
            'src' => 'test_container::test.jpg',
        ],
    ]);

    expect(
        Arr::get($entry->get('avatar'), 'src')
    )->toEqual('test_container::test.jpg');

    $this->asset->delete();

    expect(
        Arr::get($entry->fresh()->get('avatar'), 'src')
    )->toEqual('test_container::test.jpg');
});
