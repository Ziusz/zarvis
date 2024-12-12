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
const slideDirection = ref('none');
const isAnimating = ref(false);

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

// Helper function to get UTC date without time
const getUTCDate = (date) => {
    const newDate = new Date(date);
    newDate.setUTCHours(0, 0, 0, 0);
    return newDate;
};

const canGoBack = computed(() => {
    const newDate = getUTCDate(currentDate.value);
    newDate.setUTCDate(newDate.getUTCDate() - 7);
    const minDate = getUTCDate(props.minDate);
    return newDate.getTime() >= minDate.getTime();
});

const canGoForward = computed(() => {
    const newDate = getUTCDate(currentDate.value);
    newDate.setUTCDate(newDate.getUTCDate() + 7);
    const maxDate = getUTCDate(props.maxDate);
    return newDate.getTime() <= maxDate.getTime();
});

const previousWeek = () => {
    if (isAnimating.value || !canGoBack.value) return;
    
    const newDate = getUTCDate(currentDate.value);
    newDate.setUTCDate(newDate.getUTCDate() - 7);
    
    isAnimating.value = true;
    slideDirection.value = 'right';
    setTimeout(() => {
        currentDate.value = newDate;
        slideDirection.value = 'none';
        isAnimating.value = false;
    }, 300);
};

const nextWeek = () => {
    if (isAnimating.value || !canGoForward.value) return;
    
    const newDate = getUTCDate(currentDate.value);
    newDate.setUTCDate(newDate.getUTCDate() + 7);
    
    isAnimating.value = true;
    slideDirection.value = 'left';
    setTimeout(() => {
        currentDate.value = newDate;
        slideDirection.value = 'none';
        isAnimating.value = false;
    }, 300);
};

