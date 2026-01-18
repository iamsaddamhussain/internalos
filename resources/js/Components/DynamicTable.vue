<script setup>
import { computed } from 'vue';

const props = defineProps({
    schema: Object,
    records: Array,
    canView: {
        type: Boolean,
        default: true,
    },
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
    sortField: {
        type: String,
        default: 'created_at',
    },
    sortDirection: {
        type: String,
        default: 'desc',
    },
});

const emit = defineEmits(['view', 'edit', 'delete', 'sort']);

const fields = computed(() => props.schema.fields || []);

const isSortable = (field) => {
    // All field types except checkbox can be sorted
    return field.type !== 'checkbox';
};

const getSortIcon = (fieldId) => {
    if (props.sortField !== fieldId) {
        return null; // No icon when not sorted
    }
    return props.sortDirection === 'asc' ? '↑' : '↓';
};

const formatValue = (value, type, fieldId, field) => {
    if (value === null || value === undefined) return '-';
    
    switch (type) {
        case 'checkbox':
            return value ? '✓' : '✗';
        case 'date':
            return new Date(value).toLocaleDateString();
        case 'number':
            return Number(value).toLocaleString();
        case 'relation':
            // Check if multiple selection
            if (field.multiple && Array.isArray(value)) {
                const displayNames = value.map(id => {
                    const relatedRecord = props.relatedData[fieldId]?.records?.find(r => r.id === id);
                    return relatedRecord ? relatedRecord.display : id;
                });
                return displayNames.join(', ');
            }
            // Single selection
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
                        :class="[
                            'px-6 py-3 text-left text-xs font-medium uppercase tracking-wider',
                            isSortable(field) ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 select-none' : '',
                            sortField === field.id ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400'
                        ]"
                        @click="isSortable(field) ? emit('sort', field.id) : null"
                    >
                        <div class="flex items-center gap-2">
                            <span>{{ field.label }}</span>
                            <svg 
                                v-if="isSortable(field)" 
                                class="w-4 h-4 transition-opacity"
                                :class="sortField === field.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-50'"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path 
                                    v-if="sortField === field.id && sortDirection === 'asc'"
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    stroke-width="2" 
                                    d="M5 15l7-7 7 7"
                                />
                                <path 
                                    v-else-if="sortField === field.id && sortDirection === 'desc'"
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    stroke-width="2" 
                                    d="M19 9l-7 7-7-7"
                                />
                                <path 
                                    v-else
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    stroke-width="2" 
                                    d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                                />
                            </svg>
                        </div>
                    </th>
                    <th v-if="canView || canEdit || canDelete" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
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
                        {{ formatValue(record.data[field.id], field.type, field.id, field) }}
                    </td>
                    <td v-if="canView || canEdit || canDelete" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <button
                                v-if="canView"
                                @click="emit('view', record)"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View
                            </button>
                            <button
                                v-if="canEdit"
                                @click="emit('edit', record)"
                                class="inline-flex items-center px-3 py-1.5 border border-indigo-300 dark:border-indigo-600 rounded-md text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </button>
                            <button
                                v-if="canDelete"
                                @click="emit('delete', record)"
                                class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-600 rounded-md text-xs font-medium text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
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
