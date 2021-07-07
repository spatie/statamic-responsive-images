<template>
  <div class="publish-fields">
    <publish-field
        v-for="field in fields"
        :key="field.handle"
        :config="field"
        :value="getValue(field)"
        :meta="meta.meta[field.handle]"
        :read-only="isReadOnly"
        class="form-group bg-white border-t border-b"
        :class="field.type === 'section' ? 'mt-px -mb-px' : ''"
        @meta-updated="metaUpdated(field.handle, $event)"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
        @input="updateKey(field.handle, $event)"
    />
  </div>
</template>

<script>

export default {
  mixins: [Fieldtype],

  computed: {
    fields() {
      return _.chain(this.meta.fields)
          .map(field => {
            return {
              handle: field.handle,
              ...field.field
            };
          })
          .values()
          .value();
    }
  },

  methods: {
    updateKey(handle, value) {
      let responsiveValue = this.value;
      Vue.set(responsiveValue, handle, value);
      this.update(responsiveValue);
    },

    getValue(field) {
      if (field.type === 'assets') {
        return this.value[field.handle] || [];
      }

      return this.value[field.handle];
    }
  },
};
</script>
