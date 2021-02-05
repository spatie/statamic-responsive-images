# Changelog

All notable changes to `statamic-responsive-images` will be documented in this file

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
