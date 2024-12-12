<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted, onMounted } from 'vue';
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
const selectedPeriod = ref('morning');
const availableDates = ref([]);
const loadingDates = ref(false);

// Limit cache size to prevent memory issues
const MAX_CACHE_SIZE = 30;
const timeSlotsCache = ref(new Map());

const timePeriods = [
    { id: 'morning', label: 'Morning', start: '06:00', end: '12:00' },
    { id: 'afternoon', label: 'Afternoon', start: '12:00', end: '17:00' },
    { id: 'evening', label: 'Evening', start: '17:00', end: '23:59' }
];

// Format time helper function
const formatTime = (time) => {
    return new Date(`2000-01-01T${time}`).toLocaleTimeString([], { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true
    });
};

// Pre-calculate period time ranges
const periodRanges = computed(() => {
    return timePeriods.map(period => ({
        ...period,
        startTime: new Date(`2000-01-01T${period.start}`).getTime(),
        endTime: new Date(`2000-01-01T${period.end}`).getTime()
    }));
});

// Memoized function to get time slots by period
const getTimeSlotsByPeriod = computed(() => {
    const slotsMap = new Map();
    
    return (period) => {
        if (!timeSlots.value.length) return [];
        
        // Return cached result if available
        if (slotsMap.has(period)) {
            return slotsMap.get(period);
        }
        
        const periodConfig = periodRanges.value.find(p => p.id === period);
        if (!periodConfig) return [];

        const result = timeSlots.value.filter(slot => {
            const time = new Date(`2000-01-01T${slot.start_time}`).getTime();
            return time >= periodConfig.startTime && time <= periodConfig.endTime;
        });

        // Cache the result
        slotsMap.set(period, result);
        return result;
    };
});

// Maintain cache size
const maintainCacheSize = () => {
    if (timeSlotsCache.value.size > MAX_CACHE_SIZE) {
        const keys = Array.from(timeSlotsCache.value.keys());
        const oldestKey = keys[0];
        timeSlotsCache.value.delete(oldestKey);
    }
};

// Debounced time slot loading with automatic cache cleanup
let loadTimeoutId = null;
const loadTimeSlots = async (date) => {
    if (loadTimeoutId) {
        clearTimeout(loadTimeoutId);
    }

    loadTimeoutId = setTimeout(async () => {
        if (!date) {
            timeSlots.value = [];
            selectedTimeSlot.value = null;
            return;
        }

        const cacheKey = `${date}_${selectedStaff.value?.id || 'no-staff'}`;
        if (timeSlotsCache.value.has(cacheKey)) {
            timeSlots.value = timeSlotsCache.value.get(cacheKey);
            return;
        }
        
        loading.value = true;
        error.value = null;
        selectedTimeSlot.value = null;
        
        try {
            const response = await axios.post(route('bookings.time-slots'), {
                business_id: props.business.id,
                service_id: props.service.id,
                date: date,
                staff_id: selectedStaff.value?.id,
            });
            
            const slots = response.data.timeSlots.map(slot => ({
                ...slot,
                remaining_capacity: slot.capacity - slot.booked
            }));

            maintainCacheSize();
            timeSlotsCache.value.set(cacheKey, slots);
            timeSlots.value = slots;
            
            // Update available dates if needed
            if (slots.length > 0 && !availableDates.value.includes(date)) {
                availableDates.value = [...availableDates.value, date];
            } else if (slots.length === 0 && availableDates.value.includes(date)) {
                availableDates.value = availableDates.value.filter(d => d !== date);
            }
            
            if (slots.length === 0) {
                error.value = 'No time slots available for this date.';
            } else {
                const availablePeriod = periodRanges.value.find(period => 
                    getTimeSlotsByPeriod.value(period.id).length > 0
                );
                if (availablePeriod) {
                    selectedPeriod.value = availablePeriod.id;
                }
            }
        } catch (e) {
            console.error('Error loading time slots:', e);
            error.value = e.response?.data?.message || 'Failed to load time slots. Please try again.';
        } finally {
            loading.value = false;
        }
    }, 300);
};

// Clear cache when component unmounts
onUnmounted(() => {
    if (loadTimeoutId) {
        clearTimeout(loadTimeoutId);
    }
    timeSlotsCache.value.clear();
});

watch(selectedDate, loadTimeSlots);

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

// Load available dates for the next month
const loadAvailableDates = async () => {
    if (loadingDates.value) return;
    
    loadingDates.value = true;
    try {
        const response = await axios.post(route('bookings.available-dates'), {
            business_id: props.business.id,
            service_id: props.service.id,
            staff_id: selectedStaff.value?.id,
        });
        
        availableDates.value = response.data.dates;
    } catch (e) {
        console.error('Error loading available dates:', e);
        error.value = 'Failed to load available dates. Please try again.';
    } finally {
        loadingDates.value = false;
    }
};

// Load available dates on component mount and when staff changes
onMounted(loadAvailableDates);
watch(selectedStaff, loadAvailableDates);
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
                            <TimeSlotCalendar
                                v-model="selectedDate"
                                :min-date="new Date()"
                                :max-date="new Date(Date.now() + 180 * 24 * 60 * 60 * 1000)"
                                :available-dates="availableDates"
                                :loading="loadingDates"
                                :error="error"
                            />
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="flex justify-center">
                            <span class="loading loading-spinner loading-lg text-primary"></span>
                        </div>

                        <!-- Time Slots -->
                        <div v-if="selectedDate && !loading" class="form-control">
                            <!-- Time Slot Tabs -->
                            <div class="tabs tabs-boxed mb-4">
                                <button 
                                    v-for="period in timePeriods" 
                                    :key="period.id"
                                    type="button"
                                    class="tab"
                                    :class="{ 'tab-active': selectedPeriod === period.id }"
                                    @click="selectedPeriod = period.id"
                                >
                                    {{ period.label }}
                                    <span 
                                        v-if="getTimeSlotsByPeriod(period.id).length" 
                                        class="badge badge-sm ml-2"
                                    >
                                        {{ getTimeSlotsByPeriod(period.id).length }}
                                    </span>
                                </button>
                            </div>

                            <!-- Time Slots Grid -->
                            <div v-if="getTimeSlotsByPeriod(selectedPeriod).length" class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                <button
                                    v-for="slot in getTimeSlotsByPeriod(selectedPeriod)"
                                    :key="slot.id"
                                    type="button"
                                    class="btn"
                                    :class="{
                                        'btn-primary': selectedTimeSlot?.id === slot.id,
                                        'btn-disabled': !slot.is_available,
                                        'btn-outline': slot.is_available && selectedTimeSlot?.id !== slot.id
                                    }"
                                    @click="selectedTimeSlot = slot"
                                >
                                    {{ formatTime(slot.start_time) }}
                                    <span v-if="slot.remaining_capacity" class="badge badge-sm ml-1">
                                        {{ slot.remaining_capacity }}
                                    </span>
                                </button>
                            </div>
                            <div v-else class="text-center py-4 text-base-content/70">
                                No time slots available for this period.
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