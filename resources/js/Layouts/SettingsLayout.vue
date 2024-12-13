<template>
    <AppLayout>
        <Head :title="`${business.name} - Settings`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-base-100 shadow-xl rounded-box">
                    <!-- Navigation Tabs -->
                    <div class="tabs tabs-boxed bg-base-200 p-2 rounded-t-box">
                        <Link
                            v-for="tab in tabs"
                            :key="tab.key"
                            :href="route(`business.settings.${tab.key}`)"
                            class="tab tab-lg gap-2"
                            :class="{ 'tab-active': route().current(`business.settings.${tab.key}`) }"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :d="tab.icon"
                                />
                            </svg>
                            {{ tab.label }}
                        </Link>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    business: {
        type: Object,
        required: true,
    },
});

const tabs = [
    { 
        key: 'profile', 
        label: 'Business Profile', 
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' 
    },
    { 
        key: 'hours', 
        label: 'Working Hours', 
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' 
    },
    { 
        key: 'services', 
        label: 'Services', 
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' 
    },
    { 
        key: 'staff', 
        label: 'Staff Management', 
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' 
    },
];</script> 