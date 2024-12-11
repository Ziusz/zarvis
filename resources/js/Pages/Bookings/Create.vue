<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatCurrency, formatDate, formatDuration } from '@/utils';

const props = defineProps({
    business: Object,
    service: Object,
    venues: Array,
    dates: Array,
});

const form = useForm({
    business_id: props.business.id,
    service_id: props.service.id,
    venue_id: null,
    staff_id: null,
    date: null,
    time_slot: null,
    participants: 1,
    notes: '',
});

const currentStep = ref(1);
const selectedVenue = ref(null);
const selectedDate = ref(null);
const timeSlots = ref([]);
const availableStaff = ref([]);
const isLoading = ref(false);

// Step 1: Select Venue
const selectVenue = (venue) => {
    selectedVenue.value = venue;
    form.venue_id = venue.id;
    currentStep.value = 2;
};

// Step 2: Select Date
const selectDate = async (date) => {
    selectedDate.value = date;
    form.date = date;
    isLoading.value = true;

    try {
        const response = await axios.post(route('bookings.time-slots'), {
            business_id: form.business_id,
            venue_id: form.venue_id,
            service_id: form.service_id,
            date: date,
        });
        timeSlots.value = response.data;
        currentStep.value = 3;
    } catch (error) {
        console.error('Error fetching time slots:', error);
    } finally {
        isLoading.value = false;
    }
};

// Step 3: Select Time Slot
const selectTimeSlot = async (slot) => {
    form.time_slot = slot;
    isLoading.value = true;

    try {
        const response = await axios.post(route('bookings.staff'), {
            business_id: form.business_id,
            venue_id: form.venue_id,
            service_id: form.service_id,
            date: form.date,
            time_slot: slot,
        });
        availableStaff.value = response.data;
        currentStep.value = 4;
    } catch (error) {
        console.error('Error fetching available staff:', error);
    } finally {
        isLoading.value = false;
    }
};

// Step 4: Select Staff and Complete Booking
const selectStaff = (staffId) => {
    form.staff_id = staffId;
    currentStep.value = 5;
};

const submitForm = () => {
    form.post(route('bookings.store'), {
        onSuccess: () => {
            // Show success message and redirect
        },
    });
};

// Computed properties for validation
const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return !!form.venue_id;
        case 2:
            return !!form.date;
        case 3:
            return !!form.time_slot;
        case 4:
            return !!form.staff_id;
        case 5:
            return form.participants > 0;
        default:
            return false;
    }
});

// Watch for changes in participants
watch(() => form.participants, (newValue) => {
    if (newValue < 1) form.participants = 1;
    if (newValue > props.service.capacity) form.participants = props.service.capacity;
});
</script>

