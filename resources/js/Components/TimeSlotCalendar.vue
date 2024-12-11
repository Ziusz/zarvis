<script setup>
import { ref, watch } from 'vue';

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
        default: () => new Date(Date.now() + 30 * 24 * 60 * 60 * 1000),
    },
});

const emit = defineEmits(['update:modelValue']);

const currentMonth = ref(new Date());
const selectedDate = ref(props.modelValue ? new Date(props.modelValue) : null);

watch(() => props.modelValue, (newValue) => {
    selectedDate.value = newValue ? new Date(newValue) : null;
});

watch(selectedDate, (newValue) => {
    if (!newValue) {
        emit('update:modelValue', null);
        return;
    }
    // Set time to noon to avoid timezone issues
    const date = new Date(newValue);
    date.setHours(12, 0, 0, 0);
    emit('update:modelValue', date.toISOString().split('T')[0]);
});

const daysInMonth = (date) => {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const days = [];

    // Add empty days for the start of the month
    for (let i = 0; i < firstDay.getDay(); i++) {
        days.push(null);
    }

    // Add the days of the month
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const currentDate = new Date(year, month, i, 12, 0, 0, 0); // Set to noon
        days.push(currentDate);
    }

    return days;
};

const isDateDisabled = (date) => {
    if (!date) return true;
    
    // Set all dates to noon for comparison
    const compareDate = new Date(date);
    compareDate.setHours(12, 0, 0, 0);
    
    const minCompareDate = new Date(props.minDate);
    minCompareDate.setHours(12, 0, 0, 0);
    
    const maxCompareDate = new Date(props.maxDate);
    maxCompareDate.setHours(12, 0, 0, 0);
    
    return compareDate < minCompareDate || compareDate > maxCompareDate;
};

const formatDate = (date) => {
    if (!date) return '';
    return date.getDate();
};

const previousMonth = () => {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() - 1,
        1,
        12, // Set to noon
        0,
        0,
        0
    );
};

const nextMonth = () => {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() + 1,
        1,
        12, // Set to noon
        0,
        0,
        0
    );
};

const isSelected = (date) => {
    if (!date || !selectedDate.value) return false;
    const compareDate = new Date(date);
    compareDate.setHours(12, 0, 0, 0);
    const compareSelected = new Date(selectedDate.value);
    compareSelected.setHours(12, 0, 0, 0);
    return compareDate.getTime() === compareSelected.getTime();
};

const isToday = (date) => {
    if (!date) return false;
    const today = new Date();
    today.setHours(12, 0, 0, 0);
    const compareDate = new Date(date);
    compareDate.setHours(12, 0, 0, 0);
    return compareDate.getTime() === today.getTime();
};

const selectDate = (date) => {
    if (!date || isDateDisabled(date)) return;
    const newDate = new Date(date);
    newDate.setHours(12, 0, 0, 0);
    selectedDate.value = newDate;
};
</script>

<template>
    <div class="calendar bg-base-100 rounded-lg shadow-lg p-4">
        <!-- Calendar Header -->
        <div class="flex justify-between items-center mb-4">
            <button 
                class="btn btn-circle btn-sm btn-ghost"
                @click="previousMonth"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            
            <h3 class="text-lg font-semibold">
                {{ currentMonth.toLocaleDateString('default', { month: 'long', year: 'numeric' }) }}
            </h3>
            
            <button 
                class="btn btn-circle btn-sm btn-ghost"
                @click="nextMonth"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-1">
            <!-- Weekday Headers -->
            <div 
                v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                :key="day"
                class="text-center text-sm font-medium py-2"
            >
                {{ day }}
            </div>

            <!-- Calendar Days -->
            <button
                v-for="(date, index) in daysInMonth(currentMonth)"
                :key="index"
                class="aspect-square flex items-center justify-center rounded-full text-sm transition-colors"
                :class="{
                    'cursor-not-allowed opacity-30': isDateDisabled(date),
                    'btn-primary': isSelected(date),
                    'btn-ghost hover:btn-primary': !isSelected(date) && !isDateDisabled(date),
                    'ring-2 ring-primary ring-offset-2': isToday(date),
                }"
                :disabled="isDateDisabled(date)"
                @click="selectDate(date)"
            >
                {{ formatDate(date) }}
            </button>
        </div>
    </div>
</template> 