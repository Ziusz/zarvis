<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatCurrency, formatDuration } from '@/utils';
import TimeSlotCalendar from '@/Components/TimeSlotCalendar.vue';
import StaffSelection from '@/Components/StaffSelection.vue';

const props = defineProps({
    business: Object,
    service: Object,
});

const selectedDate = ref(null);
const selectedTimeSlot = ref(null);
const selectedStaff = ref(null);
const participants = ref(1);
const loading = ref(false);
const error = ref(null);
const timeSlots = ref([]);
const availableStaff = ref([]);

const formatTime = (time) => {
    return new Date(`2000-01-01T${time}`).toLocaleTimeString([], { 
        hour: 'numeric', 
        minute: '2-digit' 
    });
};

watch(selectedDate, async (newDate) => {
    if (!newDate) {
        timeSlots.value = [];
        selectedTimeSlot.value = null;
        return;
    }
    
    loading.value = true;
    error.value = null;
    selectedTimeSlot.value = null;
    timeSlots.value = [];
    
    try {
        const response = await axios.post(route('bookings.time-slots'), {
            business_id: props.business.id,
            service_id: props.service.id,
            date: newDate,
            staff_id: selectedStaff.value?.id,
        });
        
        timeSlots.value = response.data.timeSlots;
        
        if (timeSlots.value.length === 0) {
            error.value = 'No time slots available for this date.';
        }
    } catch (e) {
        console.error('Error loading time slots:', e);
        error.value = e.response?.data?.message || 'Failed to load time slots. Please try again.';
    } finally {
        loading.value = false;
    }
});

watch(selectedTimeSlot, async (newTimeSlot) => {
    if (!newTimeSlot) {
        availableStaff.value = [];
        selectedStaff.value = null;
        return;
    }
    
    loading.value = true;
    error.value = null;
    selectedStaff.value = null;
    availableStaff.value = [];
    
    try {
        const response = await axios.post(route('bookings.staff'), {
            business_id: props.business.id,
            service_id: props.service.id,
            date: selectedDate.value,
            time_slot_id: newTimeSlot.id,
        });
        
        availableStaff.value = response.data.staff;
        
        if (availableStaff.value.length === 0) {
            error.value = 'No staff members available for this time slot.';
        }
    } catch (e) {
        console.error('Error loading staff:', e);
        error.value = e.response?.data?.message || 'Failed to load available staff. Please try again.';
    } finally {
        loading.value = false;
    }
});

const handleSubmit = async () => {
    if (!selectedTimeSlot.value) return;
    
    loading.value = true;
    error.value = null;
    
    try {
        const response = await axios.post(route('bookings.store'), {
            business_id: props.business.id,
            service_id: props.service.id,
            time_slot_id: selectedTimeSlot.value.id,
            staff_id: selectedStaff.value?.id,
            participants: participants.value,
        });
        
        window.location = route('bookings.show', response.data.booking.id);
    } catch (e) {
        console.error('Error creating booking:', e);
        error.value = e.response?.data?.message || 'Failed to create booking. Please try again.';
        loading.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Book ${service.name} at ${business.name}`" />

        <div class="container mx-auto px-4 py-8">
            <!-- Service Summary -->
            <div class="card bg-base-100 shadow-xl mb-8">
                <div class="card-body">
                    <div class="flex items-start gap-6">
                        <img 
                            :src="service.images?.main || '/images/placeholder.jpg'" 
                            :alt="service.name"
                            class="w-32 h-32 rounded-lg object-cover"
                        />
                        <div>
                            <h1 class="text-2xl font-bold mb-2">
                                {{ service.name }}
                                <span class="text-base-content/60">at</span>
                                {{ business.name }}
                            </h1>
                            <div class="flex flex-wrap gap-4 text-sm mb-4">
                                <div class="badge badge-primary">
                                    {{ formatDuration(service.duration) }}
                                </div>
                                <div class="badge">
                                    Up to {{ service.capacity }} people
                                </div>
                                <div class="badge badge-secondary">
                                    {{ formatCurrency(service.price) }}
                                </div>
                            </div>
                            <p class="text-base-content/70">{{ service.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-6">Book Your Session</h2>

                    <form @submit.prevent="handleSubmit" class="space-y-8">
                        <!-- Calendar -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Select Date</span>
                            </label>
                            <TimeSlotCalendar
                                v-model="selectedDate"
                                :min-date="new Date()"
                                :max-date="new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)"
                            />
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="flex justify-center">
                            <span class="loading loading-spinner loading-lg text-primary"></span>
                        </div>

                        <!-- Time Slots -->
                        <div v-if="selectedDate && !loading" class="form-control">
                            <label class="label">
                                <span class="label-text">Select Time</span>
                            </label>
                            <div v-if="timeSlots.length > 0" class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                <button
                                    v-for="slot in timeSlots"
                                    :key="slot.id"
                                    type="button"
                                    class="btn"
                                    :class="{
                                        'btn-primary': selectedTimeSlot?.id === slot.id,
                                        'btn-disabled': !slot.is_available
                                    }"
                                    @click="selectedTimeSlot = slot"
                                >
                                    {{ formatTime(slot.start_time) }}
                                </button>
                            </div>
                            <div v-else class="text-center py-4 text-base-content/70">
                                No time slots available for this date.
                            </div>
                        </div>

                        <!-- Staff Selection -->
                        <div v-if="selectedTimeSlot && !loading" class="form-control">
                            <label class="label">
                                <span class="label-text">Select Staff (Optional)</span>
                            </label>
                            <StaffSelection
                                v-model="selectedStaff"
                                :staff="availableStaff"
                            />
                        </div>

                        <!-- Participants -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Number of Participants</span>
                            </label>
                            <select 
                                v-model="participants"
                                class="select select-bordered w-full max-w-xs"
                            >
                                <option 
                                    v-for="n in service.capacity" 
                                    :key="n" 
                                    :value="n"
                                >
                                    {{ n }} {{ n === 1 ? 'person' : 'people' }}
                                </option>
                            </select>
                        </div>

                        <!-- Error Message -->
                        <div v-if="error" class="alert alert-error">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ error }}</span>
                        </div>

                        <!-- Submit Button -->
                        <div class="card-actions justify-end">
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                :class="{ 'loading': loading }"
                                :disabled="!selectedTimeSlot || loading"
                            >
                                Confirm Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 