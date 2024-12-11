<script setup>
import { ref, computed } from 'vue';
import { formatTime } from '@/utils';

const props = defineProps({
    slots: {
        type: Array,
        required: true
    },
    selectedSlot: {
        type: Object,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['select']);

// Group slots by time period (morning, afternoon, evening)
const groupedSlots = computed(() => {
    const groups = {
        morning: [],   // 6:00 AM - 11:59 AM
        afternoon: [], // 12:00 PM - 4:59 PM
        evening: []    // 5:00 PM - 11:59 PM
    };

    props.slots.forEach(slot => {
        const hour = new Date(`2000-01-01 ${slot.start_time}`).getHours();
        
        if (hour >= 6 && hour < 12) {
            groups.morning.push(slot);
        } else if (hour >= 12 && hour < 17) {
            groups.afternoon.push(slot);
        } else if (hour >= 17) {
            groups.evening.push(slot);
        }
    });

    return groups;
});

const isSelected = (slot) => {
    return props.selectedSlot?.id === slot.id;
};

const selectSlot = (slot) => {
    if (slot.is_available) {
        emit('select', slot);
    }
};

const getSlotClass = (slot) => {
    return {
        'btn-primary': isSelected(slot),
        'btn-disabled': !slot.is_available,
        'opacity-50': !slot.is_available,
        'hover:bg-primary/20': slot.is_available && !isSelected(slot)
    };
};
</script>

<template>
    <div class="space-y-8">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center min-h-[200px]">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <template v-else>
            <!-- Morning Slots -->
            <div v-if="groupedSlots.morning.length" class="space-y-4">
                <h4 class="text-lg font-semibold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                    </svg>
                    Morning
                </h4>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <button
                        v-for="slot in groupedSlots.morning"
                        :key="slot.id"
                        class="btn btn-outline"
                        :class="getSlotClass(slot)"
                        @click="selectSlot(slot)"
                    >
                        {{ formatTime(slot.start_time) }}
                    </button>
                </div>
            </div>

            <!-- Afternoon Slots -->
            <div v-if="groupedSlots.afternoon.length" class="space-y-4">
                <h4 class="text-lg font-semibold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm0-10a1 1 0 011 1v3a1 1 0 01-.293.707l-2 2a1 1 0 01-1.414-1.414L8.586 9H7a1 1 0 110-2h3z" clip-rule="evenodd" />
                    </svg>
                    Afternoon
                </h4>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <button
                        v-for="slot in groupedSlots.afternoon"
                        :key="slot.id"
                        class="btn btn-outline"
                        :class="getSlotClass(slot)"
                        @click="selectSlot(slot)"
                    >
                        {{ formatTime(slot.start_time) }}
                    </button>
                </div>
            </div>

            <!-- Evening Slots -->
            <div v-if="groupedSlots.evening.length" class="space-y-4">
                <h4 class="text-lg font-semibold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                    Evening
                </h4>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <button
                        v-for="slot in groupedSlots.evening"
                        :key="slot.id"
                        class="btn btn-outline"
                        :class="getSlotClass(slot)"
                        @click="selectSlot(slot)"
                    >
                        {{ formatTime(slot.start_time) }}
                    </button>
                </div>
            </div>

            <!-- No Available Slots -->
            <div v-if="!slots.length" class="text-center py-8">
                <div class="text-3xl mb-2">😢</div>
                <h4 class="text-lg font-semibold">No Available Time Slots</h4>
                <p class="text-base-content/70">Please try selecting a different date.</p>
            </div>
        </template>
    </div>
</template> 