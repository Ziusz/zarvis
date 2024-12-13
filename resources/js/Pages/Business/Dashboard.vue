<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
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
    <AppLayout>
        <Head :title="`${business.name} - Dashboard`" />

        <div class="py-12">
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
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('business.settings')"
                            class="btn btn-ghost btn-circle"
                            v-tooltip="'Business Settings'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </Link>
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
                                                        <img :src="booking.customer.avatar" :alt="booking.customer.name" class="w-8 h-8 rounded-full">
                                                        <span>{{ booking.customer.name }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ booking.service.name }}</td>
                                                <td>
                                                    <div v-if="booking.staff" class="flex items-center gap-2">
                                                        <img :src="booking.staff.avatar" :alt="booking.staff.name" class="w-8 h-8 rounded-full">
                                                        <span>{{ booking.staff.name }}</span>
                                                    </div>
                                                    <span v-else>Any Staff</span>
                                                </td>
                                                <td>
                                                    <span :class="['badge', getStatusClass(booking.status)]">
                                                        {{ booking.status_label }}
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
                                            <tr v-if="todayBookings.length === 0">
                                                <td colspan="6" class="text-center py-4">
                                                    No bookings for today
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
                                    <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start gap-4">
                                        <div class="flex-shrink-0">
                                            <span :class="['badge', getStatusClass(activity.status)]">
                                                {{ activity.status_label }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ activity.customer }} booked {{ activity.service }}</p>
                                            <p class="text-sm text-base-content/70">
                                                with {{ activity.staff || 'Any Staff' }}
                                            </p>
                                            <p class="text-xs text-base-content/50">
                                                {{ formatDateTime(activity.time) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div v-if="recentActivity.length === 0" class="text-center py-4">
                                        No recent activity
                                    </div>
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
                <div v-if="selectedBooking" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">{{ selectedBooking.service.name }}</h3>
                            <p class="text-sm text-base-content/70">
                                {{ formatDateTime(selectedBooking.start_time) }}
                            </p>
                        </div>
                        <span :class="['badge', getStatusClass(selectedBooking.status)]">
                            {{ selectedBooking.status_label }}
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

                    <div class="space-y-2">
                        <h4 class="font-medium">Service Details</h4>
                        <p>Duration: {{ selectedBooking.service.duration }} minutes</p>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showBookingDetails = false">
                    Close
                </SecondaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template> 