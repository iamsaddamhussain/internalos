<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    collections: Array,
    userRole: Object,
    canCreate: Boolean,
});
</script>

<template>
    <Head title="Collections" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Collections
                </h2>
                <Link
                    v-if="canCreate"
                    :href="route('collections.create')"
                    class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600"
                >
                    ➕ New Collection
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="collections.length === 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No collections yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ canCreate ? 'Create your first collection to start managing data' : 'No collections available to view' }}</p>
                    <Link
                        v-if="canCreate"
                        :href="route('collections.create')"
                        class="inline-block px-6 py-3 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600"
                    >
                        Create Collection
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link
                        v-for="collection in collections"
                        :key="collection.id"
                        :href="route('collections.show', collection.id)"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md dark:hover:shadow-gray-900/50 transition-shadow cursor-pointer"
                    >
                        <div class="flex items-center mb-3">
                            <span class="text-3xl mr-3">{{ collection.icon }}</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ collection.name }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ collection.schema.fields.length }} fields
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                            Created {{ new Date(collection.created_at).toLocaleDateString() }}
                        </p>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
