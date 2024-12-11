<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatCurrency, formatDate, formatTime, formatDuration } from '@/utils.js';

const props = defineProps({
    booking: {
        type: Object,
        required: true
    }
});

const showCancelModal = ref(false);
const showRescheduleModal = ref(false);
const cancelReason = ref('');
const isProcessing = ref(false);

const getStatusColor = (status) => {
    return {
        'pending': 'warning',
        'confirmed': 'success',
        'completed': 'info',
        'cancelled': 'error',
        'no-show': 'error'
    }[status] || 'ghost';
};

const getPaymentStatusColor = (status) => {
    return {
        'pending': 'warning',
        'paid': 'success',
        'refunded': 'info'
    }[status] || 'ghost';
};

const cancelBooking = () => {
    isProcessing.value = true;
    
    axios.delete(route('bookings.cancel', props.booking.id), {
        data: { reason: cancelReason.value }
    }).then(() => {
        showCancelModal.value = false;
        window.location.reload();
    }).catch(error => {
        console.error('Error cancelling booking:', error);
    }).finally(() => {
        isProcessing.value = false;
    });
};

const rescheduleBooking = () => {
    window.location.href = route('bookings.create', {
        business: props.booking.business.slug,
        service: props.booking.service.id,
        reschedule: props.booking.id
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Booking #${booking.id} - ${booking.service.name}`" />

        <div class="container mx-auto px-4 py-8">
            <!-- Booking Status Banner -->
            <div class="alert mb-8" :class="`alert-${getStatusColor(booking.status)}`">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="booking.status === 'confirmed'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <path v-else-if="booking.status === 'cancelled'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold">Booking {{ booking.status_label }}</h3>
                    <div class="text-sm">
                        {{ booking.status === 'confirmed' ? 'Your booking has been confirmed!' : '' }}
                        {{ booking.status === 'cancelled' ? 'This booking has been cancelled.' : '' }}
                        {{ booking.status === 'pending' ? 'Your booking is pending confirmation.' : '' }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Booking Details -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Service Details -->
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title">Service Details</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="font-semibold mb-2">{{ booking.service.name }}</h3>
                                    <p class="text-base-content/70 text-sm">{{ booking.service.description }}</p>
                                    <div class="flex flex-wrap gap-2 mt-4">
                                        <div class="badge badge-primary">{{ formatDuration(booking.service.duration) }}</div>
                                        <div class="badge badge-secondary">{{ formatCurrency(booking.service.price) }}</div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-sm font-medium">Date & Time</span>
                                        <p class="text-base-content/70">
                                            {{ formatDate(booking.start_time) }} at {{ formatTime(booking.start_time) }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium">Duration</span>
                                        <p class="text-base-content/70">{{ formatDuration(booking.service.duration) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium">Participants</span>
                                        <p class="text-base-content/70">{{ booking.participants }} person(s)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Staff -->
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title mb-6">Location & Staff</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Location -->
                                <div>
                                    <h3 class="font-semibold mb-2">{{ booking.venue.name }}</h3>
                                    <p class="text-base-content/70">{{ booking.venue.address }}</p>
                                    <div class="mt-4">
                                        <a 
                                            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(booking.venue.address)}`"
                                            target="_blank"
                                            class="btn btn-outline btn-sm"
                                        >
                                            View on Map
                                        </a>
                                    </div>
                                </div>

                                <!-- Staff -->
                                <div v-if="booking.staff">
                                    <div class="flex items-center gap-4">
                                        <div class="avatar">
                                            <div class="w-16 h-16 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                                <img :src="booking.staff.avatar" :alt="booking.staff.name">
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">{{ booking.staff.name }}</h3>
                                            <p class="text-sm text-base-content/70">{{ booking.staff.role }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="booking.notes" class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title">Additional Notes</h2>
                            <p class="text-base-content/70">{{ booking.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="space-y-6">
                    <!-- Payment Status -->
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title">Payment</h2>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span>Status</span>
                                    <div class="badge" :class="`badge-${getPaymentStatusColor(booking.payment_status)}`">
                                        {{ booking.payment_status_label }}
                                    </div>
                                </div>
                                <div class="flex justify-between items-center font-semibold">
                                    <span>Total Amount</span>
                                    <span>{{ formatCurrency(booking.total_price) }}</span>
                                </div>
                                <div v-if="booking.payment_method" class="flex justify-between items-center text-sm">
                                    <span>Payment Method</span>
                                    <span class="capitalize">{{ booking.payment_method }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title">Actions</h2>
                            <div class="space-y-4">
                                <button 
                                    v-if="booking.can_be_rescheduled"
                                    class="btn btn-primary btn-block"
                                    @click="showRescheduleModal = true"
                                >
                                    Reschedule
                                </button>
                                <button 
                                    v-if="booking.can_be_cancelled"
                                    class="btn btn-error btn-outline btn-block"
                                    @click="showCancelModal = true"
                                >
                                    Cancel Booking
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <dialog class="modal" :class="{ 'modal-open': showCancelModal }">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Cancel Booking</h3>
                <p class="py-4">Are you sure you want to cancel this booking? This action cannot be undone.</p>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Cancellation Reason</span>
                    </label>
                    <textarea 
                        v-model="cancelReason"
                        class="textarea textarea-bordered h-24"
                        placeholder="Please provide a reason for cancellation..."
                    ></textarea>
                </div>

                <div class="modal-action">
                    <button 
                        class="btn btn-error"
                        :class="{ 'loading': isProcessing }"
                        :disabled="isProcessing"
                        @click="cancelBooking"
                    >
                        Cancel Booking
                    </button>
                    <button 
                        class="btn btn-ghost"
                        :disabled="isProcessing"
                        @click="showCancelModal = false"
                    >
                        Close
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>

        <!-- Reschedule Modal -->
        <dialog class="modal" :class="{ 'modal-open': showRescheduleModal }">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Reschedule Booking</h3>
                <p class="py-4">Would you like to reschedule this booking to a different time?</p>
                
                <div class="modal-action">
                    <button 
                        class="btn btn-primary"
                        :class="{ 'loading': isProcessing }"
                        :disabled="isProcessing"
                        @click="rescheduleBooking"
                    >
                        Reschedule
                    </button>
                    <button 
                        class="btn btn-ghost"
                        :disabled="isProcessing"
                        @click="showRescheduleModal = false"
                    >
                        Cancel
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    </AppLayout>
</template> 