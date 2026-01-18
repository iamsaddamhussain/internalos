<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    workspace: Object,
    automation: Object,
});

const toggleAutomation = () => {
    router.post(route('automations.toggle', props.automation.id), {}, {
        preserveState: true,
    });
};

const deleteAutomation = () => {
    if (confirm('Are you sure you want to delete this automation?')) {
        router.delete(route('automations.destroy', props.automation.id));
    }
};
</script>

<template>
    <Head :title="automation.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('automations.index')"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                            {{ automation.name }}
                        </h2>
                        <div class="mt-1 flex items-center gap-2">
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
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Collection: {{ automation.collection.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="toggleAutomation"
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-md',
                            automation.is_active
                                ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                : 'bg-green-100 text-green-700 hover:bg-green-200'
                        ]"
                    >
                        {{ automation.is_active ? 'Deactivate' : 'Activate' }}
                    </button>

                    <Link
                        :href="route('automations.edit', automation.id)"
                        class="px-3 py-1.5 text-sm font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                    >
                        Edit
                    </Link>

                    <button
                        @click="deleteAutomation"
                        class="px-3 py-1.5 text-sm font-medium bg-red-600 text-white rounded-md hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Description -->
                <div v-if="automation.description" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-700 dark:text-gray-300">{{ automation.description }}</p>
                </div>

                <!-- Triggers -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Triggers ({{ automation.triggers.length }})
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="trigger in automation.triggers"
                            :key="trigger.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ trigger.type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                </span>
                                <span v-if="trigger.field_name" class="text-gray-600 dark:text-gray-400">
                                    → Field: {{ trigger.field_name }}
                                </span>
                                <span v-if="trigger.offset_days !== null && trigger.offset_days !== undefined" class="text-gray-600 dark:text-gray-400">
                                    → {{ trigger.offset_days }} day(s)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conditions -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Conditions ({{ automation.conditions.length }})
                    </h3>
                    <div v-if="automation.conditions.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        No conditions. This automation will always run when triggered.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="condition in automation.conditions"
                            :key="condition.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ condition.field }}</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ condition.operator }}</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">"{{ condition.value }}"</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Actions ({{ automation.actions.length }})
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="action in automation.actions"
                            :key="action.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ action.type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                    </span>
                                    <span v-if="action.target" class="text-gray-600 dark:text-gray-400">
                                        → Target: {{ action.target }}
                                    </span>
                                    <span v-if="action.channel" class="text-gray-600 dark:text-gray-400">
                                        → Channel: {{ action.channel }}
                                    </span>
                                </div>
                                <div v-if="action.config" class="text-xs text-gray-500 dark:text-gray-400">
                                    <div v-if="action.config.title">Title: {{ action.config.title }}</div>
                                    <div v-if="action.config.body">Message: {{ action.config.body }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Logs -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Recent Execution Logs
                    </h3>
                    <div v-if="automation.logs.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        No execution logs yet.
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="log in automation.logs"
                            :key="log.id"
                            class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-1 rounded text-xs font-medium',
                                        log.status === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                        log.status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                    ]"
                                >
                                    {{ log.status }}
                                </span>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ log.message }}</span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ new Date(log.executed_at).toLocaleString() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
