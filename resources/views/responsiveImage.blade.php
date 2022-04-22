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
    @foreach (($sources ?? []) as $source)
        @isset($source['srcSetAvif'])
            <source
                type="image/avif"
                @isset($source['media']) media="{{ $source['media'] }}" @endisset
                srcset="{{ $source['srcSetAvif'] }}"
                @if($includePlaceholder ?? false) sizes="1px" @endif
            >
        @endisset
        @isset($source['srcSetWebp'])
            <source
                type="image/webp"
                @isset($source['media']) media="{{ $source['media'] }}" @endisset
                srcset="{{ $source['srcSetWebp'] }}"
                @if($includePlaceholder ?? false) sizes="1px" @endif
            >
        @endisset

        <source
            @isset($source['media']) media="{{ $source['media'] }}" @endisset
            srcset="{{ $source['srcSet'] }}"
            @if($includePlaceholder ?? false) sizes="1px" @endif
        >
    @endforeach

    <img
        {!! $attributeString ?? '' !!}
        src="{{ $src }}"
        @unless (\Illuminate\Support\Str::contains($attributeString, 'alt'))
        alt="{{ $asset['title'] }}"
        @endunless
        @isset($width) width="{{ $width }}" @endisset
        @isset($height) height="{{ $height }}" @endisset
        @isset($sources)
        data-statamic-responsive-images
        @endisset
    >
</picture>
