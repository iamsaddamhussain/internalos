<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DynamicTable from '@/Components/DynamicTable.vue';

const props = defineProps({
    collection: Object,
    records: Array,
    userRole: Object,
    canCreate: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
    relatedData: {
        type: Object,
        default: () => ({}),
    },
});

const deleteRecord = (record) => {
    if (confirm('Are you sure you want to delete this record?')) {
        router.delete(route('records.destroy', [props.collection.id, record.id]));
    }
};
</script>

<template>
    <Head :title="collection.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">{{ collection.icon }}</span>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {{ collection.name }}
                    </h2>
                    <Link
                        v-if="canEdit"
                        :href="route('collections.edit', collection.id)"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        ✏️ Edit Collection
                    </Link>
                </div>
                <Link
                    v-if="canCreate"
                    :href="route('records.create', collection.id)"
                    class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600"
                >
                    + Add {{ collection.name }}
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <DynamicTable
                        :schema="collection.schema"
                        :records="records"
                        :canEdit="canEdit"
                        :canDelete="canDelete"
                        :relatedData="relatedData"
                        @edit="(record) => router.visit(route('records.edit', { collection: collection.id, record: record.id }))"
                        @delete="deleteRecord"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
