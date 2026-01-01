<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DynamicForm from '@/Components/DynamicForm.vue';

const props = defineProps({
    collection: Object,
    record: Object,
    relatedData: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({ ...props.record.data });

const submit = () => {
    form.put(route('records.update', { collection: props.collection.id, record: props.record.id }), {
        onSuccess: () => router.visit(route('collections.show', props.collection.id)),
    });
};
</script>

<template>
    <Head :title="`Edit ${collection.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <span class="text-3xl mr-3">{{ collection.icon }}</span>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit {{ collection.name }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <DynamicForm
                            :schema="collection.schema"
                            :form="form"
                            :relatedData="relatedData"
                        />

                        <div class="flex justify-end space-x-3 mt-6">
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
                                {{ form.processing ? 'Updating...' : 'Update' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
