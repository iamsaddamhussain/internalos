<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    notifications: Object,
    unreadCount: Number,
});

const showUnreadOnly = ref(false);

const filterNotifications = () => {
    router.get(route('notifications.index'), {
        unread_only: showUnreadOnly.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const markAsRead = (notification) => {
    router.post(route('notifications.read', notification.id), {}, {
        preserveState: true,
    });
};

const markAllAsRead = () => {
    router.post(route('notifications.read-all'), {}, {
        preserveState: true,
    });
};

const deleteNotification = (notification) => {
    router.delete(route('notifications.destroy', notification.id), {
        preserveState: true,
    });
};

const getNotificationIcon = (type) => {
    switch (type) {
        case 'automation':
            return '🤖';
        case 'system':
            return '⚙️';
        default:
            return '📬';
    }
};
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Notifications
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                >
                    Mark all as read
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Filter -->
                <div class="mb-6 flex items-center gap-4">
                    <label class="flex items-center">
                        <input
                            v-model="showUnreadOnly"
                            @change="filterNotifications"
                            type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Show unread only
                        </span>
                    </label>
                </div>

                <!-- Notifications List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div v-if="notifications.data.length === 0" class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You're all caught up!</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="notification in notifications.data"
                            :key="notification.id"
                            :class="[
                                'p-6 transition-colors',
                                !notification.read_at ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div class="text-2xl">{{ getNotificationIcon(notification.type) }}</div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ notification.title }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                {{ notification.body }}
                                            </p>
                                            <div class="mt-2 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                                <span>{{ new Date(notification.created_at).toLocaleString() }}</span>
                                                <span v-if="notification.workspace">
                                                    Workspace: {{ notification.workspace.name }}
                                                </span>
                                                <span v-if="!notification.read_at" class="font-medium text-indigo-600 dark:text-indigo-400">
                                                    Unread
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center gap-2 ml-4">
                                            <button
                                                v-if="notification.metadata?.link"
                                                @click="router.visit(notification.metadata.link)"
                                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                            >
                                                View
                                            </button>
                                            <button
                                                v-if="!notification.read_at"
                                                @click="markAsRead(notification)"
                                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300"
                                            >
                                                Mark read
                                            </button>
                                            <button
                                                @click="deleteNotification(notification)"
                                                class="text-sm text-red-600 hover:text-red-800"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="notifications.links && notifications.links.length > 3" class="mt-6 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Link
                            v-for="link in notifications.links"
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
