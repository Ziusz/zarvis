<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();

const navigation = [
    {
        name: 'Dashboard',
        href: route('business.dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
    },
    {
        name: 'Calendar',
        href: route('business.calendar'),
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
    },
    {
        name: 'Bookings',
        href: route('business.bookings.index'),
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        name: 'Clients',
        href: route('business.clients.index'),
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
    },
    {
        name: 'Staff',
        href: route('business.staff.index'),
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
    },
    {
        name: 'Analytics',
        href: route('business.analytics'),
        icon: 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        current: route().current('business.analytics'),
    },
    {
        name: 'Services',
        href: route('business.settings.services'),
        icon: 'M13 10V3L4 14h7v7l9-11h-7z'
    },
    {
        name: 'Settings',
        href: route('business.settings.profile'),
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'
    },
];

const currentRoute = computed(() => route().current());
</script>

<template>
    <div class="min-h-screen bg-base-100">
        <!-- Side Navigation -->
        <div class="drawer lg:drawer-open">
            <input id="business-drawer" type="checkbox" class="drawer-toggle" />
            
            <div class="drawer-content flex flex-col">
                <!-- Top Navigation for Mobile -->
                <div class="w-full navbar bg-base-100 lg:hidden">
                    <div class="flex-none">
                        <label for="business-drawer" class="btn btn-square btn-ghost drawer-button">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </label>
                    </div>
                    <div class="flex-1">
                        <ApplicationMark class="block h-9 w-auto" />
                    </div>
                </div>

                <!-- Page Content -->
                <div class="p-4 lg:p-8">
                    <slot></slot>
                </div>
            </div>

            <div class="drawer-side">
                <label for="business-drawer" class="drawer-overlay"></label>
                
                <div class="w-80 min-h-full bg-base-200 text-base-content">
                    <!-- Sidebar Header -->
                    <div class="p-4">
                        <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <ApplicationMark class="block h-9 w-auto" />
                            <span class="text-xl font-bold">Business Panel</span>
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <ul class="menu menu-lg p-4 gap-2">
                        <li v-for="item in navigation" :key="item.name">
                            <Link 
                                :href="item.href"
                                :class="{ 'active': currentRoute.startsWith(item.href.split('.')[0] + '.' + item.href.split('.')[1]) }"
                            >
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    class="h-5 w-5" 
                                    fill="none" 
                                    viewBox="0 0 24 24" 
                                    stroke="currentColor"
                                >
                                    <path 
                                        stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        stroke-width="2" 
                                        :d="item.icon"
                                    />
                                </svg>
                                {{ item.name }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template> 