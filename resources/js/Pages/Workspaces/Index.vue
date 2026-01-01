<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    workspaces: Array,
});

const selectWorkspace = (workspace) => {
    router.post(`/workspaces/${workspace.id}/switch`, {}, {
        onSuccess: () => {
            router.visit('/dashboard');
        }
    });
};
</script>

<template>
    <Head title="Workspaces" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Your Workspaces
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="workspace in workspaces" 
                        :key="workspace.id"
                        @click="selectWorkspace(workspace)"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 cursor-pointer hover:shadow-md dark:hover:shadow-gray-900/50 transition-shadow"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            {{ workspace.name }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ workspace.slug }}
                        </p>
                        <div class="mt-4 flex items-center text-xs text-gray-500">
                            <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded">
                                {{ workspace.pivot.role }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
