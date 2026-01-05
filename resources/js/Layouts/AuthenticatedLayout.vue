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

const isSuperAdmin = computed(() => page.props.auth?.user?.is_super_admin || false);
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
                            <div class="flex shrink-0 items-center gap-3">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                                <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">Internal OS</span>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    v-if="isSuperAdmin"
                                    :href="route('saas.admin.dashboard')"
                                    :active="route().current('saas.admin.dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <template v-else>
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
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center space-x-4">
                            <!-- Workspace Switcher Dropdown -->
                            <div v-if="!isSuperAdmin && workspaces.length > 0" class="relative">
                                <Dropdown align="right" width="64">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150"
                                        >
                                            <span class="mr-2">{{ currentWorkspace?.name || 'Select Workspace' }}</span>
                                            <svg
                                                class="h-4 w-4"
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
                                    </template>

                                    <template #content>
                                        <div class="py-1">
                                            <div class="px-4 py-2 text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                                Switch Workspace
                                            </div>
                                            <button
                                                v-for="workspace in workspaces"
                                                :key="workspace.id"
                                                @click="() => router.post(`/workspaces/${workspace.id}/switch`, {}, { preserveState: false, preserveScroll: false })"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between"
                                                :class="{ 'bg-gray-50 dark:bg-gray-800': currentWorkspace?.id === workspace.id }"
                                            >
                                                <span>{{ workspace.name }}</span>
                                                <svg v-if="currentWorkspace?.id === workspace.id" class="h-4 w-4 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div class="border-t border-gray-200 dark:border-gray-700">
                                                <Link
                                                    :href="route('workspaces.create')"
                                                    class="block px-4 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                >
                                                    <span class="flex items-center">
                                                        <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        Create new workspace
                                                    </span>
                                                </Link>
                                            </div>
                                        </div>
                                    </template>
                                </Dropdown>
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
                            v-if="isSuperAdmin"
                            :href="route('saas.admin.dashboard')"
                            :active="route().current('saas.admin.dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <template v-else>
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
                        </template>
                    </div>

                    <!-- Workspace Switcher for Mobile -->
                    <div v-if="!isSuperAdmin && workspaces.length > 0" class="border-t border-gray-200 dark:border-gray-700 pb-3 pt-4">
                        <div class="px-4 pb-2">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Current Workspace
                            </div>
                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-1">
                                {{ currentWorkspace?.name || 'Select Workspace' }}
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 uppercase">
                                Switch to
                            </div>
                            <button
                                v-for="workspace in workspaces.filter(w => w.id !== currentWorkspace?.id)"
                                :key="workspace.id"
                                @click="() => router.post(`/workspaces/${workspace.id}/switch`, {}, { preserveState: false, preserveScroll: false })"
                                class="w-full text-left px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100"
                            >
                                {{ workspace.name }}
                            </button>
                            <Link
                                :href="route('workspaces.create')"
                                class="block px-4 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                + Create new workspace
                            </Link>
                        </div>
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
