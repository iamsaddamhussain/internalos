<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    workspaces: Array,
    users: Array,
    stats: Object,
    recentUsers: Array,
    recentWorkspaces: Array,
    upgradeRequests: Array,
    pendingRequestsCount: Number,
});

const activeTab = ref('overview');

const updateRequestStatus = (requestId, status, notes = '') => {
    router.put(route('saas.admin.upgrade-requests.update', requestId), {
        status: status,
        admin_notes: notes,
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'approved': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'rejected': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'completed': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
};

const getPlanBadgeColor = (plan) => {
    switch (plan) {
        case 'premium': return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200';
        case 'ultra_premium': return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
};
</script>

<template>
    <Head title="SAAS Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                🏢 Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.total_users }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">+{{ stats.users_last_30_days }} last 30 days</p>
                            </div>
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Workspaces</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.total_workspaces }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">+{{ stats.workspaces_last_30_days }} last 30 days</p>
                            </div>
                            <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Collections</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.total_collections }}</p>
                            </div>
                            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Records</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.total_records }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ stats.total_activities }} activities</p>
                            </div>
                            <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex -mb-px">
                            <button
                                @click="activeTab = 'overview'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'overview'
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                Overview
                            </button>
                            <button
                                @click="activeTab = 'workspaces'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'workspaces'
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                Workspaces ({{ workspaces.length }})
                            </button>
                            <button
                                @click="activeTab = 'users'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'users'
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                Users ({{ users.length }})
                            </button>
                            <button
                                @click="activeTab = 'upgrade-requests'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors relative',
                                    activeTab === 'upgrade-requests'
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                Upgrade Requests
                                <span v-if="pendingRequestsCount > 0" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    {{ pendingRequestsCount }}
                                </span>
                            </button>
                        </nav>
                    </div>

                    <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Recent Users -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Users</h3>
                                <div class="space-y-3">
                                    <div
                                        v-for="user in recentUsers"
                                        :key="user.email"
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                    >
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.created_at }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Workspaces -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Workspaces</h3>
                                <div class="space-y-3">
                                    <div
                                        v-for="workspace in recentWorkspaces"
                                        :key="workspace.name"
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                    >
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ workspace.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ workspace.created_at }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Workspaces Tab -->
                    <div v-if="activeTab === 'workspaces'" class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Workspace</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Owner</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Collections</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Members</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="workspace in workspaces" :key="workspace.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ workspace.name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ workspace.slug }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ workspace.owner_name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ workspace.owner_email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ workspace.collections_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ workspace.members_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ workspace.created_at }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Users Tab -->
                    <div v-if="activeTab === 'users'" class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Workspaces</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ user.workspaces_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ user.created_at }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Upgrade Requests Tab -->
                    <div v-if="activeTab === 'upgrade-requests'" class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Upgrade Requests
                                <span v-if="pendingRequestsCount > 0" class="ml-2 text-sm text-red-600 dark:text-red-400">
                                    ({{ pendingRequestsCount }} pending)
                                </span>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Manage customer upgrade requests and contact messages
                            </p>
                        </div>

                        <div v-if="upgradeRequests.length === 0" class="text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400">No upgrade requests yet</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="request in upgradeRequests"
                                :key="request.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md transition"
                            >
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                {{ request.workspace_name }}
                                            </h4>
                                            <span :class="['px-3 py-1 rounded-full text-xs font-medium', getPlanBadgeColor(request.requested_plan)]">
                                                {{ request.requested_plan === 'premium' ? 'Premium' : 'Ultra Premium' }}
                                            </span>
                                            <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusColor(request.status)]">
                                                {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            <p><strong>Contact:</strong> {{ request.user_name }} ({{ request.user_email }})</p>
                                            <p class="mt-1"><strong>Requested:</strong> {{ request.created_at }}</p>
                                            <p v-if="request.reviewed_at" class="mt-1"><strong>Reviewed:</strong> {{ request.reviewed_at }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="request.message" class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        <strong class="block mb-1">Customer Message:</strong>
                                        {{ request.message }}
                                    </p>
                                </div>

                                <div v-if="request.status === 'pending'" class="flex gap-3">
                                    <button
                                        @click="updateRequestStatus(request.id, 'approved')"
                                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition"
                                    >
                                        ✅ Approve & Upgrade
                                    </button>
                                    <button
                                        @click="updateRequestStatus(request.id, 'rejected')"
                                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition"
                                    >
                                        ❌ Reject
                                    </button>
                                    <button
                                        @click="updateRequestStatus(request.id, 'completed')"
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition"
                                    >
                                        ✔️ Mark Completed
                                    </button>
                                </div>

                                <div v-else class="text-sm text-gray-600 dark:text-gray-400 italic">
                                    Request {{ request.status }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
