[![Latest Version](https://img.shields.io/github/release/spatie/statamic-responsive-images.svg?style=flat-square)](https://github.com/spatie/statamic-responsive-images/releases)
![Statamic 3.0+](https://img.shields.io/badge/Statamic-3.0+-FF269E?style=flat-square&link=https://statamic.com)

# Responsive Images

> Responsive Images for Statamic 3.

This Addon provides responsive images inspired by [Our Medialibrary Package](https://github.com/spatie/laravel-medialibrary).

## Support us

Learn how to create a package like this one, by watching our premium video course:

[![Laravel Package training](https://spatie.be/github/package-training.jpg)](https://laravelpackage.training)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

Require it using Composer.

```
composer require spatie/statamic-responsive-images
```

## Using Responsive Images

Responsive Images will generate responsive versions of the images whenever a new asset is uploaded. If you need to regenerate the responsive images for a reason, you can use the `regenerate` command which will clear the Glide cache and regenerate the versions:

```bash
php please responsive:regenerate
```

## Templating

Pass an image to the `responsive` tag.

```twig
{{ responsive:image_field }}
```

## Image ratio

You can make sure images are a certain ratio by passing a `ratio` parameter, either as a string `16/10` or as a float `1.6`.

```twig
{{ responsive:image_field ratio="16/9" }}
```

## Responsive placeholder

By default, responsive images generates a small base64 encoded placeholder to show while your image loads. If you want to disable this you can pass `placeholder="false"` to the tag.

```twig
{{ responsive:image_field placeholder="false" }}
```

## Webp image generation

By default, responsive images generates webp variants in addition to jpg or png versions of your image, these are usually smaller. If you want to disable this functionality you can pass `webp="false"` to your tag.

```twig
{{ responsive:image_field webp="false" }}
```

## Glide parameters

You can still pass any parameters from the Glide tag that you would want to, just make sure to prefix them with `glide:`.
Passing `glide:width` will consider the width as a max width, which can prevent unnecessary large images from being generated.

```twig
{{ responsive:image_field glide:blur="20" glide:width="1600" }}
```

## HTML Attributes

If you want to add additional attributes (for example a title attribute) to your image, you can add them as parameters to the tag, any attributes will be added to the image.

```twig
{{ responsive:image_field alt="{title}" class="my-class" }}
```

## Customizing the generated html

If you want to customize the generated html, you can publish the views using

```bash
php artisan vendor:publish
```

and choosing `Spatie\ResponsiveImages\ServiceProvider`

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.

## Credits

- [Rias Van der Veken](https://github.com/riasvdv)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
