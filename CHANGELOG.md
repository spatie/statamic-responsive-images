# Changelog

All notable changes to `statamic-responsive-images` will be documented in this file

## v2.13.1 - 2022-08-27

### What's Changed

- Prevent float to int conversion warning by @kevinmeijer97 in https://github.com/spatie/statamic-responsive-images/pull/151
- Fix fieldtype Vue component issues by @ncla in https://github.com/spatie/statamic-responsive-images/pull/156

### New Contributors

- @kevinmeijer97 made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/151

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.13.0...v2.13.1

## v2.13.0 - 2022-07-20

### What's Changed

- Explicitly define crop focus by @ncla in https://github.com/spatie/statamic-responsive-images/pull/145

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.12.4...v2.13.0

## v2.12.4 - 2022-07-04

### What's changed

- Try another fix for alt tags
- The responsive blade directive can now handle augmented fieldtype data (array)
- Fix an exception with `OrderedQueryBuilder`

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.12.3...v2.12.4

## v2.12.3 - 2022-05-25

## What's Changed

- Fix `method_exists` exception by @aerni in https://github.com/spatie/statamic-responsive-images/pull/136

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.12.2...v2.12.3

## v2.12.2 - 2022-05-25

## What's Changed

- Add fix for issue #133 by @GertTimmerman in https://github.com/spatie/statamic-responsive-images/pull/135

## New Contributors

- @GertTimmerman made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/135

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.12.1...v2.12.2

## v2.12.1 - 2022-05-24

- Fix an issue where images could get upscaled #134

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.12.0...v2.12.1

## v2.12.0 - 2022-05-23

## What's Changed

- AVIF images and quality settings by @ncla in https://github.com/spatie/statamic-responsive-images/pull/123
- Use asset data alt tag and fallback to title - fix #74
- Max width now counts as max width for the source image as well - fix #114
- Asset param can be a query builder - fix #116

## New Contributors

- @ncla made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/123

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.11.2...v2.12.0

## v2.11.2 - 2022-04-19

## What's Changed

- Use setValue instead of fillValue by @hgrimelid in https://github.com/spatie/statamic-responsive-images/pull/122

## New Contributors

- @hgrimelid made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/122

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.11.1...v2.11.2

## v2.11.1 - 2022-04-15

## What's Changed

- prevent getFieldsWithValues error by @yipeecaiey in https://github.com/spatie/statamic-responsive-images/pull/117
- Use computed name property to avoid conflicts with replicator by @quintenbuis in https://github.com/spatie/statamic-responsive-images/pull/119

## New Contributors

- @yipeecaiey made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/117
- @quintenbuis made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/119

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.11.0...v2.11.1

## v2.11.0 - 2022-03-18

- Fix Statamic 3.3 support
- Drop support for 3.2 and 3.1

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.10.1...v2.11.0

## v2.10.1 - 2022-03-18

- Fix the fieldtype not saving values correctly

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.10.0...v2.10.1

## v2.10.0 - 2022-03-15

- Add support for Statamic 3.3
- Drop support for Statamic 3.1

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.9.3...v2.10.0

## v2.9.3 - 2022-03-15

- Fix PHP 7.4 compatibility

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.9.2...v2.9.3

## v2.9.2 - 2022-03-14

- Fix default breakpoint ratio not working

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.9.1...v2.9.2

## v2.9.1 - 2022-03-11

- Fix an issue with asset height not being calculated correctly

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.9.0...v2.9.1

## v2.9.0 - 2022-03-11

## What's Changed

