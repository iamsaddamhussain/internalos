<script setup>
// This component reuses the Create component for editing
import Create from './Create.vue';

defineProps({
    workspace: Object,
    automation: Object,
    collection: Object,
    fields: Array,
});
</script>

<template>
    <Create
        :workspace="workspace"
        :automation="automation"
        :collection="collection"
        :fields="fields"
    />
</template>
