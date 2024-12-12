<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();

const isBusinessOwner = computed(() => {
    return page.props.auth?.user?.business_owner ?? false;
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-base-100">
            <nav class="border-b border-base-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('welcome')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                
                                <!-- Show Business Dashboard link if user is a business owner -->
                                <NavLink 
                                    v-if="isBusinessOwner"
                                    :href="route('business.dashboard')" 
                                    :active="route().current('business.*')"
                                >
                                    Business Dashboard
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                            <!-- Register Business Button -->
                            <NavLink 
                                v-if="$page.props.auth.user && !isBusinessOwner"
                                :href="route('businesses.register')"
                                variant="primary"
                            >
                                Register Business
                            </NavLink>
                            
                            <!-- Theme Switcher -->
                            <ThemeSwitcher />

                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative" v-if="$page.props.auth.user">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-base-300 transition">
                                            <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user?.profile_photo_url" :alt="$page.props.auth.user?.name">
                                        </button>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-base-content/70">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <div class="border-t border-base-200"></div>

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                            
                            <!-- Login/Register Links for guests -->
                            <template v-else>
                                <NavLink :href="route('login')" :active="route().current('login')">
                                    Log in
                                </NavLink>
                                
                                <NavLink :href="route('register')" :active="route().current('register')">
                                    Register
                                </NavLink>
                                
                                <NavLink 
                                    :href="route('register', { redirect: 'businesses.register' })"
                                    variant="primary"
                                >
                                    Register Business
                                </NavLink>
                            </template>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-base-content/70 hover:text-base-content hover:bg-base-200 focus:outline-none focus:bg-base-200 focus:text-base-content transition" @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </NavLink>
                        
                        <NavLink 
                            v-if="isBusinessOwner"
                            :href="route('business.dashboard')" 
                            :active="route().current('business.*')"
                        >
                            Business Dashboard
                        </NavLink>
                        
                        <!-- Mobile Register Business Button -->
                        <NavLink 
                            v-if="$page.props.auth.user && !isBusinessOwner"
                            :href="route('businesses.register')"
                            variant="primary"
                        >
                            Register Business
                        </NavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-base-200" v-if="$page.props.auth.user">
                        <div class="flex items-center px-4">
                            <div class="shrink-0">
                                <img class="h-10 w-10 rounded-full object-cover" :src="$page.props.auth.user?.profile_photo_url" :alt="$page.props.auth.user?.name">
                            </div>

                            <div class="ml-3">
                                <div class="font-medium text-base text-base-content">
                                    {{ $page.props.auth.user?.name }}
                                </div>
                                <div class="font-medium text-sm text-base-content/70">
                                    {{ $page.props.auth.user?.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <DropdownLink :href="route('profile.show')">
                                Profile
                            </DropdownLink>

                            <!-- Authentication -->
                            <form @submit.prevent="logout">
                                <DropdownLink as="button">
                                    Log Out
                                </DropdownLink>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Mobile Login/Register Links for guests -->
                    <div v-else class="pt-2 pb-3 space-y-1">
                        <NavLink :href="route('login')" :active="route().current('login')">
                            Log in
                        </NavLink>
                        
                        <NavLink :href="route('register')" :active="route().current('register')">
                            Register
                        </NavLink>
                        
                        <NavLink 
                            :href="route('register', { redirect: 'businesses.register' })"
                            variant="primary"
                        >
                            Register Business
                        </NavLink>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-base-100 shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
