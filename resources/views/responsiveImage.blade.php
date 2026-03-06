@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.responsiveResizeObserver = new ResizeObserver((entries) => {
                entries.forEach(({ target, contentRect }) => {
                    const vw = Math.ceil(contentRect.width / window.innerWidth * 100) + 'vw';

                    target.parentNode.querySelectorAll('source').forEach(source => source.sizes = vw);
                });
            });

            document.querySelectorAll('[data-statamic-responsive-images]').forEach(img => {
                window.responsiveResizeObserver.observe(img);
            });

            @if(request()->isLivePreview())
                new MutationObserver(mutations => {
                    for (const { addedNodes } of mutations) {
                        for (const node of addedNodes) {
                            if (node.nodeType !== Node.ELEMENT_NODE) {
                                continue;
                            }

                            if (node.hasAttribute('data-statamic-responsive-images')) {
                                window.responsiveResizeObserver.observe(node);
                            }

                            node.querySelectorAll('[data-statamic-responsive-images]').forEach(img => {
                                window.responsiveResizeObserver.observe(img);
                            });
                        }
                    }
                }).observe(document.body, { childList: true, subtree: true });
            @endif
        });
    </script>
@endonce

<picture>
    @foreach(($breakpoints ?? []) as $breakpoint)
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
        @unless(\Illuminate\Support\Str::contains($attributeString, 'alt'))
        alt="{{ (string) $asset['alt'] ?: (string) $asset['title'] }}"
        @endunless
        @isset($width) width="{{ $width }}" @endisset
        @isset($height) height="{{ $height }}" @endisset
        @if($hasSources)
        data-statamic-responsive-images
        @endif
    >
</picture>
