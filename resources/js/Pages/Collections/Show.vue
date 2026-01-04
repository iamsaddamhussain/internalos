<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DynamicTable from '@/Components/DynamicTable.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    collection: Object,
    records: Object,
    userRole: Object,
    canCreate: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
    relatedData: {
        type: Object,
        default: () => ({}),
    },
    filters: Object,
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || props.collection.per_page || 10);

// Simple debounce function
let timeout = null;
const performSearch = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('collections.show', props.collection.id), {
            search: search.value,
            per_page: perPage.value,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

watch([search, perPage], () => {
    performSearch();
});

const deleteRecord = (record) => {
    if (confirm('Are you sure you want to delete this record?')) {
        router.delete(route('records.destroy', [props.collection.id, record.id]));
    }
};

const exportRecords = () => {
    window.location.href = route('collections.export', props.collection.id);
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
                <!-- Search and Export Bar -->
                <div v-if="collection.enable_search || collection.enable_export" class="mb-4 flex flex-wrap items-center justify-between gap-4">
                    <!-- Search -->
                    <div v-if="collection.enable_search" class="flex-1 min-w-64">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search records..."
                            class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        />
                    </div>

                    <!-- Export Button -->
                    <button
                        v-if="collection.enable_export"
                        @click="exportRecords"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 uppercase tracking-widest"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </button>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <DynamicTable
                        :schema="collection.schema"
                        :records="records.data"
                        :canEdit="canEdit"
                        :canDelete="canDelete"
                        :relatedData="relatedData"
                        @view="(record) => router.visit(route('records.show', { collection: collection.id, record: record.id }))"
                        @edit="(record) => router.visit(route('records.edit', { collection: collection.id, record: record.id }))"
                        @delete="deleteRecord"
                    />
                </div>

                <!-- Pagination -->
                <div v-if="records.last_page > 1" class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-700 dark:text-gray-300">Per page:</label>
                        <select
                            v-model.number="perPage"
                            class="border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                        >
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            Showing {{ records.from }} to {{ records.to }} of {{ records.total }} records
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <template v-for="link in records.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm rounded-md',
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                ]"
                                v-html="link.label"
                                preserve-scroll
                            />
                            <span
                                v-else
                                :class="'px-3 py-2 text-sm rounded-md bg-gray-100 dark:bg-gray-900 text-gray-400 dark:text-gray-600 cursor-not-allowed'"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
