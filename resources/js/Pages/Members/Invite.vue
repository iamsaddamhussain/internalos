<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: Array,
});

const inviteForm = useForm({
    email: '',
    role_id: '',
});

const inviteUser = () => {
    inviteForm.post(route('invitations.store'));
};
</script>

<template>
    <Head title="Invite Member" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Invite Team Member
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="inviteUser">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email Address *
                                </label>
                                <input
                                    v-model="inviteForm.email"
                                    type="email"
                                    placeholder="john@example.com"
                                    class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                />
                                <div v-if="inviteForm.errors.email" class="text-red-600 dark:text-red-400 text-sm mt-1">
                                    {{ inviteForm.errors.email }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Role *
                                </label>
                                <select
                                    v-model="inviteForm.role_id"
                                    class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Select a role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }} - {{ role.description }}
                                    </option>
                                </select>
                                <div v-if="inviteForm.errors.role_id" class="text-red-600 dark:text-red-400 text-sm mt-1">
                                    {{ inviteForm.errors.role_id }}
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <a
                                :href="route('members.index')"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                :disabled="inviteForm.processing"
                                class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600 disabled:opacity-50"
                            >
                                {{ inviteForm.processing ? 'Sending...' : 'Send Invitation' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
