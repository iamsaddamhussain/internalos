<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    roles: Array,
    permissions: Object,
    userRoleSlug: String,
});

const defaultRole = props.roles.find(r => r.slug === props.userRoleSlug) || props.roles[0];

const selectedRole = ref(defaultRole);
const form = useForm({
    permissions: selectedRole.value.permissions.map(p => p.id),
});

const selectRole = (role) => {
    selectedRole.value = role;
    form.permissions = role.permissions.map(p => p.id);
};

const togglePermission = (permissionId) => {
    const index = form.permissions.indexOf(permissionId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionId);
    }
};

const hasPermission = (permissionId) => {
    return form.permissions.includes(permissionId);
};

const submit = () => {
    form.put(route('admin.permissions.update', selectedRole.value.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Manage Permissions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                🔐 Manage Role Permissions
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Role Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Select Role to Configure
                            </label>
                            <div class="flex gap-3">
                                <button
                                    v-for="role in roles"
                                    :key="role.id"
                                    @click="selectRole(role)"
                                    :class="[
                                        'px-4 py-2 rounded-md font-medium transition-colors',
                                        selectedRole.id === role.id
                                            ? 'bg-indigo-600 dark:bg-indigo-500 text-white'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    {{ role.name }}
                                </button>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Permissions by Module -->
                            <div class="space-y-6">
                                <div v-for="(perms, module) in permissions" :key="module" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 capitalize">
                                        {{ module }} Module
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <label
                                            v-for="permission in perms"
                                            :key="permission.id"
                                            class="flex items-start p-3 border border-gray-200 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="hasPermission(permission.id)"
                                                @change="togglePermission(permission.id)"
                                                class="mt-1 rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ permission.name }}
                                                </div>
                                                <div v-if="permission.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ permission.description }}
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Saving...' : `Save ${selectedRole.name} Permissions` }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
