<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    workspace: Object,
    collection: Object,
    fields: Array,
    automation: {
        type: Object,
        default: null
    }
});

// Debug: Log fields to see what's being passed
console.log('Fields from controller:', props.fields);
console.log('Date fields:', props.fields?.filter(f => f.type === 'date'));

const isEditing = computed(() => !!props.automation);

const form = useForm({
    name: props.automation?.name || '',
    description: props.automation?.description || '',
    is_active: props.automation?.is_active ?? true,
    triggers: props.automation?.triggers || [{
        type: 'date_reached',
        field_name: '',
        offset_days: 0,
    }],
    conditions: props.automation?.conditions || [],
    actions: props.automation?.actions || [{
        type: 'notify',
        target: 'field:assignee',
        channel: 'in_app',
        config: {
            title: '',
            body: ''
        }
    }]
});

const triggerTypes = [
    { value: 'record_created', label: 'Record Created' },
    { value: 'record_updated', label: 'Record Updated' },
    { value: 'date_reached', label: 'Date Reached' },
    { value: 'status_changed', label: 'Status Changed' },
    { value: 'comment_added', label: 'Comment Added' },
];

const operators = [
    { value: '=', label: 'Equals' },
    { value: '!=', label: 'Not Equals' },
    { value: '>', label: 'Greater Than' },
    { value: '<', label: 'Less Than' },
    { value: '>=', label: 'Greater or Equal' },
    { value: '<=', label: 'Less or Equal' },
    { value: 'contains', label: 'Contains' },
    { value: 'not_contains', label: 'Not Contains' },
];

const actionTypes = [
    { value: 'notify', label: 'Send Notification' },
    { value: 'email', label: 'Send Email' },
    { value: 'update_field', label: 'Update Field' },
];

const addTrigger = () => {
    form.triggers.push({
        type: 'date_reached',
        field_name: '',
        offset_days: 0,
    });
};

const removeTrigger = (index) => {
    form.triggers.splice(index, 1);
};

const addCondition = () => {
    form.conditions.push({
        field: '',
        operator: '=',
        value: '',
        condition_group: 'default'
    });
};

const removeCondition = (index) => {
    form.conditions.splice(index, 1);
};

const addAction = () => {
    form.actions.push({
        type: 'notify',
        target: 'field:assignee',
        channel: 'in_app',
        config: {
            title: '',
            body: ''
        }
    });
};

const removeAction = (index) => {
    form.actions.splice(index, 1);
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('automations.update', props.automation.id));
    } else {
        form.post(route('automations.store', props.collection.id));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Automation' : 'Create Automation'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('automations.index')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                >
                    ← Back
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ isEditing ? 'Edit Automation' : 'Create Automation' }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Name *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    placeholder="e.g., Project deadline reminder"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    placeholder="What does this automation do?"
                                />
                            </div>

                            <div class="flex items-center">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Triggers -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Triggers</h3>
                            <button
                                type="button"
                                @click="addTrigger"
                                class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Add Trigger
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(trigger, index) in form.triggers"
                                :key="index"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex-1 space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Trigger Type
                                            </label>
                                            <select
                                                v-model="trigger.type"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            >
                                                <option v-for="type in triggerTypes" :key="type.value" :value="type.value">
                                                    {{ type.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div v-if="trigger.type === 'date_reached'">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Date Field
                                            </label>
                                            <select
                                                v-model="trigger.field_name"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            >
                                                <option value="">Select a date field</option>
                                                <option v-for="field in (fields || []).filter(f => f.type === 'date')" :key="field.name" :value="field.name">
                                                    {{ field.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div v-if="trigger.type === 'date_reached'">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Days Offset
                                            </label>
                                            <input
                                                v-model.number="trigger.offset_days"
                                                type="number"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                placeholder="e.g., -2 for 2 days before, 0 for same day, 3 for 3 days after"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">Negative = before, 0 = on date, Positive = after</p>
                                        </div>
                                    </div>

                                    <button
                                        v-if="form.triggers.length > 1"
                                        type="button"
                                        @click="removeTrigger(index)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conditions -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Conditions (Optional)</h3>
                            <button
                                type="button"
                                @click="addCondition"
                                class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Add Condition
                            </button>
                        </div>

                        <div v-if="form.conditions.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                            No conditions. Automation will always run when triggered.
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(condition, index) in form.conditions"
                                :key="index"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex-1 grid grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Field
                                            </label>
                                            <select
                                                v-model="condition.field"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            >
                                                <option value="">Select field</option>
                                                <option v-for="field in (fields || [])" :key="field.name" :value="field.name">
                                                    {{ field.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Operator
                                            </label>
                                            <select
                                                v-model="condition.operator"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            >
                                                <option v-for="op in operators" :key="op.value" :value="op.value">
                                                    {{ op.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Value
                                            </label>
                                            <input
                                                v-model="condition.value"
                                                type="text"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            />
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="removeCondition(index)"
                                        class="text-red-600 hover:text-red-800 mt-6"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Actions</h3>
                            <button
                                type="button"
                                @click="addAction"
                                class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Add Action
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(action, index) in form.actions"
                                :key="index"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex-1 space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Action Type
                                            </label>
                                            <select
                                                v-model="action.type"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            >
                                                <option v-for="type in actionTypes" :key="type.value" :value="type.value">
                                                    {{ type.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div v-if="action.type === 'notify'">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Notify
                                            </label>
                                            <input
                                                v-model="action.target"
                                                type="text"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                placeholder="e.g., field:assignee, field:manager, role:admin"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">
                                                Use field:fieldname for a user field, or role:rolename for a role
                                            </p>
                                        </div>

                                        <div v-if="action.type === 'notify'">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Notification Title
                                            </label>
                                            <input
                                                v-model="action.config.title"
                                                type="text"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                placeholder="Leave blank to use automation name"
                                            />
                                        </div>

                                        <div v-if="action.type === 'notify'">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Notification Message
                                            </label>
                                            <textarea
                                                v-model="action.config.body"
                                                rows="2"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                placeholder="Leave blank for auto-generated message"
                                            />
                                        </div>
                                    </div>

                                    <button
                                        v-if="form.actions.length > 1"
                                        type="button"
                                        @click="removeAction(index)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end gap-4">
                        <Link
                            :href="route('automations.index')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ isEditing ? 'Update' : 'Create' }} Automation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
