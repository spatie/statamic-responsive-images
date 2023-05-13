<template>
  <div class="responsive-field">
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
  </div>
</template>

<script>
import {generateId} from "./helpers";

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

<style scoped>
@container (max-width: 125px)  {
  .responsive-field .assets-fieldtype .assets-fieldtype-picker {
    flex-direction: row;
  }

  .responsive-field .assets-fieldtype .assets-fieldtype-picker .btn.btn-with-icon {
    white-space: nowrap;
    overflow: hidden;
  }
}

@container (max-width: 148px)  {
  .responsive-field .assets-fieldtype .assets-fieldtype-picker .btn.btn-with-icon svg {
    display: none;
  }
}

@container (max-width: 265px)  {
  .responsive-field .assets-fieldtype .assets-fieldtype-drag-container .asset-table-listing td.w-24 {
    display: none;
  }
}
</style>