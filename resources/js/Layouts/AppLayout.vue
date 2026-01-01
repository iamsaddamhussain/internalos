<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const workspaces = ref(page.props.workspaces || []);
const currentWorkspace = ref(page.props.currentWorkspace || null);

const switchWorkspace = (event) => {
    router.post(`/workspaces/${event.target.value}/switch`, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <span class="text-xl font-bold text-gray-800">InternalOS</span>
                    </div>
                    
                    <!-- Workspace Switcher -->
                    <div class="flex items-center" v-if="workspaces.length > 0">
                        <select 
                            @change="switchWorkspace" 
                            :value="currentWorkspace?.id"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Select Workspace</option>
                            <option 
                                v-for="workspace in workspaces" 
                                :key="workspace.id"
                                :value="workspace.id"
                            >
                                {{ workspace.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>