<!-- statamic:hide -->
[![Latest Version](https://img.shields.io/github/release/spatie/statamic-responsive-images.svg?style=flat-square)](https://github.com/spatie/statamic-responsive-images/releases)
![Statamic 4.0+](https://img.shields.io/badge/Statamic-4.0+-FF269E?style=flat-square&link=https://statamic.com)

<!-- /statamic:hide -->

# Responsive Images

This addon provides responsive images in Statamic inspired by [Our Medialibrary Package](https://github.com/spatie/laravel-medialibrary).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/statamic-responsive-images.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/statamic-responsive-images)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

Require it using Composer.

```bash
composer require spatie/statamic-responsive-images
```

Upon installation, it will publish this addons config file and the Blade templates used to generate the output of responsive images. If you do not plan to customize the template, feel free to delete the published view directory `resources/views/vendor/responsive-images`, the addon will use the default template.

## Using Responsive Images

This addon includes a fieldtype that allows for full art direction with responsive images. When you are editing your blueprint and adding a new field, a new fieldtype called "Responsive" should appear under the "Media" category. Configuration is similar to an asset fieldtype with additional configurations such as breakpoints, ratio and fit.

![fieldtype](./docs/fieldtype.png)

Once you have filled out the responsive field in your entry, simply call the responsive tag in your Antlers template like so:

```antlers
{{ responsive:image }}
```

Where `image` is the handle of our responsive field.

This will render a `<picture>` tag with srcsets. The tag uses JavaScript to define the value of the `sizes` attribute. This way the browser will always download the correct image depending on the your screens pixel density and the parent container element width.

## Configuration

This addon comes with its own config file. The config lives in `config/statamic/responsive-images.php`. Please take a look at the file yourself and go through each setting. We have configured it to some defaults. These config values apply globally, and can be overriden either through entry values or through tag parameters on individual basis. If you are not sure what to change, then the only interest point is the `breakpoints` setting which configures the breakpoint key name and the numerical value the breakpoint kicks in.

This addon relies on Statamic Glide and its assets, so a quick tour to `config/statamic/assets.php` is a also good idea. Particularly the `statamic.assets.image_manipulation.cache` setting. We recommend setting it to `false` so only images actually requested by a browser are generated. The first time the image is loaded will be slow, but Glide still has an internal cache that it will serve from the next time. This saves a lot on server resources and storage requirements. If you are curious about this more, we recommend reading Statamic documentation about this [here](https://statamic.dev/image-manipulation#caching).

## Tag parameters

### Breakpoints & Art direction

You can define breakpoints in the config file, by default the breakpoints of TailwindCSS are used in the config file and are referenced here in these examples.

Breakpoints allow you to use any tag parameter described below for that specific breakpoint onwards. For example you can have different ratios for different breakpoints:

```twig
{{ responsive:image_field ratio="1/1" lg:ratio="16/9" 2xl:ratio="2/1" }}
```

This will apply a default ratio of `1/1`. From breakpoint `lg` up to `2xl`, the ratio will be `16/9`. From `2xl` up, the ratio will be `2/1`.

Or different assets:

```twig
{{ responsive:image_field :lg:src="image_field_lg" :2xl:src="image_field_2xl" }}
```

All parameters described here can be prefixed with a breakpoint key and the parameters will apply from smallest breakpoint going up to largest breakpoints, with default breakpoint (no breakpoint prefix) applying settings to all breakpoints. Breakpoints are only used if they are explictly asked for either through tag params or entry values.

### Source image

You can pass source image in different way through `src` tag parameter. You can pass asset ID string or asset fieldtype value (you are not limited to Responsive fieldtype btw!). If it can resolve to an asset, the tag will try to use it.

### Image ratio

You can make sure images are a certain ratio by passing a `ratio` parameter, either as a string `16/10` or as a float `1.6`.

```twig
{{ responsive:image_field ratio="16/9" }}
```

### Blurry placeholder

By default, responsive images generates a small base64 encoded placeholder to show while your image loads. If you want to disable this you can pass `placeholder="false"` to the tag.

```twig
{{ responsive:image_field placeholder="false" }}
```

### Additional image format generation

By default, responsive tag generates original source image file format and WEBP variants of the image, so if you use a JPG image as source then by default JPG and WEBP variants will be generated. You can toggle WEBP and AVIF variant generation with the tag parameters.

```twig
{{ responsive:image_field webp="true" avif="false" }}
```

You can also toggle this in responsive-images.php config file, it will apply your preferences globally.

```php
'webp' => true,
'avif' => false,
```

### Image quality

Image format quality settings can be set globally through config. If you wish to override the config quality values you can use tag parameters.

```twig
{{ responsive:image_field quality:webp="50" md:quality:webp="75" }}
```

### Glide parameters

You can pass any parameters from the Glide tag that you normally can, just make sure to prefix them with `glide:`.
Passing `glide:width` will consider the width as a max width, which will prevent unnecessary large images from being generated.

```twig
{{ responsive:image_field glide:blur="20" glide:width="1600" }}
```

For a list of available Glide manipulation parameters please check out the Statamic Glide documentation page [here](https://statamic.dev/tags/glide#parameters)

### HTML Attributes

If you want to add additional HTML attributes to the `<img>` element, you can add them through tag parameters, attributes will be added to the image if they are not reserved by this addon.

```twig
{{ responsive:image_field alt="{title}" class="my-class" }}
```

If you want to conditionally render something based on a custom tag parameter, you can do the following by utilizing `$attributeString` variable in the Blade template. For example, to add lazy loading we have this Antlers tag usage:

```twig
{{ responsive:responsive_field lazyLoad="true" }}
```

Then in the [published addon Blade template](https://github.com/spatie/statamic-responsive-images/blob/main/resources/views/responsiveImage.blade.php) you can do the following (template stripped down for demonstration purposes):

```blade
<img
    src="{{ $src }}"
    @if(\Illuminate\Support\Str::contains($attributeString, 'lazyLoad="1"'))
        loading="lazy"
    @endif
>
```

And now you have conditional lazy-loading based on the tag parameter.

## Generating images in advance

If you wish to avoid long loading times for manipulated images and want to pre-warm the image cache, you have a few options.

### Generating images with default settings

If you do not plan to use any ratios or custom tag parameters, and wish to just embed an image in lets say an article, where you do not have to be strict with aspect ratio, then you can generate images in advance in two ways:

1. You can use the `php please responsive:generate` command which will generate responsive images for all assets. This command only works when you have the `statamic.assets.image_manipulation.cache` config option set to `true` (which we generally don't recommend; please refer to [this doc page](https://statamic.dev/image-manipulation#caching) on Glide caching).

2. Having `generate_on_upload` config setting to `true`, responsive images will be generated upon uploading. This also currently requires `statamic.assets.image_manipulation.cache` setting to be set to `true`.

If you are using a service, like Horizon, for queues then jobs will be queued to handle the image resizing.
By default, the job is queued under the 'default' queue. This can be changed via the `queue` config setting in `responsive-images.php`.

However as mentioned before, generating in advance loses it's value if you are using different ratios or tag parameters, from entry to entry, or in different templates. The generation command is not context aware, it just goes through all the assets in the asset containers and will use settings only from the config.

### Pre-warming image manipulations with varying parameters

If you have `statamic.assets.image_manipulation.cache` set to `false`, but still want to warm up the image manipulation cache, look at this [discussion thread](https://github.com/spatie/statamic-responsive-images/discussions/137) and try out [stuartcusackie/statamic-cache-requester PHP package](https://github.com/stuartcusackie/statamic-cache-requester). Essentially it checks all entries in your Statamic app, parses the HTML of those pages for Glide URLs and queues up them up for fetching those images, which warms up the cache.

## GraphQL

This addon comes with 2 GraphQL goodies, it adds a `responsive` field to assets and responsive fieldtype, allowing you to use this addon like you would with the Antlers tag. Secondly you can just let responsive fieldtype augment itself without passing any arguments.

### Responsive field on assets / assets fieldtype / responsive fieldtype

You can retrieve a responsive version of an `image` asset fieldtype like this:

```graphql
{
  entries {
    data {
      id,
      image {
        responsive(ratio: 1.2) {
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
            placeholder
          }
        }
      }
    }
  }
}
```

Majority of tag parameters are available as arguments in the responsive field, the parameters just need to have colons replaced with underscores. For example, `lg:glide:filter` would become `lg_glide_filter`.

If you are unsure what arguments are available, try out the GraphQL explorer in the control panel located at `/cp/graphiql` and utilize the autocomplete feature.

### Images from the responsive fieldtype

A responsive fieldtype has all the same fields as a normal responsive field from an asset, except they're under a `breakpoints` key and you cannot pass any arguments to it.

```graphql
{
  entries {
    data {
      id,
      art_image {
        breakpoints {
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
            placeholder
          }
        }
      }
    }
  }
}
```

## Publishing config and templates

Run the following command in your console

```bash
php artisan vendor:publish
```

and choose `Spatie\ResponsiveImages\ServiceProvider` from the menu.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Credits

- [Rias Van der Veken](https://github.com/riasvdv)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
