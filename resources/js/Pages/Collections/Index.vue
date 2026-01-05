<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    collections: Array,
    userRole: Object,
    canCreate: Boolean,
    currentPlan: Object,
});

const collectionProgress = computed(() => {
    if (!props.currentPlan) return null;
    const current = props.currentPlan.current.collections;
    const max = props.currentPlan.limits.max_collections;
    if (max === -1) return { percentage: 0, text: `${current} collections` };
    return {
        percentage: (current / max) * 100,
        text: `${current}/${max} collections`,
        isNearLimit: current >= max * 0.8,
        isAtLimit: current >= max,
    };
});
</script>

<template>
    <Head title="Collections" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Collections
                    </h2>
                    <div v-if="currentPlan" class="mt-1 flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                            {{ currentPlan.name }}
                        </span>
                        <span v-if="collectionProgress" class="text-xs text-gray-600 dark:text-gray-400">
                            {{ collectionProgress.text }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        v-if="collectionProgress && collectionProgress.isNearLimit && !collectionProgress.isAtLimit"
                        href="#"
                        class="text-sm text-amber-600 dark:text-amber-400 hover:underline"
                    >
                        ⚠️ Approaching limit - Upgrade
                    </Link>
                    <Link
                        v-if="canCreate"
                        :href="route('collections.create')"
                        :class="[
                            'px-4 py-2 rounded-md font-medium transition',
                            collectionProgress && collectionProgress.isAtLimit
                                ? 'bg-gray-400 text-gray-700 cursor-not-allowed opacity-50'
                                : 'bg-indigo-600 dark:bg-indigo-500 text-white hover:bg-indigo-700 dark:hover:bg-indigo-600'
                        ]"
                        :disabled="collectionProgress && collectionProgress.isAtLimit"
                    >
                        ➕ New Collection
                    </Link>
                </div>
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
