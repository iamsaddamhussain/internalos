<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    stats: Object,
    recentCollections: Array,
});

const page = usePage();

const workspaces = computed(() => page.props.workspaces || []);
const currentWorkspace = computed(() => page.props.currentWorkspace || null);
const userRole = computed(() => page.props.userRole || null);

const canCreate = computed(() => {
    if (!userRole.value) return false;
    return ['owner', 'admin', 'editor'].includes(userRole.value.slug);
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ currentWorkspace ? currentWorkspace.name : 'Dashboard' }}
                </h2>
                <Link 
                    :href="route('workspaces.create')"
                    class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600"
                >
                    Create Workspace
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- No Workspace Selected -->
                <div v-if="!currentWorkspace && workspaces.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Select a workspace to get started</h3>
                        <p class="text-gray-600 dark:text-gray-400">Choose a workspace from the dropdown above, or create a new one.</p>
                    </div>
                </div>

                <!-- No Workspaces -->
                <div v-else-if="workspaces.length === 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                        <h3 class="text-lg font-semibold mb-2">Welcome to InternalOS! 🚀</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Create your first workspace to start building internal tools.</p>
                        <Link 
                            :href="route('workspaces.create')"
                            class="px-6 py-3 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600 inline-block"
                        >
                            Create Your First Workspace
                        </Link>
                    </div>
                </div>

                <!-- Workspace Selected -->
                <div v-else>
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Total Collections -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Collections</p>
                                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats?.total_collections || 0 }}</p>
                                    </div>
                                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                                        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Records -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Records</p>
                                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats?.total_records || 0 }}</p>
                                    </div>
                                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Members -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Team Members</p>
                                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats?.total_members || 0 }}</p>
                                    </div>
                                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <Link
                                    v-if="canCreate"
                                    :href="route('collections.create')"
                                    class="flex items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">New Collection</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Create a new collection</p>
                                    </div>
                                </Link>

                                <Link
                                    :href="route('collections.index')"
                                    class="flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">View Collections</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Browse all collections</p>
                                    </div>
                                </Link>

                                <Link
                                    v-if="userRole"
                                    :href="route('members.index')"
                                    class="flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">View Team</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">See team members</p>
                                    </div>
                                </Link>

                                <Link
                                    :href="route('workspaces.index')"
                                    class="flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Workspaces</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Switch workspace</p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Collections -->
                    <div v-if="recentCollections && recentCollections.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Collections</h3>
                                <Link
                                    :href="route('collections.index')"
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
                                >
                                    View All →
                                </Link>
                            </div>
                            
                            <div class="space-y-3">
                                <Link
                                    v-for="collection in recentCollections"
                                    :key="collection.id"
                                    :href="route('collections.show', collection.id)"
                                    class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ collection.name }}</h4>
                                            <p v-if="collection.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ collection.description }}</p>
                                            <div class="flex items-center gap-4 mt-2">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    📄 {{ collection.record_count }} {{ collection.record_count === 1 ? 'record' : 'records' }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    🏷️ {{ collection.field_count }} {{ collection.field_count === 1 ? 'field' : 'fields' }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    🕒 {{ collection.created_at }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No collections yet</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">{{ canCreate ? 'Get started by creating your first collection to organize your data.' : 'Ask an admin to create collections.' }}</p>
                            <Link
                                v-if="canCreate"
                                :href="route('collections.create')"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Your First Collection
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
