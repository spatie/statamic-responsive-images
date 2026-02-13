<script setup>
import { Fieldtype } from '@statamic/cms';
import { PublishContainer, PublishFieldsProvider, PublishFields, injectPublishContext } from '@statamic/cms/ui';
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

const parentContext = injectPublishContext();

const errors = computed(() => {
    const allErrors = parentContext?.errors?.value || {};
    const prefix = props.handle + '.';
    const result = {};

    Object.entries(allErrors).forEach(([key, value]) => {
        if (key.startsWith(prefix)) {
            result[key.slice(prefix.length)] = value;
        }
    });

    return result;
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 dark:border-dark-900 rounded-lg border">
        <PublishContainer
            :name="publishContainerName"
            :blueprint="blueprint"
            :model-value="props.value"
            :meta="publishMeta"
            :errors="errors"
            :track-dirty-state="false"
            @update:model-value="update"
        >
            <PublishFieldsProvider :fields="fields">
                <PublishFields class="px-4 py-4" />
            </PublishFieldsProvider>
        </PublishContainer>
    </div>
</template>
