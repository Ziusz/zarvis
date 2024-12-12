<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatCurrency, formatTime, formatDate } from '@/utils.js';

const props = defineProps({
    upcomingBookings: {
        type: Array,
        default: () => [],
    },
    pastBookings: {
        type: Array,
        default: () => [],
    },
    business: {
        type: Object,
        default: null,
    },
    stats: {
        type: Object,
        default: null,
    },
    todayBookings: {
        type: Array,
        default: () => [],
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
});

const isBusinessOwner = computed(() => !!props.business);
</script>

<template>
    <AppLayout>
        <Head :title="isBusinessOwner ? `${business.name} - Dashboard` : 'Dashboard'" />

        <!-- Business Owner View -->
        <div v-if="isBusinessOwner" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold">
                            Welcome back, {{ business.name }}!
                        </h1>
                        <p class="text-base-content/70">
                            Here's what's happening with your business today.
                        </p>
                    </div>
                    <img 
                        :src="business.logo" 
                        :alt="business.name"
                        class="h-16 w-16 rounded-full"
                    >
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Bookings -->
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-figure text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="stat-title">Total Bookings</div>
                            <div class="stat-value">{{ stats.total_bookings }}</div>
                            <div class="stat-desc">{{ stats.today_bookings }} today</div>
                        </div>
                    </div>

                    <!-- Active Staff -->
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-figure text-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="stat-title">Active Staff</div>
                            <div class="stat-value">{{ stats.active_staff }}</div>
                            <div class="stat-desc">{{ stats.total_services }} services</div>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-figure text-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="stat-title">Total Revenue</div>
                            <div class="stat-value">{{ formatCurrency(stats.total_revenue) }}</div>
                            <div class="stat-desc">{{ formatCurrency(stats.this_month_revenue) }} this month</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Today's Bookings -->
                    <div class="lg:col-span-2">
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <h2 class="card-title">Today's Bookings</h2>
                                
                                <div class="overflow-x-auto">
                                    <table class="table">
                                        <!-- ... (rest of the business owner table code) ... -->
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="lg:col-span-1">
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <h2 class="card-title">Recent Activity</h2>
                                <!-- ... (rest of the business owner activity code) ... -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client View -->
        <div v-else class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold">Welcome back!</h1>
                    <p class="text-base-content/70">
                        Manage your bookings and discover new services.
                    </p>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="card bg-primary text-primary-content">
                        <div class="card-body">
                            <h2 class="card-title">Book a Service</h2>
                            <p>Find and book your next appointment</p>
                            <div class="card-actions justify-end">
                                <a href="/" class="btn btn-ghost">Browse Services</a>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-secondary text-secondary-content">
                        <div class="card-body">
                            <h2 class="card-title">Upcoming Bookings</h2>
                            <p>{{ upcomingBookings.length }} upcoming appointments</p>
                            <div class="card-actions justify-end">
                                <button class="btn btn-ghost">View All</button>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-accent text-accent-content">
                        <div class="card-body">
                            <h2 class="card-title">Past Bookings</h2>
                            <p>{{ pastBookings.length }} past appointments</p>
                            <div class="card-actions justify-end">
                                <button class="btn btn-ghost">View History</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Bookings -->
                <div class="card bg-base-100 shadow-xl mb-8">
                    <div class="card-body">
                        <h2 class="card-title">Upcoming Bookings</h2>
                        
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Business</th>
                                        <th>Service</th>
                                        <th>Staff</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in upcomingBookings" :key="booking.id">
                                        <td>
                                            <div class="font-medium">{{ formatDate(booking.start_time) }}</div>
                                            <div class="text-sm opacity-60">{{ formatTime(booking.start_time) }}</div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img :src="booking.business.logo" :alt="booking.business.name">
                                                    </div>
                                                </div>
                                                <span>{{ booking.business.name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            {{ booking.service.name }}
                                            <div class="text-xs opacity-60">
                                                {{ booking.service.duration }} min
                                            </div>
                                        </td>
                                        <td v-if="booking.staff">
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img :src="booking.staff.avatar" :alt="booking.staff.name">
                                                    </div>
                                                </div>
                                                <span>{{ booking.staff.name }}</span>
                                            </div>
                                        </td>
                                        <td v-else>
                                            <span class="text-base-content/50">Not assigned</span>
                                        </td>
                                        <td>
                                            <div class="badge" :class="{
                                                'badge-warning': booking.status === 'pending',
                                                'badge-success': booking.status === 'confirmed',
                                                'badge-error': booking.status === 'cancelled',
                                                'badge-info': booking.status === 'completed',
                                            }">
                                                {{ booking.status_label }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a :href="route('bookings.show', booking.id)" class="btn btn-ghost btn-sm">
                                                    View
                                                </a>
                                                <button 
                                                    v-if="booking.can_be_cancelled"
                                                    class="btn btn-error btn-sm btn-outline"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="upcomingBookings.length === 0">
                                        <td colspan="6" class="text-center py-4">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="text-base-content/50">No upcoming bookings</span>
                                                <a href="/" class="btn btn-primary btn-sm mt-2">Book Now</a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
