<template>
    <div class="flex gap-2 text-2xs">
        <a
            v-for="asset in assets"
            :key="asset.id"
            :href="asset.url"
            target="_blank"
            :title="asset.breakpoint"
            class="-my-1 h-8 max-w-3xs"
        >
            <img
                v-if="asset.thumbnail"
                :src="asset.thumbnail"
                class="mx-auto max-h-8 max-w-full rounded-sm"
                loading="lazy"
                :draggable="false"
            />
            <img
                v-else-if="asset.is_svg"
                :src="asset.url"
                class="mx-auto h-8 max-w-full rounded-sm"
                loading="lazy"
                :draggable="false"
            />
            <file-icon
                v-else
                :extension="asset.extension"
                class="h-8 w-8 rounded-sm p-px"
            />
        </a>
        <span
            v-if="value.total > 6"
            class="-my-1 flex h-8 min-w-8 items-center justify-center px-1.5 font-mono text-gray-600 dark:text-gray-400"
        >
            +&thinsp;{{ value.total - 5 }}
        </span>
    </div>
</template>

<script>
import { IndexFieldtypeMixin } from '@statamic/cms';

export default {
    mixins: [IndexFieldtypeMixin],

    computed: {
        assets() {
            return this.value?.assets?.slice(0, 5) ?? [];
        },
    },
};
</script>
