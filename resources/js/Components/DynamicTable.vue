<script setup>
import { computed } from 'vue';

const props = defineProps({
    schema: Object,
    records: Array,
    canEdit: {
        type: Boolean,
        default: true,
    },
    canDelete: {
        type: Boolean,
        default: true,
    },
    relatedData: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['edit', 'delete']);

const fields = computed(() => props.schema.fields || []);

const formatValue = (value, type, fieldId) => {
    if (value === null || value === undefined) return '-';
    
    switch (type) {
        case 'checkbox':
            return value ? '✓' : '✗';
        case 'date':
            return new Date(value).toLocaleDateString();
        case 'number':
            return Number(value).toLocaleString();
        case 'relation':
            // Find the related record display name
            const relatedRecord = props.relatedData[fieldId]?.records?.find(r => r.id === value);
            return relatedRecord ? relatedRecord.display : '-';
        default:
            return value;
    }
};
</script>

<template>
    <div class="overflow-x-auto">
        <table v-if="records.length > 0" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th
                        v-for="field in fields"
                        :key="field.id"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                        {{ field.label }}
                    </th>
                    <th v-if="canEdit || canDelete" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="record in records" :key="record.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td
                        v-for="field in fields"
                        :key="field.id"
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100"
                    >
                        {{ formatValue(record.data[field.id], field.type, field.id) }}
                    </td>
                    <td v-if="canEdit || canDelete" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <button
                            v-if="canEdit"
                            @click="emit('edit', record)"
                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                        >
                            Edit
                        </button>
                        <button
                            v-if="canDelete"
                            @click="emit('delete', record)"
                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-else class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">No records yet</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm">Click the "Add" button above to create your first record</p>
        </div>
    </div>
</template>
