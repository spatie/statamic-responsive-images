<template>
  <div class="publish-fields">
    <publish-container
        :name="name"
        :values="value"
        :meta="meta"
        :errors="errors"
        :trackDirtyState="false"
        @updated="updated($event)"
    >
      <div slot-scope="{ setFieldValue, setFieldMeta }">
        <publish-fields
            :fields="fields"
            :name-prefix="name"
            @updated="setFieldValue"
            @meta-updated="setFieldMeta"
        />
      </div>
    </publish-container>
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
    },

    storeState() {
      return this.$store.state.publish['base'] || {};
    },

    errors() {
      let errors = this.storeState.errors || [];

      Object.keys(errors).map((key) => {
        const newKey = key.replace(this.handle + '.', '');
        errors[newKey] = errors[key];
        delete errors[key];
      });

      return Object.assign({}, errors);
    },
  },

  methods: {
    updated(data) {
      const value = Object.assign({}, data);
      this.update(value);
    },
  },
};
</script>
