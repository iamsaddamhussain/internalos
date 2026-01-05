<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckIcon, ArrowUpIcon } from '@heroicons/vue/24/solid';
import { ref, computed } from 'vue';

const props = defineProps({
    currentPlan: Object,
    plans: Array,
    workspace: Object,
});

const selectedPlan = ref(null);
const upgradeMessage = ref('');
const showUpgradeModal = ref(false);

const getUsagePercentage = (current, max) => {
    if (max === -1) return 0;
    return Math.min((current / max) * 100, 100);
};

const getUsageColor = (current, max) => {
    if (max === -1) return 'text-green-600 dark:text-green-400';
    const percentage = (current / max) * 100;
    if (percentage >= 90) return 'text-red-600 dark:text-red-400';
    if (percentage >= 70) return 'text-amber-600 dark:text-amber-400';
    return 'text-green-600 dark:text-green-400';
};

const openUpgradeModal = (plan) => {
    selectedPlan.value = plan;
    showUpgradeModal.value = true;
};

const submitUpgrade = () => {
    router.post(route('plans.upgrade'), {
        plan: selectedPlan.value.slug,
        message: upgradeMessage.value,
    }, {
        onSuccess: () => {
            showUpgradeModal.value = false;
            upgradeMessage.value = '';
            selectedPlan.value = null;
        }
    });
};
</script>

<template>
    <Head title="Plan & Billing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Plan & Billing
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Current Plan Overview -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Current Plan</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ workspace.name }}</p>
                        </div>
                        <div class="text-right">
                            <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                {{ currentPlan.name }}
                            </div>
                        </div>
                    </div>

                    <!-- Usage Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Collections Usage -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Collections</span>
                                <span :class="['text-sm font-bold', getUsageColor(currentPlan.current.collections, currentPlan.limits.max_collections)]">
                                    {{ currentPlan.current.collections }}{{ currentPlan.limits.max_collections === -1 ? '' : '/' + currentPlan.limits.max_collections }}
                                </span>
                            </div>
                            <div v-if="currentPlan.limits.max_collections !== -1" class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div 
                                    class="bg-indigo-600 h-2 rounded-full transition-all"
                                    :style="{ width: getUsagePercentage(currentPlan.current.collections, currentPlan.limits.max_collections) + '%' }"
                                ></div>
                            </div>
                            <p v-else class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unlimited</p>
                        </div>

                        <!-- Records Usage -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Records</span>
                                <span :class="['text-sm font-bold', getUsageColor(currentPlan.current.records, currentPlan.limits.max_records)]">
                                    {{ currentPlan.current.records.toLocaleString() }}{{ currentPlan.limits.max_records === -1 ? '' : '/' + currentPlan.limits.max_records.toLocaleString() }}
                                </span>
                            </div>
                            <div v-if="currentPlan.limits.max_records !== -1" class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div 
                                    class="bg-indigo-600 h-2 rounded-full transition-all"
                                    :style="{ width: getUsagePercentage(currentPlan.current.records, currentPlan.limits.max_records) + '%' }"
                                ></div>
                            </div>
                            <p v-else class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unlimited</p>
                        </div>

                        <!-- Users Usage -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Team Members</span>
                                <span :class="['text-sm font-bold', getUsageColor(currentPlan.current.users, currentPlan.limits.max_users)]">
                                    {{ currentPlan.current.users }}{{ currentPlan.limits.max_users === -1 ? '' : '/' + currentPlan.limits.max_users }}
                                </span>
                            </div>
                            <div v-if="currentPlan.limits.max_users !== -1" class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div 
                                    class="bg-indigo-600 h-2 rounded-full transition-all"
                                    :style="{ width: getUsagePercentage(currentPlan.current.users, currentPlan.limits.max_users) + '%' }"
                                ></div>
                            </div>
                            <p v-else class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unlimited</p>
                        </div>
                    </div>
                </div>

                <!-- Available Plans -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Available Plans</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            v-for="plan in plans"
                            :key="plan.slug"
                            :class="[
                                'border-2 rounded-lg p-6 transition-all',
                                plan.slug === currentPlan.slug
                                    ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-indigo-400'
                            ]"
                        >
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ plan.name }}</h4>
                                    <span v-if="plan.slug === currentPlan.slug" class="text-xs font-medium px-2 py-1 bg-indigo-600 text-white rounded">
                                        Current
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ plan.price }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">{{ plan.period }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ plan.description }}</p>
                            </div>

                            <ul class="space-y-3 mb-6">
                                <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2">
                                    <CheckIcon class="w-5 h-5 text-green-500 shrink-0 mt-0.5" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ feature }}</span>
                                </li>
                            </ul>

                            <button
                                v-if="plan.slug !== currentPlan.slug"
                                @click="openUpgradeModal(plan)"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition flex items-center justify-center gap-2"
                            >
                                <ArrowUpIcon class="w-4 h-4" />
                                Upgrade to {{ plan.name }}
                            </button>
                            <div
                                v-else
                                class="w-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-medium py-3 px-4 rounded-lg text-center"
                            >
                                Your Current Plan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upgrade Modal -->
        <div v-if="showUpgradeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    Upgrade to {{ selectedPlan?.name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Our team will contact you to complete the upgrade process.
                </p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Additional Message (Optional)
                    </label>
                    <textarea
                        v-model="upgradeMessage"
                        rows="4"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Tell us about your needs..."
                    ></textarea>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="submitUpgrade"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition"
                    >
                        Request Upgrade
                    </button>
                    <button
                        @click="showUpgradeModal = false"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
