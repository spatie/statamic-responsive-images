# Changelog

All notable changes to `statamic-responsive-images` will be documented in this file

## v5.2.2 - 2025-02-11

### What's Changed

* Update AssetNotFoundException to handle $assetParam as array by @JeremyDunn in https://github.com/spatie/statamic-responsive-images/pull/256

### New Contributors

* @JeremyDunn made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/256

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v5.2.1...v5.2.2

## v5.2.1 - 2024-10-30

### What's Changed

* Move placeholder generation into job by @aerni in https://github.com/spatie/statamic-responsive-images/pull/255

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v5.2.0...v5.2.1

## v5.2.0 - 2024-10-30

* Add a way to configure the dimension calculator threshold

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v5.1.1...v5.2.0

## v5.1.1 - 2024-10-30

* Also dispatch a generate job for the default `src` when generating images

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v5.1.0...v5.1.1

## v5.1.0 - 2024-10-14

### What's Changed

* Apply glide params to src image by @aerni in https://github.com/spatie/statamic-responsive-images/pull/252

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v5.0.1...v5.1.0

## v5.0.1 - 2024-09-12

### What's Changed

* Return empty string when image is not found by @kevinmeijer97 in https://github.com/spatie/statamic-responsive-images/pull/242
* Also return a empty string if HTTP Not found as this will throw a 404… by @BobWez98 in https://github.com/spatie/statamic-responsive-images/pull/249

### New Contributors

* @BobWez98 made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/249

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v5.0.0...v5.0.1

## v5.0.0 - 2024-05-13

### What's Changed

* v5 support by @ncla in https://github.com/spatie/statamic-responsive-images/pull/243
* Added option to exclude containers from generating responsive variants by @kevinmeijer97 in https://github.com/spatie/statamic-responsive-images/pull/240

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v4.1.1...v5.0.0

## v4.1.1 - 2023-07-23

### What's Changed

- Fail silently on asset exceptions when processing data for fieldtype index (entry lists, tables), fixes #231 by @ncla in https://github.com/spatie/statamic-responsive-images/pull/235

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v4.1.0...v4.1.1

## v4.1.0 - 2023-07-23

### What's Changed

- Accept `width` param from GraphQL arguments for dimension calculations; fixes #233 by @ncla in https://github.com/spatie/statamic-responsive-images/pull/234
- Add `glide_width` as possible argument for GraphQL `responsive` field

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v4.0.3...v4.1.0

## v4.0.3 - 2023-05-20

### What's Changed

- Fix `sometimes` validation not working when hiding responsive field by @ncla in https://github.com/spatie/statamic-responsive-images/pull/229

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v4.0.2...v4.0.3

## v4.0.2 - 2023-05-14

### What's Changed

- Handle empty src in GraphQL ResponsiveFieldType for fresh collections by @ncla in https://github.com/spatie/statamic-responsive-images/pull/226

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v4.0.1...v4.0.2

## v4.0.1 - 2023-05-13

### What's Changed

- Allow extending calculation methods in the `ResponsiveDimensionCalculator` by @ncla in https://github.com/spatie/statamic-responsive-images/pull/225

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v4.0.0...v4.0.1

## v4.0.0 - 2023-05-13

### What's Changed

- Statamic v4 support by @bengs @ncla in https://github.com/spatie/statamic-responsive-images/pull/221
- Asset fieldtype styles within Responsive field have been adjusted for narrow spaces

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v3.1.3...v4.0.0

## v3.1.3 - 2023-02-11

### What's Changed

- Rethrow `Unable to read file` exception for placeholder generation in SSG context by @ncla in https://github.com/spatie/statamic-responsive-images/pull/209. Developers must be aware of failed placeholder generation when running SSG due to a SSG bug. Workaround and discussion about it can be found [here](https://github.com/spatie/statamic-responsive-images/issues/178#issuecomment-1310963543), alternatively just disable placeholder generation.

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v3.1.2...v3.1.3

## v3.1.2 - 2023-02-11

### What's Changed

- GraphQL: let responsive field/fieldtype fail more gracefully when default breakpoint asset has not been found by @ncla in https://github.com/spatie/statamic-responsive-images/pull/208

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v3.1.1...v3.1.2

## v3.1.1 - 2023-02-09

### What's Changed

- Fix `AssetUploaded` listener `GenerateResponsiveVersions` erroring out due to outdated code, `Breakpoint` no longer dispatches jobs by @ncla in https://github.com/spatie/statamic-responsive-images/pull/207
- Fix `AssetUploaded` listener `GenerateResponsiveVersions` firing off twice due to mistakenly booting events twice in `ServiceProvider` by @ncla in https://github.com/spatie/statamic-responsive-images/pull/207

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v3.1.0...v3.1.1

