<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    collection: Object,
    record: Object,
    recordTitle: String,
    displayData: Object,
    activities: Array,
    canEdit: Boolean,
    canDelete: Boolean,
    canCreateActivity: Boolean,
    canSignOffActivity: Boolean,
    canDeleteActivity: Boolean,
});

const showActivityForm = ref(false);

const activityForm = useForm({
    title: '',
    description: '',
    status: 'open',
});

const submitActivity = () => {
    activityForm.post(route('activities.store', props.record.id), {
        preserveScroll: true,
        onSuccess: () => {
            activityForm.reset();
            showActivityForm.value = false;
        },
    });
};

const signOffActivity = (activityId) => {
    router.post(route('activities.sign-off', activityId), {}, {
        preserveScroll: true,
    });
};

const deleteActivity = (activityId) => {
    if (confirm('Are you sure you want to delete this activity?')) {
        router.delete(route('activities.destroy', activityId), {
            preserveScroll: true,
        });
    }
};

const getStatusColor = (status) => {
    const colors = {
        open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        done: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        blocked: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return colors[status] || colors.open;
};
</script>

<template>
    <Head :title="`${recordTitle} - ${collection.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {{ collection.name }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ recordTitle }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a
                        v-if="canEdit"
                        :href="route('records.edit', [collection.id, record.id])"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none"
                    >
                        Edit Record
                    </a>
                    <a
                        :href="route('collections.show', collection.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white"
                    >
                        Back to Collection
                    </a>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Record Details Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800 mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Record Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="field in collection.schema.fields" :key="field.id">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ field.label }}
                                </label>
                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ displayData[field.id] || '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activities Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold">Activity</h3>
                            <button
                                v-if="canCreateActivity"
                                @click="showActivityForm = !showActivityForm"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Activity
                            </button>
                        </div>

                        <!-- Activity Form -->
                        <div v-if="showActivityForm" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <form @submit.prevent="submitActivity">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Title *
                                    </label>
                                    <input
                                        v-model="activityForm.title"
                                        type="text"
                                        required
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    />
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Description
                                    </label>
                                    <textarea
                                        v-model="activityForm.description"
                                        rows="3"
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    ></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Status
                                    </label>
                                    <select
                                        v-model="activityForm.status"
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="open">Open</option>
                                        <option value="done">Done</option>
                                        <option value="blocked">Blocked</option>
                                    </select>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <button
                                        type="button"
                                        @click="showActivityForm = false"
                                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="activityForm.processing"
                                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50"
                                    >
                                        Add Activity
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Activities List -->
                        <div v-if="activities.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No activities yet. Add one to get started!
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="activity in activities"
                                :key="activity.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="text-base font-semibold">{{ activity.title }}</h4>
                                            <span
                                                :class="getStatusColor(activity.status)"
                                                class="px-2 py-1 text-xs font-semibold rounded-full capitalize"
                                            >
                                                {{ activity.status }}
                                            </span>
                                        </div>
                                        
                                        <p v-if="activity.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            {{ activity.description }}
                                        </p>

                                        <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span>Added by {{ activity.created_by }}</span>
                                            <span>{{ activity.created_at }}</span>
                                        </div>

                                        <div v-if="activity.is_signed_off" class="mt-2 flex items-center text-sm text-green-600 dark:text-green-400">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Signed off by {{ activity.signed_off_by }} {{ activity.signed_off_at }}
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 ml-4">
                                        <button
                                            v-if="canSignOffActivity && !activity.is_signed_off && activity.status !== 'done'"
                                            @click="signOffActivity(activity.id)"
                                            class="px-3 py-1 text-xs font-medium text-green-600 dark:text-green-400 border border-green-600 dark:border-green-400 rounded hover:bg-green-50 dark:hover:bg-green-900"
                                        >
                                            Sign Off
                                        </button>
                                        <button
                                            v-if="canDeleteActivity"
                                            @click="deleteActivity(activity.id)"
                                            class="p-1 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 rounded"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
