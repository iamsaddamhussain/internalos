<script setup>
import { ref, computed, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

const showingNavigationDropdown = ref(false);
const page = usePage();

const workspaces = computed(() => page.props.workspaces || []);
const currentWorkspace = computed(() => page.props.currentWorkspace || null);
const userRole = computed(() => page.props.userRole || null);

// Check if user can view members (includes viewers)
const canViewMembers = computed(() => {
    if (!userRole.value) return false;
    // Viewers and above can see members
    return ['owner', 'admin', 'editor', 'viewer'].includes(userRole.value.slug);
});

// Check if user is owner (for permissions management)
const isOwner = computed(() => {
    if (!userRole.value) return false;
    return userRole.value.slug === 'owner';
});

const switchWorkspace = (event) => {
    if (event.target.value) {
        router.post(`/workspaces/${event.target.value}/switch`, {}, {
            preserveState: false,
            preserveScroll: false,
        });
    }
};

const { initTheme } = useTheme();

onMounted(() => {
    initTheme();
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('collections.index')"
                                    :active="route().current('collections.*')"
                                >
                                    Collections
                                </NavLink>
                                <NavLink
                                    v-if="canViewMembers"
                                    :href="route('members.index')"
                                    :active="route().current('members.*')"
                                >
                                    Members
                                </NavLink>
                                <NavLink
                                    v-if="isOwner"
                                    :href="route('admin.permissions')"
                                    :active="route().current('admin.permissions')"
                                >
                                    Permissions
                                </NavLink>
                                <NavLink
                                    :href="route('workspaces.index')"
                                    :active="route().current('workspaces.index')"
                                >
                                    Workspaces
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center space-x-4">
                            <!-- Workspace Switcher -->
                            <div v-if="workspaces.length > 0" class="relative">
                                <select 
                                    @change="switchWorkspace" 
                                    :value="currentWorkspace?.id || ''"
                                    class="border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                >
                                    <option value="">Select Workspace</option>
                                    <option 
                                        v-for="workspace in workspaces" 
                                        :key="workspace.id"
                                        :value="workspace.id"
                                    >
                                        {{ workspace.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Theme Toggle -->
                            <ThemeToggle />
                            
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-500 dark:text-gray-400 transition duration-150 ease-in-out hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 dark:text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-500 dark:hover:text-gray-400 focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('collections.index')"
                            :active="route().current('collections.*')"
                        >
                            Collections
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="canViewMembers"
                            :href="route('members.index')"
                            :active="route().current('members.*')"
                        >
                            Members
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isOwner"
                            :href="route('admin.permissions')"
                            :active="route().current('admin.permissions')"
                        >
                            Permissions
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('workspaces.index')"
                            :active="route().current('workspaces.index')"
                        >
                            Workspaces
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 dark:border-gray-700 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white dark:bg-gray-800 shadow dark:shadow-gray-900/50"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
