# Upgrade Guide

# Upgrading to 3.x from 2.x

`Breakpoint` now contain `Source` objects which represent the `<source>` tag.

## High impact

### responsiveImage.blade.php template changes

If you had published the `responsiveImage.blade.php` template in your project, you will need to update it to use the
new `Source` objects.

If you had published the templates, you will find them in `resources/views/vendor/responsive-images`. If you do not see
this file in your project then you do not have to do anything for this.

To review the changes, please
use [the history of the template](https://github.com/spatie/statamic-responsive-images/commits/main/resources/views/responsiveImage.blade.php)
in this repository. We recommend just copy and pasting the whole template and adding your changes as you go.

### GraphQL query changes

If you are using GraphQL in your project, you will have to update your queries, as each breakpoint will now contain array of sources.

If you had a query like this:

```graphql
{
    entries {
        data {
            id,
            image {
                responsive(ratio: 1.2) {
                    label
                    value
                    unit
                    ratio
                    mediaString
                    srcSet
                    srcSetWebp
                    srcSetAvif
                    placeholder
                }
            }
        }
    }
}
```

Then that would become:

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

Additionally, some properties have changed name to be more descriptive of what they are:
- `value` to `minWidth`
- `unit` to `widthUnit`

A new addition is `mimeType` property which you should set on `<source>` tags to help browsers determine which image format to serve.

These changes are also in effect when you query responsive fieldtype, except the data is under `breakpoints` key, just like before.

To easily test the GraphQL changes and see what is available, we recommend using in-built GUI which you can visit through `/cp/graphiql`. This tool provides auto-complete.

## Low impact

`<img>` `src` URL will always now go through Glide instead of pointing to original asset. Meaning, the filename will become base64 encoded by Statamic Glide controller. If you relied on readable filenames for SEO purposes then this may impact you. As a workaround:
- Let this happen and utilize `alt` attribute instead to help crawlers understand what the image contains
- Update `src` attribute to be `{{ $asset['url'] }}`. This will point to the original, source asset. However, as this is original asset - any image manipulations you have set will not happen on this image.

This is low impact change because most modern browsers will not hit the URL provided in `<img>` tag if `<source>` tags are provided.

## Very low impact changes

If you had made any changes or used objects in a way that are out of scope of the provided README in this project then please review the commits manually.