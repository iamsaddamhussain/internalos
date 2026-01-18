<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    workspace: Object,
    automations: Object,
    collections: Array,
});

const selectedCollection = ref(null);
const showCollectionMenu = ref(false);

const filterByCollection = () => {
    router.get(route('automations.index'), {
        collection_id: selectedCollection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleAutomation = (automation) => {
    router.post(route('automations.toggle', automation.id), {}, {
        preserveState: true,
    });
};
</script>

<template>
    <Head title="Automations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Automations
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Automate workflows with triggers, conditions, and actions
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filter and Actions -->
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <select
                            v-model="selectedCollection"
                            @change="filterByCollection"
                            class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option :value="null">All Collections</option>
                            <option v-for="collection in collections" :key="collection.id" :value="collection.id">
                                {{ collection.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div v-if="collections.length === 1">
                        <Link
                            :href="route('automations.create', collections[0].id)"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            + Create Automation
                        </Link>
                    </div>
                    <div v-else-if="collections.length > 1" class="relative inline-block text-left">
                        <button
                            @click="showCollectionMenu = !showCollectionMenu"
                            type="button"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            + Create Automation
                            <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            v-show="showCollectionMenu"
                            @click.away="showCollectionMenu = false"
                            class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50"
                        >
                            <div class="py-1">
                                <Link
                                    v-for="collection in collections"
                                    :key="collection.id"
                                    :href="route('automations.create', collection.id)"
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    {{ collection.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Automations List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div v-if="automations.data.length === 0" class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No automations</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ collections.length === 0 ? 'Create a collection first, then add automations to it.' : 'Get started by creating an automation using the button above.' }}
                        </p>
                    </div>

                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="automation in automations.data"
                            :key="automation.id"
                            class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <Link
                                            :href="route('automations.show', automation.id)"
                                            class="text-lg font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400"
                                        >
                                            {{ automation.name }}
                                        </Link>
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                automation.is_active
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                            ]"
                                        >
                                            {{ automation.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>

                                    <p v-if="automation.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        {{ automation.description }}
                                    </p>

                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                        <span>Collection: {{ automation.collection.name }}</span>
                                        <span>•</span>
                                        <span>{{ automation.triggers.length }} trigger(s)</span>
                                        <span>•</span>
                                        <span>{{ automation.conditions.length }} condition(s)</span>
                                        <span>•</span>
                                        <span>{{ automation.actions.length }} action(s)</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 ml-4">
                                    <button
                                        @click="toggleAutomation(automation)"
                                        :class="[
                                            'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                                            automation.is_active
                                                ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                                : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800'
                                        ]"
                                    >
                                        {{ automation.is_active ? 'Deactivate' : 'Activate' }}
                                    </button>

                                    <Link
                                        :href="route('automations.edit', automation.id)"
                                        class="px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="automations.links && automations.links.length > 3" class="mt-6 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Link
                            v-for="link in automations.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded-md',
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700',
                                !link.url && 'opacity-50 cursor-not-allowed'
                            ]"
                            :disabled="!link.url"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
