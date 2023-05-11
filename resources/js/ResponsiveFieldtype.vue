<template>
    <publish-container
        :name="publishContainerName"
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
</template>

<script>
import { generateId } from "./helpers";

export default {
  mixins: [Fieldtype],

  computed: {
    publishContainerName() {
      return this.$props.handle + '.' + generateId(10)
    },

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
