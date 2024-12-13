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

const showCancellationModal = ref(false)
const showBookingDetails = ref(false)
const selectedBooking = ref(null)
const processing = ref(false)
const currentFilter = ref('all')

const filters = [
    { label: 'All', value: 'all' },
    { label: 'Today', value: 'today' },
    { label: 'This Week', value: 'week' },
    { label: 'This Month', value: 'month' }
]

const filteredBookings = computed(() => {
    if (!props.upcomingBookings) return []
    
    const now = new Date()
    return props.upcomingBookings.filter(booking => {
        const bookingDate = new Date(booking.start_time)
        switch (currentFilter.value) {
            case 'today':
                return bookingDate.toDateString() === now.toDateString()
            case 'week':
                const weekStart = new Date(now)
                weekStart.setDate(now.getDate() - now.getDay())
                const weekEnd = new Date(weekStart)
                weekEnd.setDate(weekStart.getDate() + 6)
                return bookingDate >= weekStart && bookingDate <= weekEnd
            case 'month':
                return bookingDate.getMonth() === now.getMonth() && 
                       bookingDate.getFullYear() === now.getFullYear()
            default:
                return true
        }
    })
})

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

const openCancellationModal = (booking) => {
    selectedBooking.value = booking
    showCancellationModal.value = true
    if (showBookingDetails.value) {
        showBookingDetails.value = false
    }
}

const cancelBooking = () => {
    if (!selectedBooking.value) return
    
    processing.value = true
    router.delete(route('bookings.cancel', selectedBooking.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCancellationModal.value = false
            selectedBooking.value = null
        },
        onFinish: () => {
            processing.value = false
        }
    })
}
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
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('business.settings.edit')"
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

                <!-- Quick Filters -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <button 
                        v-for="filter in filters" 
                        :key="filter.value"
                        @click="currentFilter = filter.value"
                        :class="[
                            'btn btn-sm',
                            currentFilter === filter.value ? 'btn-primary' : 'btn-ghost'
                        ]"
                    >
                        {{ filter.label }}
                    </button>
                </div>

                <!-- Upcoming Bookings -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Upcoming Bookings</h2>
                        <div class="space-y-4">
                            <div v-for="booking in filteredBookings" 
                                :key="booking.id" 
                                class="card bg-base-200"
                            >
                                <div class="card-body p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="avatar">
                                                <div class="w-12 h-12 rounded-full">
                                                    <img :src="booking.business.logo" :alt="booking.business.name">
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="font-medium text-base-content">{{ booking.business.name }}</h3>
                                                <p class="text-sm text-base-content/70">
                                                    {{ booking.service.name }} with {{ booking.staff?.name || 'Any Staff' }}
                                                </p>
                                                <p class="text-sm text-base-content/70">
                                                    {{ formatDateTime(booking.start_time) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <span :class="[
                                                'badge',
                                                {
                                                    'badge-warning': booking.status === 'pending',
                                                    'badge-success': booking.status === 'confirmed',
                                                    'badge-error': booking.status === 'cancelled',
                                                    'badge-info': booking.status === 'completed'
                                                }
                                            ]">
                                                {{ booking.status_label }}
                                            </span>
                                            <div class="flex gap-2">
                                                <Link 
                                                    :href="route('bookings.show', booking.id)"
                                                    class="btn btn-sm btn-ghost"
                                                >
                                                    View Details
                                                </Link>
                                                <button 
                                                    v-if="booking.can_be_cancelled" 
                                                    @click="openCancellationModal(booking)"
                                                    class="btn btn-sm btn-error btn-outline"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="filteredBookings.length === 0" class="text-center py-8">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-base-content/70">No upcoming bookings</p>
                                    <a href="/" class="btn btn-primary btn-sm mt-2">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancellation Modal -->
        <DialogModal :show="showCancellationModal" @close="showCancellationModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Cancel Booking
                </div>
            </template>
            <template #content>
                <div class="space-y-4">
                    <p>Are you sure you want to cancel this booking?</p>
                    <div v-if="selectedBooking" class="card bg-base-200">
                        <div class="card-body p-4">
                            <p><strong>Service:</strong> {{ selectedBooking.service.name }}</p>
                            <p><strong>Date & Time:</strong> {{ formatDateTime(selectedBooking.start_time) }}</p>
                            <p><strong>Business:</strong> {{ selectedBooking.business.name }}</p>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Cancellation policies may apply. Please check the business terms and conditions.</span>
                    </div>
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="showCancellationModal = false">
                    Never mind
                </SecondaryButton>
                <DangerButton 
                    class="ml-3" 
                    :class="{ 'opacity-25': processing }" 
                    :disabled="processing"
                    @click="cancelBooking"
                >
                    Cancel Booking
                </DangerButton>
            </template>
        </DialogModal>

        <!-- Booking Details Modal -->
        <DialogModal :show="showBookingDetails" @close="showBookingDetails = false">
            <template #title>
                Booking Details
            </template>
            <template #content>
                <div v-if="selectedBooking" class="space-y-6">
                    <!-- Business Info -->
                    <div class="flex items-center space-x-4">
                        <div class="avatar">
                            <div class="w-16 h-16 rounded-full">
                                <img :src="selectedBooking.business.logo" :alt="selectedBooking.business.name">
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium">{{ selectedBooking.business.name }}</h3>
                            <span :class="[
                                'badge mt-1',
                                {
                                    'badge-warning': selectedBooking.status === 'pending',
                                    'badge-success': selectedBooking.status === 'confirmed',
                                    'badge-error': selectedBooking.status === 'cancelled',
                                    'badge-info': selectedBooking.status === 'completed'
                                }
                            ]">
                                {{ selectedBooking.status_label }}
                            </span>
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div class="card bg-base-200">
                        <div class="card-body p-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Service</span>
                                <span class="font-medium">{{ selectedBooking.service.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Duration</span>
                                <span class="font-medium">{{ selectedBooking.service.duration }} minutes</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Date & Time</span>
                                <span class="font-medium">{{ formatDateTime(selectedBooking.start_time) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Staff</span>
                                <span class="font-medium">{{ selectedBooking.staff?.name || 'Any Staff' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-between w-full">
                    <button 
                        v-if="selectedBooking?.can_be_cancelled"
                        @click="openCancellationModal(selectedBooking)"
                        class="btn btn-error btn-outline"
                    >
                        Cancel Booking
                    </button>
                    <SecondaryButton @click="showBookingDetails = false">
                        Close
                    </SecondaryButton>
                </div>
            </template>
        </DialogModal>
    </AppLayout>
</template>
