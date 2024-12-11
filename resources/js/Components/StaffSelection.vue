<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null,
    },
    staff: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedStaff = ref(props.modelValue);

watch(() => props.modelValue, (newValue) => {
    selectedStaff.value = newValue;
});

watch(selectedStaff, (newValue) => {
    emit('update:modelValue', newValue);
});

const selectStaff = (staff) => {
    selectedStaff.value = selectedStaff.value?.id === staff.id ? null : staff;
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- No Preference Option -->
        <div 
            class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow cursor-pointer"
            :class="{ 'ring-2 ring-primary': !selectedStaff }"
            @click="selectStaff(null)"
        >
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="avatar placeholder">
                        <div class="w-16 rounded-full bg-neutral-focus text-neutral-content">
                            <span class="text-2xl">?</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">No Preference</h3>
                        <p class="text-sm opacity-70">Any available staff member</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Members -->
        <div 
            v-for="member in staff"
            :key="member.id"
            class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow cursor-pointer"
            :class="{ 'ring-2 ring-primary': selectedStaff?.id === member.id }"
            @click="selectStaff(member)"
        >
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="avatar">
                        <div class="w-16 rounded-full">
                            <img 
                                :src="member.profile_photo_url" 
                                :alt="member.name"
                                class="object-cover"
                            />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">{{ member.name }}</h3>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <div 
                                v-for="specialty in member.specialties?.slice(0, 2)"
                                :key="specialty"
                                class="badge badge-sm"
                            >
                                {{ specialty }}
                            </div>
                            <div 
                                v-if="member.specialties?.length > 2"
                                class="badge badge-sm"
                            >
                                +{{ member.specialties.length - 2 }}
                            </div>
                        </div>
                        <div class="flex items-center gap-1 mt-2 text-sm text-base-content/70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            {{ member.average_rating }} ({{ member.reviews_count }})
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Staff Available -->
        <div 
            v-if="staff.length === 0"
            class="col-span-full text-center py-8"
        >
            <div class="text-3xl mb-2">😢</div>
            <h4 class="text-lg font-semibold">No Staff Available</h4>
            <p class="text-base-content/70">Please try selecting a different time slot.</p>
        </div>
    </div>
</template> 