// Get visible dates (2 weeks at a time)
const visibleDates = computed(() => {
    const dates = [];
    const startDate = getUTCDate(currentDate.value);

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

const isDateDisabled = (date) => {
    const timestamp = getUTCDate(date).getTime();
    const minTimestamp = getUTCDate(props.minDate).getTime();
    const maxTimestamp = getUTCDate(props.maxDate).getTime();
    return timestamp < minTimestamp || timestamp > maxTimestamp;
};

const selectedTimestamp = computed(() => 
    selectedDate.value ? new Date(selectedDate.value).setUTCHours(0, 0, 0, 0) : null
);

const selectDate = (dateString) => {
    if (!availableDatesMap.value.has(dateString)) {
        return;
    }
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

</script>

<template>
    <div class="calendar-wrapper overflow-hidden">
        <!-- Month Display -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">
                {{ currentMonthYear.month }} {{ currentMonthYear.year }}
            </h3>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col justify-center items-center py-8 space-y-4">
            <div class="kawaii-spinner">
                <div class="kawaii-face">
                    <div class="kawaii-eyes"></div>
                    <div class="kawaii-mouth"></div>
                </div>
            </div>
            <span class="text-sm text-base-content/70 animate-bounce">Loading dates...</span>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ error }}</span>
        </div>

        <!-- Calendar -->
        <div v-else class="relative px-8">
            <button 
                type="button"
                class="btn btn-circle btn-sm btn-ghost absolute left-0 top-1/2 -translate-y-1/2 z-10 hover:bg-base-200"
                @click.prevent="previousWeek"
                :disabled="!canGoBack || isAnimating"
                :class="{ 'opacity-50 cursor-not-allowed': !canGoBack || isAnimating }"
                aria-label="Previous week"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div 
                class="flex gap-2 overflow-hidden py-2"
                :class="{
                    'slide-left': slideDirection === 'left',
                    'slide-right': slideDirection === 'right'
                }"
                role="grid"
                aria-label="Calendar dates"
            >
                <button
                    type="button"
                    v-for="day in visibleDates"
                    :key="day.dateString"
                    class="flex-shrink-0 w-20 p-2 rounded-lg transition-all duration-200 relative hover:scale-105"
                    :class="{
                        'bg-primary text-primary-content scale-105': day.timestamp === selectedTimestamp,
                        'bg-base-200 hover:bg-base-300': day.timestamp !== selectedTimestamp && !day.isDisabled && day.hasSlots,
                        'bg-base-200 cursor-not-allowed opacity-50': !day.hasSlots,
                        'opacity-50 cursor-not-allowed': day.isDisabled
                    }"
                    :disabled="day.isDisabled || !day.hasSlots"
                    @click.prevent="selectDate(day.dateString)"
                    :aria-label="`${day.dayName} ${day.dayNumber} ${day.monthName || ''} ${day.hasSlots ? 'Available' : 'Not available'}`"
                    :aria-selected="day.timestamp === selectedTimestamp"
                    role="gridcell"
                >
                    <!-- Sparkles (only show when available and selected) -->
                    <div v-if="day.timestamp === selectedTimestamp && day.hasSlots" class="sparkles-container">
                        <div class="sparkle sparkle-1">✦</div>
                        <div class="sparkle sparkle-2">✧</div>
                        <div class="sparkle sparkle-3">⋆</div>
                        <div class="sparkle sparkle-4">✦</div>
                    </div>

                    <div class="text-center">
                        <div v-if="day.monthName" class="text-xs mb-1 font-medium">
                            {{ day.monthName }}
                        </div>
                        <div class="text-xs mb-1">{{ day.dayName }}</div>
                        <div class="text-lg font-bold">{{ day.dayNumber }}</div>
                    </div>
                    <!-- Availability Indicator with sparkle on hover -->
                    <div 
                        class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 rounded-full transition-all duration-300 group"
                        :class="{
                            'bg-success scale-125 sparkle-on-hover': day.hasSlots,
                            'bg-error': !day.hasSlots,
                            'opacity-50 scale-75': day.isDisabled
                        }"
                    >
                        <div v-if="day.hasSlots" class="sparkle-ring"></div>
                    </div>
                    <!-- Today Indicator with sparkle -->
                    <div 
                        v-if="day.isToday" 
                        class="absolute -top-1 -right-1 w-3 h-3 bg-accent rounded-full animate-pulse"
                    >
                        <div class="sparkle-ping"></div>
                    </div>
                </button>
            </div>

            <button 
                type="button"
                class="btn btn-circle btn-sm btn-ghost absolute right-0 top-1/2 -translate-y-1/2 z-10 hover:bg-base-200"
                @click.prevent="nextWeek"
                :disabled="!canGoForward || isAnimating"
                :class="{ 'opacity-50 cursor-not-allowed': !canGoForward || isAnimating }"
                aria-label="Next week"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.kawaii-spinner {
    width: 60px;
    height: 60px;
    background: hsl(var(--p));
    border-radius: 50%;
    position: relative;
    animation: spin 2s linear infinite;
}

.kawaii-face {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 30px;
    height: 20px;
}

.kawaii-eyes {
    position: relative;
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    box-shadow: 16px 0 white;
    animation: blink 3s infinite;
}