<template>
    <AppLayout>
        <Head :title="`Book ${service.name} at ${business.name}`" />

        <div class="container mx-auto px-4 py-8">
            <!-- Progress Steps -->
            <div class="steps w-full mb-8">
                <a class="step" :class="{ 'step-primary': currentStep >= 1 }">Choose Location</a>
                <a class="step" :class="{ 'step-primary': currentStep >= 2 }">Select Date</a>
                <a class="step" :class="{ 'step-primary': currentStep >= 3 }">Pick Time</a>
                <a class="step" :class="{ 'step-primary': currentStep >= 4 }">Choose Staff</a>
                <a class="step" :class="{ 'step-primary': currentStep >= 5 }">Confirm</a>
            </div>

            <!-- Service Summary -->
            <div class="card bg-base-200 mb-8">
                <div class="card-body">
                    <h2 class="card-title">{{ service.name }}</h2>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <div class="badge badge-primary">{{ formatDuration(service.duration) }}</div>
                        <div class="badge badge-secondary">{{ formatCurrency(service.price) }}</div>
                        <div class="badge">Up to {{ service.capacity }} people</div>
                    </div>
                    <p class="mt-4">{{ service.description }}</p>
                </div>
            </div>

            <!-- Step 1: Choose Location -->
            <div v-if="currentStep === 1" class="space-y-6">
                <h3 class="text-2xl font-bold mb-4">Choose a Location</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="venue in venues" :key="venue.id" 
                        class="card bg-base-100 hover:shadow-xl transition-shadow cursor-pointer"
                        @click="selectVenue(venue)"
                    >
                        <div class="card-body">
                            <h3 class="card-title">{{ venue.name }}</h3>
                            <p class="text-sm">{{ venue.address }}</p>
                            <div class="card-actions justify-end">
                                <button class="btn btn-primary btn-sm">Select</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Select Date -->
            <div v-else-if="currentStep === 2" class="space-y-6">
                <h3 class="text-2xl font-bold mb-4">Select a Date</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                    <button v-for="date in dates" :key="date" 
                        class="btn btn-outline"
                        :class="{ 'btn-primary': selectedDate === date }"
                        @click="selectDate(date)"
                    >
                        {{ formatDate(date, { weekday: 'short', month: 'short', day: 'numeric' }) }}
                    </button>
                </div>
            </div>

            <!-- Step 3: Select Time -->
            <div v-else-if="currentStep === 3" class="space-y-6">
                <h3 class="text-2xl font-bold mb-4">Choose a Time</h3>
                <div v-if="isLoading" class="flex justify-center">
                    <span class="loading loading-spinner loading-lg"></span>
                </div>
                <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <button v-for="slot in timeSlots" :key="slot.id"
                        class="btn btn-outline"
                        :class="{ 'btn-primary': form.time_slot?.id === slot.id }"
                        :disabled="!slot.is_available"
                        @click="selectTimeSlot(slot)"
                    >
                        {{ slot.start_time }}
                    </button>
                </div>
            </div>

            <!-- Step 4: Select Staff -->
            <div v-else-if="currentStep === 4" class="space-y-6">
                <h3 class="text-2xl font-bold mb-4">Choose Your Provider</h3>
                <div v-if="isLoading" class="flex justify-center">
                    <span class="loading loading-spinner loading-lg"></span>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="staff in availableStaff" :key="staff.id"
                        class="card bg-base-100 hover:shadow-xl transition-shadow cursor-pointer"
                        @click="selectStaff(staff.id)"
                    >
                        <div class="card-body">
                            <div class="flex items-center gap-4">
                                <div class="avatar">
                                    <div class="w-16 rounded-full">
                                        <img :src="staff.avatar" :alt="staff.name">
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">{{ staff.name }}</h3>
                                    <p class="text-sm opacity-70">{{ staff.role }}</p>
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-4">
                                <button class="btn btn-primary btn-sm">Select</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Confirm Booking -->
            <div v-else-if="currentStep === 5" class="space-y-6">
                <h3 class="text-2xl font-bold mb-4">Confirm Your Booking</h3>
                
                <div class="card bg-base-100">
                    <div class="card-body">
                        <!-- Booking Summary -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="font-bold">Service:</span>
                                <span>{{ service.name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold">Location:</span>
                                <span>{{ selectedVenue.name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold">Date & Time:</span>
                                <span>
                                    {{ formatDate(form.date) }} at {{ form.time_slot.start_time }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold">Duration:</span>
                                <span>{{ formatDuration(service.duration) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold">Price:</span>
                                <span>{{ formatCurrency(service.price * form.participants) }}</span>
                            </div>

                            <div class="divider"></div>

                            <!-- Number of Participants -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Number of Participants</span>
                                </label>
                                <div class="join">
                                    <button 
                                        class="btn join-item"
                                        @click="form.participants--"
                                        :disabled="form.participants <= 1"
                                    >-</button>
                                    <input 
                                        type="number" 
                                        v-model="form.participants"
                                        class="input input-bordered join-item w-20 text-center"
                                        min="1"
                                        :max="service.capacity"
                                    >
                                    <button 
                                        class="btn join-item"
                                        @click="form.participants++"
                                        :disabled="form.participants >= service.capacity"
                                    >+</button>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Additional Notes</span>
                                </label>
                                <textarea 
                                    v-model="form.notes"
                                    class="textarea textarea-bordered h-24"
                                    placeholder="Any special requests or notes for your booking?"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="card-actions justify-end mt-6">
                            <button 
                                class="btn btn-primary"
                                :class="{ 'loading': form.processing }"
                                :disabled="form.processing"
                                @click="submitForm"
                            >
                                Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8">
                <button 
                    v-if="currentStep > 1"
                    class="btn btn-outline"
                    @click="currentStep--"
                >
                    Back
                </button>
                <button 
                    v-if="currentStep < 5"
                    class="btn btn-primary ml-auto"
                    :disabled="!canProceed"
                    @click="currentStep++"
                >
                    Continue
                </button>
            </div>
        </div>
    </AppLayout>
</template> 