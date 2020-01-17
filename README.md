![Icon](icon.png)

[![Latest Version](https://img.shields.io/github/release/spatie/statamic-responsive-images.svg?style=flat-square)](https://github.com/spatie/statamic-responsive-images/releases)

# Responsive Images

> Responsive Images for Statamic 3.

This Addon provides responsive images inspired by [Our Medialibrary Package](https://github.com/spatie/laravel-medialibrary).

## Installation

Require it using Composer.

```
composer require spatie/statamic-responsive-images
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

By default, responsive images generates webp variants in addition to jpg or png versions of your image, these are usually smaller. If you want to disable this functionality you can pass `webp="false"` to your tag.

```twig
{{ responsive:image_field webp="false" }}
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

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Rias Van der Veken](https://github.com/riasvdv)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie).
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

