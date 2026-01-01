<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: Object,
});

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('invitations.accept', props.invitation.token));
};
</script>

<template>
    <Head title="Accept Invitation" />

    <GuestLayout>
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">You're Invited!</h2>
            <p class="text-gray-600">
                <span class="font-semibold">{{ invitation.inviter.name }}</span> has invited you to join
                <span class="font-semibold">{{ invitation.workspace.name }}</span>
                as a <span class="font-semibold">{{ invitation.role.name }}</span>
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email
                </label>
                <input
                    id="email"
                    type="email"
                    :value="invitation.email"
                    disabled
                    class="w-full border-gray-300 bg-gray-100 rounded-md shadow-sm"
                />
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Your Name *
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                    autofocus
                />
                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                    {{ form.errors.name }}
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Password *
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                />
                <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                    {{ form.errors.password }}
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm Password *
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full px-4 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 font-medium"
            >
                {{ form.processing ? 'Accepting...' : 'Accept Invitation & Join' }}
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Already have an account? 
            <a :href="route('login')" class="text-indigo-600 hover:text-indigo-700 font-medium">
                Sign in instead
            </a>
        </p>
    </GuestLayout>
</template>
