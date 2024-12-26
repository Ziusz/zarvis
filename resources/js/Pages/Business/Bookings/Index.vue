<template>
    <BusinessLayout>
        <Head title="Bookings Management" />
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Bookings Management</h1>
            <Link 
                :href="route('business.calendar')"
                class="btn btn-primary"
            >
                Create Booking
            </Link>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="form-control">
                <input 
                    type="text" 
                    v-model="filters.search"
                    placeholder="Search bookings..."
                    class="input input-bordered w-full"
                    @input="handleSearch"
                />
            </div>
            <div class="form-control">
                <select v-model="filters.status" class="select select-bordered w-full">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="no-show">No Show</option>
                </select>
            </div>
            <div class="form-control">
                <select v-model="filters.service" class="select select-bordered w-full">
                    <option value="">All Services</option>
                    <option v-for="service in services" :key="service.id" :value="service.id">
                        {{ service.name }}
                    </option>
                </select>
            </div>
            <div class="form-control">
                <select v-model="filters.staff" class="select select-bordered w-full">
                    <option value="">All Staff</option>
                    <option v-for="member in staff" :key="member.id" :value="member.id">
                        {{ member.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="overflow-x-auto bg-base-100 rounded-lg shadow">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Staff</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!bookings || !Array.isArray(bookings) || bookings.length === 0">
                        <td colspan="8" class="text-center py-8 text-base-content/70">
                            No bookings found. Create your first booking by clicking the "Create Booking" button above.
                        </td>
                    </tr>
                    <template v-else v-for="booking in bookings" :key="booking?.id || Math.random()">
                        <tr v-if="!booking">
                            <td colspan="8" class="text-center text-base-content/70">
                                Invalid booking data
                            </td>
                        </tr>
                        <tr v-else>
                            <td>
                                <div v-if="booking.start_time">
                                    <div class="font-medium">{{ formatDate(booking.start_time) }}</div>
                                    <div class="text-sm text-base-content/70">{{ formatTime(booking.start_time) }}</div>
                                </div>
                                <div v-else class="text-base-content/50">No date set</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-3" v-if="booking.customer">
                                    <div class="avatar" v-if="booking.customer.avatar">
                                        <div class="w-8 h-8 rounded-full">
                                            <img :src="booking.customer.avatar" :alt="booking.customer.name">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ booking.customer?.name }}</div>
                                        <div class="text-sm text-base-content/70">{{ booking.customer?.email }}</div>
                                    </div>
                                </div>
                                <div v-else class="text-base-content/50">No customer data</div>
                            </td>
                            <td>
                                <div v-if="booking.service">
                                    <div class="font-medium">{{ booking.service.name }}</div>
                                    <div class="text-sm text-base-content/70">{{ booking.service.duration }} min</div>
                                </div>
                                <div v-else class="text-base-content/50">No service data</div>
                            </td>
                            <td>
                                <div v-if="booking.staff" class="flex items-center gap-3">
                                    <div class="avatar" v-if="booking.staff.avatar">
                                        <div class="w-8 h-8 rounded-full">
                                            <img :src="booking.staff.avatar" :alt="booking.staff?.name || 'Staff member'">
                                        </div>
                                    </div>
                                    <div class="font-medium">{{ booking.staff?.name || 'Unnamed staff' }}</div>
                                </div>
                                <div v-else class="text-base-content/50">Not assigned</div>
                            </td>
                            <td>
                                <div class="font-medium">{{ formatPrice(booking.total_price || 0) }}</div>
                                <div class="text-sm" v-if="booking.payment_status">
                                    <div class="badge badge-sm" :class="getPaymentStatusClass(booking.payment_status)">
                                        {{ booking.payment_status }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="badge" :class="getStatusClass(booking.status || 'pending')">
                                    {{ booking.status || 'pending' }}
                                </div>
                            </td>
                            <td>
                                <div class="badge" :class="getPaymentStatusClass(booking.payment_status || 'pending')">
                                    {{ booking.payment_status || 'pending' }}
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <button 
                                        class="btn btn-ghost btn-sm"
                                        @click="viewBooking(booking)"
                                    >
                                        View
                                    </button>
                                    <button 
                                        v-if="booking.status === 'pending'"
                                        class="btn btn-ghost btn-sm text-success"
                                        @click="confirmBooking(booking)"
                                    >
                                        Confirm
                                    </button>
                                    <button 
                                        v-if="['pending', 'confirmed'].includes(booking.status)"
                                        class="btn btn-ghost btn-sm text-error"
                                        @click="cancelBooking(booking)"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Booking Details Modal -->
        <dialog id="booking_details_modal" class="modal">
            <div class="modal-box" v-if="selectedBooking">
                <h3 class="font-bold text-lg mb-4">Booking Details</h3>
                <div class="space-y-6">
                    <!-- Service Details -->
                    <div v-if="selectedBooking?.service">
                        <h4 class="font-medium mb-2">Service</h4>
                        <div class="bg-base-200 p-4 rounded-lg">
                            <div class="font-medium">{{ selectedBooking.service.name }}</div>
                            <div class="text-sm text-base-content/70 mt-1">
                                {{ selectedBooking.service.duration }} minutes • {{ formatPrice(selectedBooking.total_price || 0) }}
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="grid grid-cols-2 gap-4" v-if="selectedBooking?.start_time">
                        <div>
                            <h4 class="font-medium mb-2">Date</h4>
                            <div>{{ formatDate(selectedBooking.start_time) }}</div>
                        </div>
                        <div>
                            <h4 class="font-medium mb-2">Time</h4>
                            <div>{{ formatTime(selectedBooking.start_time) }}</div>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div v-if="selectedBooking?.customer">
                        <h4 class="font-medium mb-2">Customer</h4>
                        <div class="flex items-center gap-4">
                            <div class="avatar" v-if="selectedBooking.customer.avatar">
                                <div class="w-12 h-12 rounded-full">
                                    <img :src="selectedBooking.customer.avatar" :alt="selectedBooking.customer.name">
                                </div>
                            </div>
                            <div>
                                <div class="font-medium">{{ selectedBooking.customer?.name }}</div>
                                <div class="text-sm text-base-content/70">{{ selectedBooking.customer?.email }}</div>
                                <div class="text-sm text-base-content/70">{{ selectedBooking.customer?.phone }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Info -->
                    <div v-if="selectedBooking?.staff">
                        <h4 class="font-medium mb-2">Staff Member</h4>
                        <div class="flex items-center gap-4">
                            <div class="avatar" v-if="selectedBooking.staff.avatar">
                                <div class="w-12 h-12 rounded-full">
                                    <img :src="selectedBooking.staff.avatar" :alt="selectedBooking.staff?.name || 'Staff member'">
                                </div>
                            </div>
                            <div>
                                <div class="font-medium">{{ selectedBooking.staff?.name || 'Unnamed staff' }}</div>
                                <div class="text-sm text-base-content/70">{{ selectedBooking.staff?.email }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Payment -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium mb-2">Status</h4>
                            <div class="badge" :class="getStatusClass(selectedBooking?.status || 'pending')">
                                {{ selectedBooking?.status || 'pending' }}
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium mb-2">Payment</h4>
                            <div class="badge" :class="getPaymentStatusClass(selectedBooking?.payment_status || 'pending')">
                                {{ selectedBooking?.payment_status || 'pending' }}
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="selectedBooking?.notes">
                        <h4 class="font-medium mb-2">Notes</h4>
                        <p class="text-base-content/70">{{ selectedBooking.notes }}</p>
                    </div>
                </div>
                <div class="modal-action">
                    <button class="btn" @click="closeBookingModal">Close</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    </BusinessLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import dayjs from 'dayjs';
import { debounce } from 'lodash';

const props = defineProps({
    bookings: {
        type: Array,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
    staff: {
        type: Array,
        required: true,
    },
});

// State
const filters = ref({
    search: '',
    status: '',
    service: '',
    staff: '',
});
const selectedBooking = ref(null);

// Methods
const formatDate = (date) => {
    if (!date) return 'No date';
    try {
        return dayjs(date).format('MMM D, YYYY');
    } catch (error) {
        console.error('Error formatting date:', error);
        return 'Invalid date';
    }
};

const formatTime = (time) => {
    if (!time) return 'No time';
    try {
        return dayjs(time).format('h:mm A');
    } catch (error) {
        console.error('Error formatting time:', error);
        return 'Invalid time';
    }
};

const formatPrice = (price) => {
    if (!price && price !== 0) return 'N/A';
    try {
        return new Intl.NumberFormat('pl-PL', {
            style: 'currency',
            currency: 'PLN'
        }).format(price);
    } catch (error) {
        console.error('Error formatting price:', error);
        return 'Invalid price';
    }
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'badge-warning',
        confirmed: 'badge-success',
        completed: 'badge-info',
        cancelled: 'badge-error',
        'no-show': 'badge-error',
    };
    return classes[status] || 'badge-ghost';
};

const getPaymentStatusClass = (status) => {
    const classes = {
        pending: 'badge-warning',
        paid: 'badge-success',
        refunded: 'badge-error',
    };
    return classes[status] || 'badge-ghost';
};

const handleSearch = debounce(() => {
    router.get(route('business.bookings.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

const viewBooking = (booking) => {
    if (!booking) return;
    selectedBooking.value = { ...booking }; // Create a copy of the booking
    document.getElementById('booking_details_modal')?.showModal();
};

const closeBookingModal = () => {
    document.getElementById('booking_details_modal')?.close();
    selectedBooking.value = null;
};

const confirmBooking = async (booking) => {
    try {
        await axios.post(route('business.bookings.status.update', booking.id), {
            status: 'confirmed'
        });
        router.reload();
    } catch (error) {
        console.error('Failed to confirm booking:', error);
    }
};

const cancelBooking = async (booking) => {
    if (!confirm('Are you sure you want to cancel this booking?')) return;
    
    try {
        await axios.post(route('business.bookings.status.update', booking.id), {
            status: 'cancelled'
        });
        router.reload();
    } catch (error) {
        console.error('Failed to cancel booking:', error);
    }
};

// Watch filters
watch(filters, () => {
    handleSearch();
}, { deep: true });
</script> 