.kawaii-mouth {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 12px;
    height: 6px;
    border: 2px solid white;
    border-radius: 50%;
    border-top: 0;
    border-left-color: transparent;
    border-right-color: transparent;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes blink {
    0%, 100% { transform: scaleY(1); }
    50% { transform: scaleY(0.1); }
}

.slide-left {
    animation: slideLeft 0.3s ease-in-out;
}

.slide-right {
    animation: slideRight 0.3s ease-in-out;
}

@keyframes slideLeft {
    from { transform: translateX(0); }
    to { transform: translateX(-100%); }
}

@keyframes slideRight {
    from { transform: translateX(0); }
    to { transform: translateX(100%); }
}

/* Add hover effects */
.btn:not(:disabled):hover {
    transform: scale(1.1);
    transition: transform 0.2s ease-in-out;
}

/* Add focus styles */
.btn:focus {
    outline: none;
    box-shadow: 0 0 0 2px hsl(var(--p));
}

/* Smooth transitions */
.btn {
    transition: all 0.2s ease-in-out;
}

/* Sparkles Animation */
.sparkles-container {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.sparkle {
    position: absolute;
    color: hsl(var(--p));
    animation: sparkle 1.5s linear infinite;
    opacity: 0;
}

.sparkle-1 { top: -10%; left: 0; animation-delay: 0s; }
.sparkle-2 { top: 0; right: -10%; animation-delay: 0.3s; }
.sparkle-3 { bottom: -10%; right: 0; animation-delay: 0.6s; }
.sparkle-4 { bottom: 0; left: -10%; animation-delay: 0.9s; }

@keyframes sparkle {
    0%, 100% { 
        transform: scale(0) rotate(0deg); 
        opacity: 0;
    }
    50% { 
        transform: scale(1.2) rotate(180deg); 
        opacity: 1;
    }
}

/* Sparkle Ring for Available Dates */
.sparkle-ring {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid hsl(var(--su));
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.3s ease-in-out;
}

/* Sparkle on hover for available dates */
.sparkle-on-hover:hover .sparkle-ring {
    opacity: 1;
    transform: scale(1.5);
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Today indicator sparkle */
.sparkle-ping {
    position: absolute;
    inset: -2px;
    border-radius: 50%;
    border: 2px solid hsl(var(--a));
    opacity: 0.7;
    animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(1.5);
    }
}

@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Kawaii loading animation improvements */
.kawaii-spinner {
    /* ... existing styles ... */
    &::before,
    &::after {
        content: '✨';
        position: absolute;
        font-size: 1.2rem;
        animation: float 3s ease-in-out infinite;
    }
    
    &::before {
        top: -20px;
        left: 0;
        animation-delay: 0.5s;
    }
    
    &::after {
        bottom: -20px;
        right: 0;
        animation-delay: 1s;
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-10px) rotate(180deg);
    }
}

/* Improve hover transitions */
.btn:not(:disabled):hover {
    transform: scale(1.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    &::before {
        content: '✨';
        position: absolute;
        font-size: 1rem;
        animation: buttonSparkle 1s ease-in-out infinite;
    }
}

@keyframes buttonSparkle {
    0%, 100% {
        transform: translate(-50%, -50%) scale(0) rotate(0deg);
        opacity: 0;
    }
    50% {
        transform: translate(-50%, -50%) scale(1) rotate(180deg);
        opacity: 1;
    }
}

/* Smooth slide transitions */
.slide-left, .slide-right {
    position: relative;
    &::after {
        content: '✨';
        position: absolute;
        font-size: 1.5rem;
        top: 50%;
        opacity: 0;
        animation: slideSparkle 0.3s ease-in-out;
    }
}

.slide-left::after {
    right: -20px;
}

.slide-right::after {
    left: -20px;
}

@keyframes slideSparkle {
    0% {
        transform: translateY(-50%) scale(0);
        opacity: 0;
    }
    50% {
        transform: translateY(-50%) scale(1.2);
        opacity: 1;
    }
    100% {
        transform: translateY(-50%) scale(0);
        opacity: 0;
    }
}

/* Remove vertical scrollbar */
.calendar-wrapper {
    position: relative;
    width: 100%;
}

/* Improve button states */
.btn:not(:disabled):not(.cursor-not-allowed):hover {
    transform: scale(1.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Prevent text selection */
.calendar-wrapper {
    user-select: none;
}

/* Fix sparkle container overflow */
.sparkles-container {
    overflow: hidden;
    pointer-events: none;
}

/* Improve button accessibility */
.btn-circle {
    width: 2rem;
    height: 2rem;
    min-height: 2rem;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease-in-out;
}

.btn-circle:focus-visible {
    outline: 2px solid hsl(var(--p));
    outline-offset: 2px;
}

/* Ensure arrows are always visible */
.calendar-wrapper {
    position: relative;
    width: 100%;
    margin: 0 auto;
}

/* Add hover effect for navigation buttons */
.btn-ghost:not(:disabled):hover {
    background-color: hsl(var(--b2));
    transform: scale(1.1);
}

/* Improve animation container */
.slide-left, .slide-right {
    position: relative;
    will-change: transform;
}
</style> 