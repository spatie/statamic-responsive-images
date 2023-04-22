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

<style>
.sri__container {
    position: relative;
}

.sri__container:before {
    display: block;
    content: "";
    width: 100%;
    padding-top: calc((var(--height) / var(--width)) * 100%);
}

.sri__container img {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    object-fit: cover;
    width: 100%;
    height: 100%;
}
</style>

@php
    $uniqueClassName = uniqid('sri__');
@endphp

<style>
@foreach($breakpoints->reverse() ?? [] as $breakpoint)
    @if($breakpoint->label === "default")
        .{{ $uniqueClassName }}:before {
        --width: {{ $breakpoint->getAspectRatioDimensions()->width }};
        --height: {{ $breakpoint->getAspectRatioDimensions()->height }};
    }
    @else
        @media screen and {{ $breakpoint->getMediaString() }} {
            .{{ $uniqueClassName }}:before {
                --width: {{ $breakpoint->getAspectRatioDimensions()->width }};
                --height: {{ $breakpoint->getAspectRatioDimensions()->height }};
            }
        }
    @endif
@endforeach
</style>

<div class="sri__container {{ $uniqueClassName }}">
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
                        @if($source->getWidth()) width="{{ $source->getWidth() }}" @endif
                        @if($source->getHeight()) height="{{ $source->getHeight() }}" @endif
                    >
                @endif
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
</div>
