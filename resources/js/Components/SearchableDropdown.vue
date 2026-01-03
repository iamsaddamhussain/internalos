<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Select an item...',
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    displayKey: {
        type: String,
        default: 'display',
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);
const searchInputRef = ref(null);

const selectedItem = computed(() => {
    return props.options.find(option => option[props.valueKey] === props.modelValue);
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) {
        return props.options;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(option => 
        option[props.displayKey].toLowerCase().includes(query)
    );
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        setTimeout(() => {
            searchInputRef.value?.focus();
        }, 100);
    }
};

const selectItem = (option) => {
    emit('update:modelValue', option[props.valueKey]);
    isOpen.value = false;
    searchQuery.value = '';
};

const clearSelection = () => {
    emit('update:modelValue', '');
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative w-full">
        <!-- Selected Item Display / Trigger -->
        <div
            @click="toggleDropdown"
            class="min-h-[42px] w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm cursor-pointer hover:border-gray-400 dark:hover:border-gray-500 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 flex items-center"
            :class="{ 'ring-2 ring-indigo-500 border-indigo-500': isOpen }"
        >
            <span
                v-if="selectedItem"
                class="text-gray-900 dark:text-gray-100 text-sm flex-1 truncate"
            >
                {{ selectedItem[displayKey] }}
            </span>
            <span
                v-else
                class="text-gray-500 dark:text-gray-400 text-sm flex-1"
            >
                {{ placeholder }}
            </span>
            
            <!-- Clear Button & Dropdown Arrow -->
            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                <button
                    v-if="selectedItem && !required"
                    type="button"
                    @click.stop="clearSelection"
                    class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 mr-1"
                >
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                
                <svg
                    class="h-5 w-5 text-gray-400 transition-transform pointer-events-none"
                    :class="{ 'rotate-180': isOpen }"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>

        <!-- Dropdown Menu -->
        <div
            v-show="isOpen"
            class="absolute z-[9999] mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-xl"
            style="max-height: 400px;"
        >
            <!-- Search Input -->
            <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                <input
                    ref="searchInputRef"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    @click.stop
                />
            </div>

            <!-- Options List -->
            <div class="max-h-60 overflow-y-auto">
                <button
                    v-for="option in filteredOptions"
                    :key="option[valueKey]"
                    type="button"
                    @click.stop="selectItem(option)"
                    class="w-full text-left px-3 py-2.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between text-gray-900 dark:text-gray-100 transition-colors"
                    :class="{
                        'bg-indigo-50 dark:bg-indigo-900/20': selectedItem?.[valueKey] === option[valueKey],
                    }"
                >
                    <span>{{ option[displayKey] }}</span>
                    <svg
                        v-if="selectedItem?.[valueKey] === option[valueKey]"
                        class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
                
                <!-- No Results -->
                <div
                    v-if="filteredOptions.length === 0"
                    class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                >
                    No results found
                </div>
            </div>
        </div>
    </div>
</template>