## v3.1.0 - 2023-02-07

### What's Changed

GraphQL improvements by @ncla in https://github.com/spatie/statamic-responsive-images/pull/203

- Support way more arguments in responsive field, add all of them for each breakpoint
- Accept fieldtype data into GraphQL responsive field

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v3.0.0...v3.1.0

## v3.0.0 - 2023-02-04

**This is a breaking change release, as such please see UPGRADE.md for instructions on how to upgrade from 2.x version to 3.x.**

### What's Changed

- Extensible dimension calculators by @ncla in https://github.com/spatie/statamic-responsive-images/pull/193
  
- - Developers now can customize the amount of images that get generated and their exact dimensions through a custom `DimensionCalculator` that developers bind in `ServiceProvider`. Calculations can be done for each breakpoint and source, and receive `Breakpoint` / `Source` in the calculation methods, which allows to access breakpoint parameters, original asset and more. For motivation and examples of this, please see the PR and the original issue associated with it.
  
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- Fix max widths config value or glide width param not being respected in some cases
  
- Fix floating numbers being output for width and height values for Glide endpoint, they are now rounded integers
  
- Placeholder now can be toggled per breakpoint
  
- `<img>` tag src will now go through Glide instead of pointing to the original asset file, due to `DimensionCalculators` ability to return specific widths and heights for `<img>` tag
  
- Empty `media` attributes will no longer be outputted
  
- Properties have been renamed to be more descriptive:
  
- - `value` to `minWidth`
  
- 
- 
- 
- 
- 
- 
- 
- 
- 
- - `unit` to `widthUnit`
  
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- Add `mimeType` property to explicitly help browsers determine what images does the `<source>` contain
  
- Housekeeping: add additional tests for dimension calculator and GraphQL
  

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.15.2...v3.0.0

## v2.15.2 - 2023-01-28

### What's Changed

- Fix broken thumbnails on control panel entry index pages on Statamic v3.4 by @ncla in https://github.com/spatie/statamic-responsive-images/pull/201

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.15.1...v2.15.2

## v2.15.1 - 2023-01-12

### What's Changed

- Fix ResponsiveFieldtype erroring out in Replicator sets due to PublishContainer by @ncla in https://github.com/spatie/statamic-responsive-images/pull/192

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.15.0...v2.15.1

## v2.15.0 - 2023-01-09

### What's Changed

- Add config option to force absolute URLs on Glide URLs by @SteJW in https://github.com/spatie/statamic-responsive-images/pull/190

### New Contributors

- @SteJW made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/190

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.14.5...v2.15.0

## v2.14.5 - 2023-01-08

### What's Changed

- Placeholder generation now fails silently on production by logging exceptions if `APP_DEBUG` is `false` by @heidkaemper in https://github.com/spatie/statamic-responsive-images/pull/189
- Refactor tests to Pest by @alexmanase in https://github.com/spatie/statamic-responsive-images/pull/183

### New Contributors

- @alexmanase made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/183
- @heidkaemper made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/189

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.14.4...v2.14.5

## v2.14.4 - 2022-11-09

### What's Changed

- Force `<img>` tag width & height parameters to an integer by @micahhenshaw in https://github.com/spatie/statamic-responsive-images/pull/180

### New Contributors

- @micahhenshaw made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/180

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.14.3...v2.14.4

## v2.14.3 - 2022-11-02

### What's Changed

- Control panel JS assets have been compiled for distribution with missing fixes from https://github.com/spatie/statamic-responsive-images/pull/156

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.14.2...v2.14.3

## v2.14.2 - 2022-10-16

### What's Changed

- Fix placeholder generation failing when changing `assets.image_manipulation.cache` value by @ncla in https://github.com/spatie/statamic-responsive-images/pull/174

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.14.1...v2.14.2

## v2.14.1 - 2022-10-16

### What's Changed

- Fix `avif` attribute being output to `<img>` tag by @ncla in https://github.com/spatie/statamic-responsive-images/pull/170
- Fix `src` saving as an array when it should be a string by @ncla in https://github.com/spatie/statamic-responsive-images/pull/173

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.14.0...v2.14.1

## v2.14.0 - 2022-09-26

### What's Changed

- Add asset reference updating for Repsonsive fieldtype by @stuartcusackie @ncla in https://github.com/spatie/statamic-responsive-images/pull/149
- Fix required validation for default breakpoint by @ncla in https://github.com/spatie/statamic-responsive-images/pull/164

### New Contributors

- @stuartcusackie made their first contribution in https://github.com/spatie/statamic-responsive-images/pull/149

**Full Changelog**: https://github.com/spatie/statamic-responsive-images/compare/v2.13.1...v2.14.0

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
