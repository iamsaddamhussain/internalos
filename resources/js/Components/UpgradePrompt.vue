<script setup>
import { computed } from 'vue';
import Modal from './Modal.vue';
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    limitType: {
        type: String,
        required: true,
        validator: (value) => ['collections', 'records', 'users'].includes(value)
    },
    currentPlan: {
        type: String,
        default: 'free'
    }
});

const emit = defineEmits(['close']);

const limitMessages = {
    collections: {
        title: "Collection Limit Reached",
        message: "You've reached your plan's collection limit. Upgrade to create unlimited collections."
    },
    records: {
        title: "Record Limit Reached",
        message: "You've reached your plan's record limit. Upgrade to add more records."
    },
    users: {
        title: "Team Member Limit Reached",
        message: "You've reached your plan's team member limit. Upgrade to invite more people."
    }
};

const planFeatures = {
    free: {
        name: 'Free',
        price: '$0',
        features: ['3 Collections', '500 Records', '3 Team Members', '1 Workspace']
    },
    premium: {
        name: 'Premium',
        price: '$15-25/user/month',
        features: ['Unlimited Collections', '50,000 Records', '20 Team Members', '5 Workspaces', 'Export to CSV', 'Priority Support']
    },
    ultra_premium: {
        name: 'Ultra Premium',
        price: 'Custom Pricing',
        features: ['Everything Unlimited', 'API Access', 'Webhooks', 'Audit Logs', 'SSO', 'Dedicated Support']
    }
};

const currentLimitInfo = computed(() => limitMessages[props.limitType]);
const targetPlan = computed(() => props.currentPlan === 'free' ? planFeatures.premium : planFeatures.ultra_premium);

const close = () => {
    emit('close');
};

const contactSales = () => {
    window.location.href = 'mailto:sales@internalos.com?subject=Upgrade Inquiry';
};
</script>

<template>
    <Modal :show="show" @close="close" max-width="2xl">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ currentLimitInfo.title }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ currentLimitInfo.message }}
                    </p>
                </div>
                <button @click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <div class="mt-6 p-6 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950 dark:to-purple-950 rounded-lg border border-indigo-200 dark:border-indigo-800">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                            Upgrade to {{ targetPlan.name }}
                        </h4>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                            {{ targetPlan.price }}
                        </p>
                    </div>
                </div>

                <ul class="space-y-3 mb-6">
                    <li v-for="feature in targetPlan.features" :key="feature" class="flex items-start gap-2">
                        <CheckIcon class="w-5 h-5 text-green-500 shrink-0 mt-0.5" />
                        <span class="text-gray-700 dark:text-gray-300">{{ feature }}</span>
                    </li>
                </ul>

                <div class="flex gap-3">
                    <button
                        @click="contactSales"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg transition"
                    >
                        Contact Sales
                    </button>
                    <button
                        @click="close"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                    >
                        Maybe Later
                    </button>
                </div>
            </div>

            <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                Questions? <a href="mailto:support@internalos.com" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Contact Support</a>
            </div>
        </div>
    </Modal>
</template>
