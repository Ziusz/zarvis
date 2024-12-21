<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { formatCurrency, formatTime, formatDate } from '@/utils.js';
import DialogModal from '@/Components/DialogModal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    business: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    todayBookings: {
        type: Array,
        required: true,
    },
    recentActivity: {
        type: Array,
        required: true,
    },
});

const showBookingDetails = ref(false)
const selectedBooking = ref(null)

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    })
}

const getStatusClass = (status) => {
    const classes = {
        'confirmed': 'bg-success/20 text-success',
        'pending': 'bg-warning/20 text-warning',
        'cancelled': 'bg-error/20 text-error',
        'completed': 'bg-info/20 text-info'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const viewBookingDetails = (booking) => {
    selectedBooking.value = booking
    showBookingDetails.value = true
}
</script>

<template>
    <BusinessLayout>
        <Head :title="`${business.name} - Dashboard`" />

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
            <div class="flex items-center gap-4">
                <img 
                    :src="business.logo" 
                    :alt="business.name"
                    class="h-16 w-16 rounded-full"
                >
            </div>
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
                </div>
            </div>

            <!-- Today's Revenue -->
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-title">Today's Revenue</div>
                    <div class="stat-value">{{ formatCurrency(stats.today_revenue) }}</div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="stat-title">Monthly Revenue</div>
                    <div class="stat-value">{{ formatCurrency(stats.monthly_revenue) }}</div>
                </div>
            </div>

            <!-- Active Customers -->
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="stat-title">Active Customers</div>
                    <div class="stat-value">{{ stats.active_customers }}</div>
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
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Staff</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in todayBookings" :key="booking.id">
                                        <td>{{ formatTime(booking.start_time) }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 h-8 rounded-full">
                                                        <img :src="booking.customer.avatar" :alt="booking.customer.name">
                                                    </div>
                                                </div>
                                                {{ booking.customer.name }}
                                            </div>
                                        </td>
                                        <td>{{ booking.service.name }}</td>
                                        <td>
                                            <div v-if="booking.staff" class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 h-8 rounded-full">
                                                        <img :src="booking.staff.avatar" :alt="booking.staff.name">
                                                    </div>
                                                </div>
                                                {{ booking.staff.name }}
                                            </div>
                                            <span v-else class="text-base-content/50">Not assigned</span>
                                        </td>
                                        <td>
                                            <span class="px-2 py-1 rounded-lg text-sm" :class="getStatusClass(booking.status)">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <button 
                                                class="btn btn-ghost btn-sm"
                                                @click="viewBookingDetails(booking)"
                                            >
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
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
                        <div class="space-y-4">
                            <div v-for="activity in recentActivity" :key="activity.id" class="flex gap-4">
                                <div class="avatar">
                                    <div class="w-10 h-10 rounded-full">
                                        <img :src="activity.user.avatar" :alt="activity.user.name">
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">{{ activity.description }}</p>
                                    <p class="text-sm text-base-content/70">{{ formatDateTime(activity.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Details Modal -->
        <DialogModal :show="showBookingDetails" @close="showBookingDetails = false">
            <template #title>
                Booking Details
            </template>

            <template #content>
                <div v-if="selectedBooking" class="space-y-6">
                    <div class="space-y-2">
                        <h4 class="font-medium">Service</h4>
                        <p>{{ selectedBooking.service.name }}</p>
                        <p class="text-base-content/70">{{ selectedBooking.service.description }}</p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-medium">Date & Time</h4>
                        <p>{{ formatDateTime(selectedBooking.start_time) }}</p>
                        <span class="px-2 py-1 rounded-lg text-sm" :class="getStatusClass(selectedBooking.status)">
                            {{ selectedBooking.status }}
                        </span>
                    </div>

                    <div class="divider"></div>

                    <div class="space-y-2">
                        <h4 class="font-medium">Customer</h4>
                        <div class="flex items-center gap-2">
                            <img :src="selectedBooking.customer.avatar" :alt="selectedBooking.customer.name" class="w-10 h-10 rounded-full">
                            <div>
                                <p class="font-medium">{{ selectedBooking.customer.name }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedBooking.staff" class="space-y-2">
                        <h4 class="font-medium">Staff</h4>
                        <div class="flex items-center gap-2">
                            <img :src="selectedBooking.staff.avatar" :alt="selectedBooking.staff.name" class="w-10 h-10 rounded-full">
                            <div>
                                <p class="font-medium">{{ selectedBooking.staff.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showBookingDetails = false">
                    Close
                </SecondaryButton>
            </template>
        </DialogModal>
    </BusinessLayout>
</template> 