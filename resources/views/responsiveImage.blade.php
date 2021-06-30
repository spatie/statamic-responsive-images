@once
    <script>
        window.responsiveResizeObserver = new ResizeObserver((entries) => {
            entries.forEach(entry => {
                const imgWidth = entry.target.getBoundingClientRect().width;
                entry.target.parentNode.querySelectorAll('source').forEach((source) => {
                    source.sizes = Math.ceil(imgWidth / window.innerWidth * 100) + 'vw';
                });
            });
        });
    </script>
@endonce

<picture>
    @foreach (($sources ?? []) as $source)
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
        alt="{{ $asset['title'] }}"
        @isset($width) width="{{ $width }}" @endisset
        @isset($height) height="{{ $height }}" @endisset
        @isset($sources)
        onload="
            this.onload=null;
            window.responsiveResizeObserver.observe(this);
        "
        @endisset
    >
</picture>
