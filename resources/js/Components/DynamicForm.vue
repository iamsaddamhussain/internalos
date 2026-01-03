<script setup>
import { computed } from 'vue';
import SearchableDropdown from './SearchableDropdown.vue';
import MultiSelectDropdown from './MultiSelectDropdown.vue';

const props = defineProps({
    schema: Object,
    form: Object,
    relatedData: {
        type: Object,
        default: () => ({}),
    },
});

const fields = computed(() => props.schema.fields || []);
</script>

<template>
    <div class="space-y-4">
        <div 
            v-for="(field, index) in fields" 
            :key="field.id" 
            class="form-group" 
            :class="{ 'relative z-30': field.type === 'relation' && index < fields.length - 1, 'relative z-20': field.type === 'relation' && index === fields.length - 1 }"
        >
            <label :for="field.id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ field.label }}
                <span v-if="field.required" class="text-red-500 dark:text-red-400">*</span>
            </label>

            <!-- Text Input -->
            <input
                v-if="field.type === 'text'"
                :id="field.id"
                v-model="form[field.id]"
                type="text"
                :required="field.required"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            />

            <!-- Email Input -->
            <input
                v-else-if="field.type === 'email'"
                :id="field.id"
                v-model="form[field.id]"
                type="email"
                :required="field.required"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            />

            <!-- Number Input -->
            <input
                v-else-if="field.type === 'number'"
                :id="field.id"
                v-model="form[field.id]"
                type="number"
                step="any"
                :required="field.required"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            />

            <!-- Date Input -->
            <input
                v-else-if="field.type === 'date'"
                :id="field.id"
                v-model="form[field.id]"
                type="date"
                :required="field.required"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            />

            <!-- Textarea -->
            <textarea
                v-else-if="field.type === 'textarea'"
                :id="field.id"
                v-model="form[field.id]"
                rows="3"
                :required="field.required"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            ></textarea>

            <!-- Select -->
            <select
                v-else-if="field.type === 'select'"
                :id="field.id"
                v-model="form[field.id]"
                :required="field.required"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            >
                <option value="">Select {{ field.label }}</option>
                <option v-for="option in field.options" :key="option" :value="option">
                    {{ option }}
                </option>
            </select>

            <!-- Checkbox -->
            <div v-else-if="field.type === 'checkbox'" class="flex items-center">
                <input
                    :id="field.id"
                    v-model="form[field.id]"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                />
                <label :for="field.id" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ field.label }}
                </label>
            </div>

            <!-- Relation (Single Select) -->
            <SearchableDropdown
                v-else-if="field.type === 'relation' && !field.multiple"
                :id="field.id"
                v-model="form[field.id]"
                :options="relatedData[field.id]?.records || []"
                :placeholder="`Select ${field.label}`"
                :required="field.required"
                value-key="id"
                display-key="display"
            />

            <!-- Relation (Multiple Select) -->
            <MultiSelectDropdown
                v-else-if="field.type === 'relation' && field.multiple"
                :id="field.id"
                v-model="form[field.id]"
                :options="relatedData[field.id]?.records || []"
                :placeholder="`Select ${field.label}`"
                :required="field.required"
                value-key="id"
                display-key="display"
            />

            <!-- Error Message -->
            <div v-if="form.errors && form.errors[field.id]" class="text-red-600 dark:text-red-400 text-sm mt-1">
                {{ form.errors[field.id] }}
            </div>
        </div>
    </div>
</template>
