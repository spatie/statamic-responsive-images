@once
    <script>
        window.addEventListener('load', function () {
            window.responsiveResizeObserver = new ResizeObserver((entries) => {
                requestAnimationFrame(() => {
                    const current = entry.target.currentSrc;
                    const imgWidth = entry.target.getBoundingClientRect().width;
                    let matchedSource = null;
                    entry.target.parentNode.querySelectorAll('source').forEach((source) => {
                        source.sizes = Math.ceil(imgWidth / window.innerWidth * 100) + 'vw';
                        const srcset = source.getAttribute('srcset');
                        if (!srcset) return;

                        // find out which <source> tag is displayed
                        srcset.split(',').forEach(candidate => {
                            const [url] = candidate.trim().split(/\s+/);
                            if (current.includes(url)) {
                                matchedSource = source;
                            }
                        });
                    });

                    if (matchedSource) {
                        const alt = matchedSource.dataset.alt;
                        if (alt) entry.target.alt = alt;
                    }
                });
            });

            document.querySelectorAll('[data-statamic-responsive-images]').forEach(responsiveImage => {
                responsiveResizeObserver.onload = null;
                responsiveResizeObserver.observe(responsiveImage);
            });
        });
    </script>
@endonce

<picture>
    @foreach (($breakpoints ?? []) as $breakpoint)
        @foreach($breakpoint->sources() ?? [] as $source)
            @php
                $srcSet = $source->getSrcset();
            @endphp

            @if($srcSet !== null)
                <source
                    @if($type = $source->getMimeType()) type="{{ $type }}" @endif
                    @if($media = $source->getMediaString()) media="{{ $media }}" @endif
                    srcset="{{ $srcSet }}"
                    @if($includePlaceholder ?? false) sizes="1px" @endif
                    @if(is_string($breakpoint->asset->alt) && $breakpoint->asset->alt !== '') data-alt="{{ $breakpoint->asset->alt }}" @endif
                >
            @endif
        @endforeach
    @endforeach

    <img
        {!! $attributeString ?? '' !!}
        src="{{ $src }}"
        @unless (\Illuminate\Support\Str::contains($attributeString, 'alt'))
        alt="{{ (string) $asset['alt'] ?: (string) $asset['title'] }}"
        @endunless
        @isset($width) width="{{ $width }}" @endisset
        @isset($height) height="{{ $height }}" @endisset
        @if($hasSources)
        data-statamic-responsive-images
        @endif
    >
</picture>
