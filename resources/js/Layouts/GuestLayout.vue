<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
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
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                            <!-- Theme Switcher -->
                            <ThemeSwitcher />
                            
                            <!-- Login/Register Links for guests -->
                            <template v-if="!$page.props.auth.user">
                                <NavLink 
                                    :href="route('login')" 
                                    :active="route().current('login')"
                                    variant="ghost"
                                    size="sm"
                                >
                                    Log in
                                </NavLink>
                                
                                <NavLink 
                                    :href="route('register')" 
                                    :active="route().current('register')"
                                    variant="ghost"
                                    size="sm"
                                >
                                    Register
                                </NavLink>
                                
                                <NavLink 
                                    :href="route('register', { redirect: 'businesses.register' })"
                                    variant="primary"
                                    size="sm"
                                >
                                    Register Business
                                </NavLink>
                            </template>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button 
                                class="btn btn-ghost btn-sm"
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path 
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        stroke-width="2" 
                                        d="M4 6h16M4 12h16M4 18h16" 
                                    />
                                    <path 
                                        :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
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
                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <template v-if="!$page.props.auth.user">
                            <NavLink 
                                :href="route('login')" 
                                :active="route().current('login')"
                                variant="ghost"
                                class="block w-full text-left"
                            >
                                Log in
                            </NavLink>
                            
                            <NavLink 
                                :href="route('register')" 
                                :active="route().current('register')"
                                variant="ghost"
                                class="block w-full text-left"
                            >
                                Register
                            </NavLink>
                            
                            <NavLink 
                                :href="route('register', { redirect: 'businesses.register' })"
                                variant="primary"
                                class="block w-full text-left"
                            >
                                Register Business
                            </NavLink>
                        </template>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template> 