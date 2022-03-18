<template>
  <div class="publish-fields">
    <publish-container
        name="responsive"
        :values="value"
        :meta="meta"
        :errors="errors"
        @updated="update($event)"
    >
      <div slot-scope="{ setFieldValue, setFieldMeta }">
        <publish-fields
            :fields="fields"
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
  },
};
</script>
