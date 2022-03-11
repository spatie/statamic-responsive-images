<template>
  <div class="text-2xs flex">
    <a
        v-for="asset in assets"
        :key="asset.id"
        :href="asset.url"
        target="_blank"
        :title="asset.breakpoint"
    >
      <asset-thumbnail :asset="asset" class="h-8 max-w-3xs -my-sm" />
    </a>
  </div>
</template>

<script>
import AssetThumbnail from '../../vendor/statamic/cms/resources/js/components/assets/Browser/Thumbnail.vue';

export default {
  mixins: [IndexFieldtype],
  components: {AssetThumbnail},

  computed: {
    assets() {
      if (this.value) {
        return this.value.reverse();
      }

      return [];
    }
  }
};
</script>
