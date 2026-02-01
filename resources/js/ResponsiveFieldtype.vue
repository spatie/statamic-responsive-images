<script setup>
import { Fieldtype } from '@statamic/cms';
import { PublishContainer, PublishFieldsProvider as FieldsProvider, PublishFields as Fields } from '@statamic/cms/ui';
import { computed } from 'vue';
import { generateId } from './helpers';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);

defineExpose(expose);

const publishContainerName = computed(() => props.handle + '.' + generateId(10));

const fields = computed(() => {
    return Object.values(props.meta.fields).map(field => ({
        handle: field.handle,
        ...field.field
    }));
});

const blueprint = computed(() => ({
    tabs: [{ fields: fields.value }],
}));

const publishMeta = computed(() => props.meta.meta || {});

const errors = computed(() => {
    const storeErrors = Statamic.$store?.state?.publish?.base?.errors || {};
    const result = {};

    Object.keys(storeErrors).forEach(key => {
        const newKey = key.replace(props.handle + '.', '');
        result[newKey] = storeErrors[key];
    });

    return result;
});
</script>

<template>
    <PublishContainer
        :name="publishContainerName"
        :blueprint="blueprint"
        :model-value="props.value"
        :meta="publishMeta"
        :errors="errors"
        :track-dirty-state="false"
        @update:model-value="update"
    >
        <FieldsProvider :fields="fields">
            <Fields />
        </FieldsProvider>
    </PublishContainer>
</template>
