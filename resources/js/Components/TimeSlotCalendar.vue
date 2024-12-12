<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    minDate: {
        type: Date,
        default: () => new Date(),
    },
    maxDate: {
        type: Date,
        default: () => {
            const date = new Date();
            date.setMonth(date.getMonth() + 6);
            return date;
        },
    },
    availableDates: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    }
});

const emit = defineEmits(['update:modelValue']);

const currentDate = ref(new Date());
const selectedDate = ref(props.modelValue ? new Date(props.modelValue) : null);

// Pre-calculate today's date
const TODAY = new Date();
TODAY.setHours(0, 0, 0, 0);
const TODAY_TIME = TODAY.getTime();

// Create a map of available dates for O(1) lookup
const availableDatesMap = computed(() => {
    const map = new Map();
    props.availableDates.forEach(date => {
        map.set(date, true);
    });
    return map;
});

// Get visible dates (2 weeks at a time)
const visibleDates = computed(() => {
    const dates = [];
    const startDate = new Date(currentDate.value);
    startDate.setUTCHours(0, 0, 0, 0);

    for (let i = 0; i < 14; i++) {
        const date = new Date(startDate);
        date.setUTCDate(startDate.getUTCDate() + i);
        
        const dateString = date.toISOString().split('T')[0];
        
        dates.push({
            dateString,
            dayName: date.toLocaleDateString('default', { weekday: 'short' }),
            dayNumber: date.getUTCDate(),
            monthName: date.getUTCDate() === 1 || i === 0 
                ? date.toLocaleDateString('default', { month: 'short' }) 
                : null,
            isToday: date.getTime() === TODAY_TIME,
            isDisabled: isDateDisabled(date),
            hasSlots: availableDatesMap.value.has(dateString),
            timestamp: date.getTime()
        });
    }
    return dates;
});

const previousWeek = () => {
    const newDate = new Date(currentDate.value);
    newDate.setUTCDate(currentDate.value.getUTCDate() - 7);
    const minDate = new Date(props.minDate);
    minDate.setUTCHours(0, 0, 0, 0);
    
    if (newDate >= minDate) {
        currentDate.value = newDate;
    }
};

const nextWeek = () => {
    const newDate = new Date(currentDate.value);
    newDate.setUTCDate(currentDate.value.getUTCDate() + 7);
    const maxDate = new Date(props.maxDate);
    maxDate.setUTCHours(0, 0, 0, 0);
    
    if (newDate <= maxDate) {
        currentDate.value = newDate;
    }
};

const isDateDisabled = (date) => {
    const timestamp = date.getTime();
    const minTimestamp = props.minDate.getTime();
    const maxTimestamp = props.maxDate.getTime();
    return timestamp < minTimestamp || timestamp > maxTimestamp;
};

const selectedTimestamp = computed(() => 
    selectedDate.value ? new Date(selectedDate.value).setUTCHours(0, 0, 0, 0) : null
);

const selectDate = (dateString) => {
    selectedDate.value = new Date(dateString);
    emit('update:modelValue', dateString);
};

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        selectedDate.value = new Date(newValue);
        // Update current date to show selected date in view, but don't scroll
        const newDate = new Date(newValue);
        if (newDate < currentDate.value || newDate > new Date(currentDate.value.getTime() + 13 * 24 * 60 * 60 * 1000)) {
            currentDate.value = newDate;
        }
    } else {
        selectedDate.value = null;
    }
});

const currentMonthYear = computed(() => {
    const date = new Date(currentDate.value);
    return {
        month: date.toLocaleDateString('default', { month: 'long' }),
        year: date.getFullYear()
    };
});

const canGoBack = computed(() => {
    const newDate = new Date(currentDate.value);
    newDate.setUTCDate(currentDate.value.getUTCDate() - 7);
    return newDate >= props.minDate;
});

const canGoForward = computed(() => {
    const newDate = new Date(currentDate.value);
    newDate.setUTCDate(currentDate.value.getUTCDate() + 7);
    return newDate <= props.maxDate;
});
</script>

<template>
    <div class="calendar-wrapper">
        <!-- Month Display -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">
                {{ currentMonthYear.month }} {{ currentMonthYear.year }}
            </h3>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-8">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ error }}</span>
        </div>

        <!-- Calendar -->
        <div v-else class="relative">
            <button 
                class="btn btn-circle btn-sm btn-ghost absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4"
                @click="previousWeek"
                :disabled="!canGoBack"
                :class="{ 'opacity-50 cursor-not-allowed': !canGoBack }"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="flex gap-2 overflow-x-auto py-2 px-4 scrollbar-hide">
                <button
                    v-for="day in visibleDates"
                    :key="day.dateString"
                    class="flex-shrink-0 w-20 p-2 rounded-lg transition-all duration-200 relative"
                    :class="{
                        'bg-primary text-primary-content': day.timestamp === selectedTimestamp,
                        'bg-base-200 hover:bg-base-300': day.timestamp !== selectedTimestamp && !day.isDisabled,
                        'opacity-50 cursor-not-allowed': day.isDisabled
                    }"
                    :disabled="day.isDisabled"
                    @click="selectDate(day.dateString)"
                >
                    <div class="text-center">
                        <div v-if="day.monthName" class="text-xs mb-1 font-medium">
                            {{ day.monthName }}
                        </div>
                        <div class="text-xs mb-1">{{ day.dayName }}</div>
                        <div class="text-lg font-bold">{{ day.dayNumber }}</div>
                    </div>
                    <!-- Availability Indicator -->
                    <div 
                        class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 rounded-full transition-colors duration-200"
                        :class="{
                            'bg-success': day.hasSlots,
                            'bg-error': !day.hasSlots,
                            'opacity-50': day.isDisabled
                        }"
                    ></div>
                    <!-- Today Indicator -->
                    <div 
                        v-if="day.isToday" 
                        class="absolute -top-1 -right-1 w-3 h-3 bg-accent rounded-full"
                    ></div>
                </button>
            </div>

            <button 
                class="btn btn-circle btn-sm btn-ghost absolute right-0 top-1/2 -translate-y-1/2 translate-x-4"
                @click="nextWeek"
                :disabled="!canGoForward"
                :class="{ 'opacity-50 cursor-not-allowed': !canGoForward }"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style> 