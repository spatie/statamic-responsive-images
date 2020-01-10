![Icon](icon.png)

[![Latest Version](https://img.shields.io/github/release/riasvdv/statamic-responsive-images.svg?style=flat-square)](https://github.com/riasvdv/statamic-responsive-images/releases)

# Responsive Images

> Responsive Images for Statamic 3.

This Addon provides responsive images inspired by [Spatie's Medialibrary](https://github.com/spatie/laravel-medialibrary).

## Installation

Require it using Composer.

```
composer require rias/statamic-responsive-images
```

## Using Responsive Images

Pass an image to the `responsive` tag.

```twig
{{ responsive:image_field }}
```

## Responsive placeholder

By default, responsive images generates a small base64 encoded placeholder to show while your image loads. If you want to disable this you can pass `placeholder="false"` to the tag.

```twig
{{ responsive:image_field placeholder="false" }}
```

## Webp image generation

By default, responsive images generates webp variants of your image, these are usually smaller. If you want to disable this functionality you can pass `webp="false"` to your tag.

```twig
{{ responsive:image_field alt="{title}" webp="false" }}
```

## HTML Attributes

If you want to add additional attributes (for example a title attribute) to your image, you can add them as parameters to the tag, any attributes will be added to the image.

```twig
{{ responsive:image_field alt="{title}" class="my-class" }}
```

---
Brought to you by [Rias](https://rias.be)
