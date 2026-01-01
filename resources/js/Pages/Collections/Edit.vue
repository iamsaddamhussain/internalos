<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    collection: Object,
    collections: {
        type: Array,
        default: () => [],
    },
});

// Track original field IDs to disable type changes
const originalFieldIds = props.collection.schema.fields.map(f => f.id);

const form = useForm({
    name: props.collection.name,
    description: props.collection.description || '',
    icon: props.collection.icon || '📄',
    fields: props.collection.schema.fields.map(field => ({
        ...field,
        optionsString: field.options ? field.options.join(', ') : '',
        isExisting: true, // Mark as existing field
        default_value: field.default_value ?? null,
    })),
});

const fieldTypes = [
    { value: 'text', label: 'Text' },
    { value: 'textarea', label: 'Textarea' },
    { value: 'email', label: 'Email' },
    { value: 'number', label: 'Number' },
    { value: 'date', label: 'Date' },
    { value: 'checkbox', label: 'Checkbox' },
    { value: 'select', label: 'Select' },
    { value: 'relation', label: 'Relation' },
];

const addField = () => {
    form.fields.push({
        id: `field_${Date.now()}`,
        label: '',
        type: 'text',
        required: false,
        options: [],
        optionsString: '',
        isExisting: false, // Mark as new field
        default_value: null,
    });
};

const removeField = (index) => {
    form.fields.splice(index, 1);
};

const updateFieldOptions = (field, value) => {
    field.optionsString = value;
    field.options = value
        .split(',')
        .map(option => option.trim())
        .filter(option => option.length > 0);
};

const submit = () => {
    // Parse options before submitting
    form.fields.forEach(field => {
        if (field.type === 'select' && field.optionsString) {
            field.options = field.optionsString
                .split(',')
                .map(option => option.trim())
                .filter(option => option.length > 0);
        }
    });
    form.put(route('collections.update', props.collection.id));
};
</script>

<template>
    <Head title="Edit Collection" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Collection
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Collection Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- Icon -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Icon (Emoji)
                                </label>
                                <input
                                    v-model="form.icon"
                                    type="text"
                                    placeholder="📄"
                                    maxlength="2"
                                    class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-center text-3xl py-3"
                                />
                            </div>
                            
                            <!-- Name & Description -->
                            <div class="md:col-span-10 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Collection Name *
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="e.g., Employees, Tasks, Projects"
                                        class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="text-red-600 dark:text-red-400 text-sm mt-1">
                                        {{ form.errors.name }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Description
                                    </label>
                                    <input
                                        v-model="form.description"
                                        type="text"
                                        placeholder="Brief description of this collection (optional)"
                                        class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Fields -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                                Fields
                            </label>

                            <div v-if="form.fields.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                <p class="text-gray-500 dark:text-gray-400 mb-3">No fields yet. Add your first field to get started.</p>
                                <button
                                    type="button"
                                    @click="addField"
                                    class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600"
                                >
                                    Add Your First Field
                                </button>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(field, index) in form.fields"
                                    :key="field.id"
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                                >
                                    <div class="flex justify-between items-start mb-3">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Field {{ index + 1 }}</h4>
                                        <button
                                            type="button"
                                            @click="removeField(index)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm"
                                        >
                                            Remove
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Field Label *
                                            </label>
                                            <input
                                                v-model="field.label"
                                                type="text"
                                                placeholder="e.g., Name"
                                                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                required
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Field Type *
                                            </label>
                                            <select
                                                v-model="field.type"
                                                :disabled="field.isExisting"
                                                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <option v-for="type in fieldTypes" :key="type.value" :value="type.value">
                                                    {{ type.label }}
                                                </option>
                                            </select>
                                            <p v-if="field.isExisting" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Field type cannot be changed for existing fields
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="flex items-center">
                                            <input
                                                v-model="field.required"
                                                type="checkbox"
                                                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Required field</span>
                                        </label>
                                    </div>

                                    <!-- Default Value for Checkbox -->
                                    <div v-if="field.type === 'checkbox'" class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Default Value
                                        </label>
                                        <div class="flex items-center space-x-4">
                                            <label class="flex items-center">
                                                <input
                                                    v-model="field.default_value"
                                                    type="radio"
                                                    :value="true"
                                                    class="border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                />
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Checked by default</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input
                                                    v-model="field.default_value"
                                                    type="radio"
                                                    :value="false"
                                                    class="border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                />
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Unchecked by default</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Options for Select type -->
                                    <div v-if="field.type === 'select'" class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Options (comma-separated)
                                        </label>
                                        <input
                                            v-model="field.optionsString"
                                            @blur="updateFieldOptions(field, field.optionsString)"
                                            type="text"
                                            placeholder="e.g., Manager, Developer, Designer"
                                            class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                        />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Separate options with commas</p>
                                    </div>

                                    <!-- Relation Collection Selector -->
                                    <div v-if="field.type === 'relation'" class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Related Collection *
                                        </label>
                                        <select
                                            v-model="field.relation_collection_id"
                                            class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                            required
                                        >
                                            <option value="">Select a collection</option>
                                            <option 
                                                v-for="col in collections.filter(c => c.id !== collection.id)" 
                                                :key="col.id" 
                                                :value="col.id"
                                            >
                                                {{ col.icon }} {{ col.name }}
                                            </option>
                                        </select>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Link records from another collection</p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="addField"
                                    class="w-full py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 hover:border-indigo-500 hover:text-indigo-500 dark:hover:border-indigo-400 dark:hover:text-indigo-400"
                                >
                                    + Add Another Field
                                </button>
                            </div>

                            <div v-if="form.errors.fields" class="text-red-600 dark:text-red-400 text-sm mt-1">
                                {{ form.errors.fields }}
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a
                                :href="route('collections.show', collection.id)"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Updating...' : 'Update Collection' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
