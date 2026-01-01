<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    members: Array,
    invitations: Array,
    roles: Array,
    currentUserId: Number,
});

const page = usePage();
const userRole = computed(() => page.props.userRole || null);

// Check if user can manage members (change roles, remove members)
const canManageMembers = computed(() => {
    if (!userRole.value) return false;
    return ['owner', 'admin'].includes(userRole.value.slug);
});

// Check if user can invite members
const canInviteMembers = computed(() => {
    if (!userRole.value) return false;
    return ['owner', 'admin'].includes(userRole.value.slug);
});

const updateRole = (userId, newRoleId) => {
    router.put(route('members.update-role', userId), {
        role_id: newRoleId,
    });
};

const removeMember = (userId) => {
    if (confirm('Are you sure you want to remove this member from the workspace?')) {
        router.delete(route('members.destroy', userId));
    }
};

const cancelInvitation = (invitationId) => {
    if (confirm('Are you sure you want to cancel this invitation?')) {
        router.delete(route('invitations.destroy', invitationId));
    }
};

const copyInviteLink = (token) => {
    const url = `${window.location.origin}/invitations/${token}/accept`;
    navigator.clipboard.writeText(url);
    alert('Invitation link copied to clipboard!');
};
</script>

<template>
    <Head title="Members" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Team Members
                </h2>
                <Link
                    v-if="canInviteMembers"
                    :href="route('invitations.create')"
                    class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600"
                >
                    ➕ Invite Member
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Active Members -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Active Members ({{ members.length }})</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Joined</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="member in members" :key="member.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ member.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            {{ member.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <select
                                                v-if="canManageMembers"
                                                :value="member.role_id"
                                                @change="updateRole(member.id, $event.target.value)"
                                                :disabled="member.id === currentUserId"
                                                class="border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                                    {{ role.name }}
                                                </option>
                                            </select>
                                            <span v-else class="text-gray-900 dark:text-gray-100">
                                                {{ member.role.name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            {{ new Date(member.joined_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <button
                                                v-if="canManageMembers && member.id !== currentUserId"
                                                @click="removeMember(member.id)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                            >
                                                Remove
                                            </button>
                                            <span
                                                v-else-if="member.id === currentUserId"
                                                class="text-gray-400 dark:text-gray-600 text-xs"
                                            >
                                                (You)
                                            </span>
                                            <span
                                                v-else
                                                class="text-gray-400 dark:text-gray-600 text-xs"
                                            >
                                                —
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pending Invitations -->
                <div v-if="invitations.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pending Invitations ({{ invitations.length }})</h3>
                        
                        <div class="space-y-3">
                            <div
                                v-for="invitation in invitations"
                                :key="invitation.id"
                                class="flex justify-between items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
                            >
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ invitation.email }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Role: {{ invitation.role.name }} • 
                                        Invited by {{ invitation.inviter.name }} • 
                                        Expires {{ new Date(invitation.expires_at).toLocaleDateString() }}
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        v-if="canInviteMembers"
                                        @click="copyInviteLink(invitation.token)"
                                        class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
                                    >
                                        Copy Link
                                    </button>
                                    <button
                                        v-if="canManageMembers"
                                        @click="cancelInvitation(invitation.id)"
                                        class="px-3 py-1 text-sm text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