- Test against php 8.1 by @sergiy-petrov in https://github.com/spatie/statamic-responsive-images/pull/100
- [Fix](https://github.com/spatie/statamic-responsive-images/commit/edf10b72a09b2718ddf8b7bb858ad5cca4e7e874) https://github.com/spatie/statamic-responsive-images/issues/105 [- Don't inherit ratio](https://github.com/spatie/statamic-responsive-images/commit/edf10b72a09b2718ddf8b7bb858ad5cca4e7e874)
- [Fix](https://github.com/spatie/statamic-responsive-images/commit/6938e4c62708c9bccc406f4f6a71d47cc1f00cda) https://github.com/spatie/statamic-responsive-images/issues/59 [- Add asset validation](https://github.com/spatie/statamic-responsive-images/commit/6938e4c62708c9bccc406f4f6a71d47cc1f00cda)
- [Fix](https://github.com/spatie/statamic-responsive-images/commit/a3c8602685af80fd604de98f8937e504f4e78f1c) https://github.com/spatie/statamic-responsive-images/issues/81 [- Add index fieldtype display](https://github.com/spatie/statamic-responsive-images/commit/a3c8602685af80fd604de98f8937e504f4e78f1c)
- [Fix duplicate alt tag issue -](https://github.com/spatie/statamic-responsive-images/commit/2706a7b28247db851b8a0ad9b070be3554a3af37) [fix](https://github.com/spatie/statamic-responsive-images/commit/2706a7b28247db851b8a0ad9b070be3554a3af37) https://github.com/spatie/statamic-responsive-images/issues/104
- [Use the first assetContainer if none is set](https://github.com/spatie/statamic-responsive-images/commit/a6fe385e838e44e2031a605523ea91be36204d91)
- [Try a different resize observer technique](https://github.com/spatie/statamic-responsive-images/commit/7b086425c73d5454e9c71c217d0a5d8b836a2a02)

## New Contributors

- @sergiy-petrov made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/100

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.8.5...v2.9.0

## 2.8.5 - 2021-10-29

## What's fixed

- Fix images not rendering (#99)

## 2.8.4 - 2021-10-29

## What's fixed

- Return of the missing "default" breakpoint

## 2.8.3 - 2021-10-22

## What's fixed

- Fix an issue where an undefined breakpoint could occur (#95)
- Only require the first image if the field config contains required validation (#97)

## 2.8.2 - 2021-10-13

## What's fixed

- Fix an issue when localizable isn't set in the blueprint

## 2.8.1 - 2021-10-13

## What's fixed

- Fix the field not being localizable

## 2.8.0 - 2021-08-19

### What's new

- Allow Statamic 3.2

## 2.7.1 - 2021-07-07

### What's fixed

- We now throw our own exception with more information when a corrupt image is being handled (#71)

## 2.7.0 - 2021-07-07

### What's new

- Added GraphQL support ([docs](https://github.com/spatie/statamic-responsive-images#graphql))

## 2.6.0 - 2021-07-02

### What's new

- We now use ResizeObserver instead of an inline script to set the sizes attribute on the images

## 2.5.5 - 2021-06-24

### What's fixed

- Prevent the fieldtype from generating too many breakpoints (#66)

## 2.5.4 - 2021-05-07

### What's fixed

- Assets can be retrieved from an augmented asset's array values

## 2.5.3 - 2021-03-17

### What's fixed

- Fixed an issue when using the fieldtype inside a replicator or Bard set - #51

## 2.5.2 - 2021-03-05

### What's fixed

- Fixed an issue with invalid asset exception - #58

## 2.5.1 - 2021-02-26

### What's fixed

- Fixed an issue where the placeholder ratio was not always correct

## 2.5.0 - 2021-02-22

### What's fixed

- Fixed an issue with images not being output
- Wrapped the components in Statamic.booting to prevent loading issues
- Reworked how assets are generated on upload & with the command which should result in them being used again instead of regenerating when visiting the page.

### What's new

- Added a config option to disable image generation on upload

## 2.4.4 - 2021-02-09

### What's fixed

- Cache placeholder generation

## 2.4.3 - 2021-02-08

### What's fixed

- The breakpoint no longer forces a relative URL

## 2.4.2 - 2021-02-05

### What's fixed

- Fix config registration

## 2.4.1 - 2021-02-05

### What's fixed

- Fix assets not being published

## 2.4.0 - 2021-02-05

### What's new

- The generate image job is now configurable

## 2.3.0 - 2021-02-05

### What's new

- The `placeholder` is now available in the view
- The global config now contains global `webp` and `placeholder` config settings

### What's fixed

- Gifs are now treated the same as SVGs, they don't generate variants
- Fixed the Blade directive

## 2.2.1 - 2021-02-02

### What's fixed

- Fixed an issue where passing a `null` ratio would break

## 2.2.0 - 2021-02-01

### What's new

- Added a fieldtype that allows full art direction.

## 2.1.1 - 2021-01-26

- [new] Responsive images now supports true Art direction with the possibility to add a different `src` for each breakpoint.

## 2.0.1 - 2021-01-26

- [fix] Fix the order of picture sources

## 2.0.0 - 2021-01-25

### Breaking

- The `\Spatie\ResponsiveImages\Responsive` class has been renamed to `\Spatie\ResponsiveImages\ResponsiveTag`
- The views have been changed to Blade views instead of Antlers views.

### Changes

- [new] Added the possibility for Art Direction [#32]

## 1.8.0 - 2021-01-13

- [new] Added a config option for a global max width on generated images

## 1.7.1

- [fix] The max width parameter now generates an image correctly when only 1 size was calculated [#27]

## 1.7.0

- [new] Allow non-integer ratios

## 1.6.0 - 2020-12-14

- [new] Added a `Responsive::render()` function
- [new] Added a `@responsive` Blade directive
- [new] You can now pass an asset url as parameter instead of needing to pass an asset object

## 1.5.1 - 2020-12-11

- [fix] Register the command

## 1.5.0 - 2020-12-11

- [new] Adds a `generate` command to generate responsive images without clearing the glide cache

## 1.4.0 - 2020-11-17

- [new] Share the `asset` data with the views

## 1.3.2 - 2020-11-17

- [fix] Improve exception handling in command

## 1.3.1 - 2020-11-17

- [fix] Fix an issue with small images
- [fix] Make sure the placeholder respects the image ratio

## 1.3.0 - 2020-10-05

- [new] Added GenerateImageJob queue specification via config

## 1.2.3 - 2020-08-07

- [fix] Fix publishing & loading of the views

## 1.2.2 - 2020-07-31

- [fix] Fix compatibility with latest Statamic Beta

## 1.2.1 - 2020-06-20

- [fix] Fix a bug where webp images weren't being shown

## 1.2.0 - 2020-03-27

- [new] Responsive tag now accepts a `ratio` parameter
- [new] Glide parameters are passed through using a `glide:` prefix

## 1.1.0 - 2020-03-25

- [new] Now generates responsive versions after asset upload
- [new] Added a command to regenerate responsive image versions

## 1.0.0 - 2020-01-10

- initial release
