<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DynamicTable from '@/Components/DynamicTable.vue';
import Modal from '@/Components/Modal.vue';
import SearchableDropdown from '@/Components/SearchableDropdown.vue';
import MultiSelectDropdown from '@/Components/MultiSelectDropdown.vue';
import { ref, watch, computed } from 'vue';

const page = usePage();

const props = defineProps({
    collection: Object,
    records: Object,
    userRole: Object,
    canCreate: Boolean,
    canView: Boolean,
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

// Initialize fieldFilters with empty arrays for multiple relation fields
const initFieldFilters = () => {
    const filters = props.filters.filter || {};
    const initialized = { ...filters };
    
    // Ensure multiple relation fields are initialized as arrays
    props.collection.schema.fields.forEach(field => {
        if (field.type === 'relation' && field.multiple && !initialized[field.id]) {
            initialized[field.id] = [];
        }
    });
    
    return initialized;
};

const fieldFilters = ref(initFieldFilters());
const dateRanges = ref(props.filters.date_ranges || {});
const sortField = ref(props.filters.sort || 'created_at');
const sortDirection = ref(props.filters.direction || 'desc');
const showFilterModal = ref(false);

// Get filterable fields (select, relation, date, checkbox - exclude text/email as they're search fields)
const filterableFields = computed(() => {
    return props.collection.schema.fields.filter(field => 
        ['select', 'relation', 'date', 'checkbox'].includes(field.type)
    );
});

// Count active filters
const activeFiltersCount = computed(() => {
    const regularFilters = Object.keys(fieldFilters.value).filter(key => fieldFilters.value[key] !== '' && fieldFilters.value[key] !== null).length;
    const dateFilters = Object.keys(dateRanges.value).filter(key => {
        const range = dateRanges.value[key];
        return (range?.from && range.from !== '') || (range?.to && range.to !== '');
    }).length;
    return regularFilters + dateFilters;
});

// Simple debounce function
let timeout = null;
const performSearch = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('collections.show', props.collection.id), {
            search: search.value,
            per_page: perPage.value,
            filter: fieldFilters.value,
            date_ranges: dateRanges.value,
            sort: sortField.value,
            direction: sortDirection.value,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

watch([search, perPage, sortField, sortDirection], () => {
    performSearch();
}, { deep: true });

const handleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
};

const clearFilters = () => {
    fieldFilters.value = {};
    dateRanges.value = {};
    showFilterModal.value = false;
    performSearch();
};

const applyFilters = () => {
    showFilterModal.value = false;
    performSearch();
};

const deleteRecord = (record) => {
    if (confirm('Are you sure you want to delete this record?')) {
        router.delete(route('records.destroy', [props.collection.id, record.id]));
    }
};

const exportRecords = () => {
    window.location.href = route('collections.export', props.collection.id);
};

const showImportModal = ref(false);
const importFile = ref(null);
const isImporting = ref(false);
const importFileInput = ref(null);

const handleFileSelect = (event) => {
    importFile.value = event.target.files[0];
};

const downloadTemplate = () => {
    window.location.href = route('collections.template', props.collection.id);
};

const submitImport = () => {
    if (!importFile.value) {
        alert('Please select a file to import');
        return;
    }

    const formData = new FormData();
    formData.append('file', importFile.value);

    isImporting.value = true;

    router.post(route('collections.import', props.collection.id), formData, {
        forceFormData: true,
        preserveScroll: false,
        onSuccess: (page) => {
            showImportModal.value = false;
            importFile.value = null;
            if (importFileInput.value) {
                importFileInput.value.value = '';
            }
            isImporting.value = false;
        },
        onError: (errors) => {
            isImporting.value = false;
            console.error('Import errors:', errors);
        },
        onFinish: () => {
            isImporting.value = false;
        },
    });
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
                <!-- Flash Messages -->
                <div v-if="page.props.success || page.props.error" class="mb-4">
                    <div v-if="page.props.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg relative">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ page.props.success }}</span>
                        </div>
                    </div>
                    <div v-if="page.props.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg relative">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ page.props.error }}</span>
                        </div>
                    </div>
                    <div v-if="page.props.importErrors && page.props.importErrors.length > 0" class="mt-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200 px-4 py-3 rounded-lg">
                        <p class="font-semibold mb-2">Import Issues:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            <li v-for="(error, index) in page.props.importErrors.slice(0, 10)" :key="index">{{ error }}</li>
                            <li v-if="page.props.importErrors.length > 10" class="font-semibold">
                                ... and {{ page.props.importErrors.length - 10 }} more errors
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Search and Filter Bar -->
                <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-1">
                        <!-- Search -->
                        <div v-if="collection.enable_search" class="flex-1 min-w-64">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search records..."
                                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            />
                        </div>

                        <!-- Filter Button -->
                        <button
                            v-if="filterableFields.length > 0"
                            @click="showFilterModal = true"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 uppercase tracking-widest relative"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filters
                            <span 
                                v-if="activeFiltersCount > 0"
                                class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-indigo-600 rounded-full"
                            >
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Import & Export Buttons -->
                    <div class="flex gap-2">
                        <!-- Import Button -->
                        <button
                            v-if="canCreate && collection.enable_import"
                            @click="showImportModal = true"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 uppercase tracking-widest"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Import Excel
                        </button>
                        
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
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <DynamicTable
                        :schema="collection.schema"
                        :records="records.data"
                        :canView="canView"
                        :canEdit="canEdit"
                        :canDelete="canDelete"
                        :relatedData="relatedData"
                        :sortField="sortField"
                        :sortDirection="sortDirection"
                        @sort="handleSort"
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
        <!-- Filter Modal -->
        <Modal :show="showFilterModal" @close="showFilterModal = false" max-width="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filter Records</h2>
                    <button
                        @click="clearFilters"
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium"
                    >
                        Clear All
                    </button>
                </div>

                <div class="space-y-4 mb-6">
                    <div v-for="field in filterableFields" :key="field.id">
                        <label :for="`filter-${field.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ field.label }}
                        </label>
                        
                        <!-- Select Dropdown -->
                        <select
                            v-if="field.type === 'select'"
                            :id="`filter-${field.id}`"
                            v-model="fieldFilters[field.id]"
                            class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">All</option>
                            <option v-for="option in field.options" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                        
                        <!-- Checkbox/Boolean Filter -->
                        <select
                            v-else-if="field.type === 'checkbox'"
                            :id="`filter-${field.id}`"
                            v-model="fieldFilters[field.id]"
                            class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">All</option>
                            <option value="true">Active</option>
                            <option value="false">Inactive</option>
                        </select>
                        
                        <!-- Relation Dropdown -->
                        <div v-else-if="field.type === 'relation'">
                            <!-- Multiple Selection Relation -->
                            <MultiSelectDropdown
                                v-if="field.multiple"
                                v-model="fieldFilters[field.id]"
                                :options="relatedData[field.id]?.records || []"
                                :placeholder="`Filter by ${field.label.toLowerCase()}`"
                                displayKey="display"
                                valueKey="id"
                            />
                            <!-- Single Selection Relation -->
                            <SearchableDropdown
                                v-else
                                v-model="fieldFilters[field.id]"
                                :options="relatedData[field.id]?.records || []"
                                :placeholder="`Filter by ${field.label.toLowerCase()}`"
                                displayKey="display"
                                valueKey="id"
                                :allowEmpty="true"
                                emptyLabel="All"
                            />
                        </div>
                        
                        <!-- Date Range -->
                        <div v-else-if="field.type === 'date'" class="grid grid-cols-2 gap-3">
                            <div>
                                <label :for="`filter-${field.id}-from`" class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                                    From
                                </label>
                                <input
                                    :id="`filter-${field.id}-from`"
                                    v-model="(dateRanges[field.id] = dateRanges[field.id] || {}).from"
                                    type="date"
                                    class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>
                            <div>
                                <label :for="`filter-${field.id}-to`" class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                                    To
                                </label>
                                <input
                                    :id="`filter-${field.id}-to`"
                                    v-model="(dateRanges[field.id] = dateRanges[field.id] || {}).to"
                                    type="date"
                                    class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showFilterModal = false"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Import Modal -->
        <Modal :show="showImportModal" @close="showImportModal = false" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Import Records from Excel</h2>
                
                <div class="mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-1">Import Instructions</h3>
                                <ol class="text-sm text-blue-800 dark:text-blue-300 list-decimal list-inside space-y-1">
                                    <li>Download the template file to see the required format and example data</li>
                                    <li>Fill in your data following the format shown in the template</li>
                                    <li>Delete the instruction and example rows (rows 2-3) before importing</li>
                                    <li>Upload your completed Excel file</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button
                            @click="downloadTemplate"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700 focus:bg-green-700 active:bg-green-900"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Template with Examples
                        </button>
                    </div>

                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6">
                        <input
                            ref="importFileInput"
                            type="file"
                            accept=".xlsx,.xls"
                            @change="handleFileSelect"
                            class="w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/20 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/40"
                        />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Accepted formats: .xlsx, .xls (Max size: 10MB)
                        </p>
                        <p v-if="importFile" class="mt-2 text-sm text-green-600 dark:text-green-400">
                            Selected: {{ importFile.name }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showImportModal = false"
                        :disabled="isImporting"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitImport"
                        :disabled="!importFile || isImporting"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="isImporting">Importing...</span>
                        <span v-else>Import Records</span>
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
