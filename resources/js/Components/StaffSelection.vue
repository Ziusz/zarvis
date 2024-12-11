<script setup>
import { ref, computed } from 'vue';
import { StarIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    staff: {
        type: Array,
        required: true
    },
    selectedStaffId: {
        type: Number,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['select']);

const selectStaff = (staffMember) => {
    emit('select', staffMember.id);
};

const getStaffCardClass = (staffMember) => {
    return {
        'ring-2 ring-primary': props.selectedStaffId === staffMember.id,
        'hover:shadow-xl': props.selectedStaffId !== staffMember.id
    };
};

const getRatingColor = (rating) => {
    if (rating >= 4.5) return 'text-success';
    if (rating >= 4.0) return 'text-warning';
    return 'text-base-content/70';
};
</script>

<template>
    <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center min-h-[200px]">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <template v-else>
            <!-- Staff Grid -->
            <div v-if="staff.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="member in staff" :key="member.id"
                    class="card bg-base-100 shadow-md transition-all duration-300 cursor-pointer"
                    :class="getStaffCardClass(member)"
                    @click="selectStaff(member)"
                >
                    <div class="card-body">
                        <!-- Staff Header -->
                        <div class="flex items-start gap-4">
                            <div class="avatar">
                                <div class="w-16 h-16 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                    <img :src="member.avatar" :alt="member.name">
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="card-title">{{ member.name }}</h3>
                                <p class="text-sm text-base-content/70">{{ member.role }}</p>
                                
                                <!-- Rating -->
                                <div class="flex items-center gap-1 mt-1">
                                    <div class="rating rating-sm">
                                        <template v-for="i in 5" :key="i">
                                            <input 
                                                type="radio" 
                                                :class="['mask mask-star-2', getRatingColor(member.rating)]"
                                                :checked="Math.round(member.rating) === i"
                                                disabled
                                            />
                                        </template>
                                    </div>
                                    <span class="text-sm text-base-content/70">
                                        ({{ member.reviews_count }} reviews)
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Specialties -->
                        <div class="mt-4">
                            <h4 class="font-semibold text-sm mb-2">Specialties</h4>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="specialty in member.specialties" :key="specialty"
                                    class="badge badge-outline"
                                >
                                    {{ specialty }}
                                </span>
                            </div>
                        </div>

                        <!-- Experience -->
                        <div class="mt-4">
                            <h4 class="font-semibold text-sm mb-2">Experience</h4>
                            <p class="text-sm text-base-content/70">{{ member.experience }}</p>
                        </div>

                        <!-- Languages -->
                        <div v-if="member.languages?.length" class="mt-4">
                            <h4 class="font-semibold text-sm mb-2">Languages</h4>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="language in member.languages" :key="language"
                                    class="badge badge-ghost"
                                >
                                    {{ language }}
                                </span>
                            </div>
                        </div>

                        <!-- Next Available -->
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm text-base-content/70">Next Available</span>
                            <span class="text-sm font-medium">{{ member.next_available }}</span>
                        </div>

                        <!-- Selection Button -->
                        <div class="card-actions justify-end mt-4">
                            <button 
                                class="btn btn-primary btn-sm"
                                :class="{ 'btn-outline': props.selectedStaffId !== member.id }"
                            >
                                {{ props.selectedStaffId === member.id ? 'Selected' : 'Select' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Staff Available -->
            <div v-else class="text-center py-8">
                <div class="text-3xl mb-2">😔</div>
                <h4 class="text-lg font-semibold">No Staff Available</h4>
                <p class="text-base-content/70">Please try selecting a different time slot.</p>
            </div>
        </template>
    </div>
</template> 