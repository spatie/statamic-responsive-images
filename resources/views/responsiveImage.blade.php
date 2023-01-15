@once
    <script>
        window.addEventListener('load', function () {
            window.responsiveResizeObserver = new ResizeObserver((entries) => {
                entries.forEach(entry => {
                    const imgWidth = entry.target.getBoundingClientRect().width;
                    entry.target.parentNode.querySelectorAll('source').forEach((source) => {
                        source.sizes = Math.ceil(imgWidth / window.innerWidth * 100) + 'vw';
                    });
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
        @foreach($breakpoint->getSources() ?? [] as $source)
            <source
                @if($type = $source->getMimeType()) type="{{ $type }}" @endif
                @if($media = $source->getMediaString()) media="{{ $media }}" @endif
                srcset="{{ $source->getSrcSet() }}"
                @if($includePlaceholder ?? false) sizes="1px" @endif
            >
        @endforeach
    @endforeach

    <img
        {!! $attributeString ?? '' !!}
        src="{{ $src }}"
        @unless (\Illuminate\Support\Str::contains($attributeString, 'alt'))
        alt="{{ $asset['alt'] ?? $asset['title'] }}"
        @endunless
        @isset($width) width="{{ $width }}" @endisset
        @isset($height) height="{{ $height }}" @endisset
        @if($hasSources)
        data-statamic-responsive-images
        @endif
    >
</picture>
