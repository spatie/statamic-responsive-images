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

@once
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
@endonce

<div class="sri__container {{ $uniqueId }}">
    <style>
        @if(isset($breakpoints))
            @foreach($breakpoints->reverse() ?? [] as $breakpoint)
                @if($breakpoint->label === "default")
                    .{{ $uniqueId }}:before {
                        --width: {{ $breakpoint->getRatioDimensions()->width }};
                        --height: {{ $breakpoint->getRatioDimensions()->height }};
                    }
                @else
                    @media screen and {{ $breakpoint->getMediaString() }} {
                        .{{ $uniqueId }}:before {
                            --width: {{ $breakpoint->getRatioDimensions()->width }};
                            --height: {{ $breakpoint->getRatioDimensions()->height }};
                        }
                    }
                @endif
            @endforeach
        @elseif(!isset($breakpoints) && in_array($asset['extension'], ['svg', 'gif']))
            .{{ $uniqueId }}:before {
                --width: {{ $width }};
                --height: {{ $height }};
            }
        @endif
    </style>
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
