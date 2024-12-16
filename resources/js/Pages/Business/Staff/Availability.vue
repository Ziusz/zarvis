<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold">
                        {{ singleStaffMode ? `${staff[0].name}'s Availability` : 'Staff Availability' }}
                    </h1>
                    <p v-if="singleStaffMode" class="text-base-content/70 mt-1">
                        Manage availability and working hours
                    </p>
                </div>
                <div class="flex gap-2">
                    <button 
                        v-if="!singleStaffMode"
                        class="btn btn-primary"
                        @click="syncAllWithBusinessHours"
                        :disabled="loading"
                    >
                        Sync All with Business Hours
                    </button>
                    <Link
                        v-if="singleStaffMode"
                        :href="route('business.staff.index')"
                        class="btn"
                    >
                        Back to Staff List
                    </Link>
                </div>
            </div>

            <!-- Staff List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="member in staff" :key="member.id" class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex justify-between items-center">
                            <h2 class="card-title">{{ member.name }}</h2>
                            <div class="badge badge-secondary">{{ member.role }}</div>
                        </div>

                        <!-- Calendar -->
                        <div class="mt-4">
                            <div class="flex justify-between items-center mb-4">
                                <button 
                                    class="btn btn-sm"
                                    @click="previousWeek"
                                >
                                    ←
                                </button>
                                <span class="text-sm font-medium">
                                    {{ formatDateRange(currentWeekStart, currentWeekEnd) }}
                                </span>
                                <button 
                                    class="btn btn-sm"
                                    @click="nextWeek"
                                >
                                    →
                                </button>
                            </div>

                            <!-- Week View -->
                            <div class="space-y-2">
                                <div 
                                    v-for="date in weekDates" 
                                    :key="date.format('YYYY-MM-DD')"
                                    class="flex items-center gap-4"
                                >
                                    <div class="w-24 text-sm">
                                        {{ date.format('ddd, MMM D') }}
                                    </div>
                                    <div class="flex-1">
                                        <div 
                                            v-if="getAvailability(member.id, date)"
                                            class="flex flex-wrap gap-2"
                                        >
                                            <div 
                                                v-for="slot in getAvailability(member.id, date)"
                                                :key="slot.id"
                                                class="badge"
                                                :class="{
                                                    'badge-success': slot.is_available && slot.status === 'available',
                                                    'badge-warning': slot.status === 'unavailable',
                                                    'badge-error': slot.status === 'on-leave'
                                                }"
                                            >
                                                {{ formatTimeRange(slot.start_time, slot.end_time) }}
                                            </div>
                                        </div>
                                        <div v-else class="text-sm text-base-content/60">
                                            No availability set
                                        </div>
                                    </div>
                                    <button 
                                        class="btn btn-sm btn-ghost"
                                        @click="editAvailability(member, date)"
                                    >
                                        Edit
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-actions justify-end mt-4">
                            <button 
                                class="btn btn-sm btn-outline"
                                @click="syncWithBusinessHours(member)"
                                :disabled="loading"
                            >
                                Sync with Business Hours
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Availability Modal -->
            <dialog id="edit_availability_modal" class="modal">
                <div class="modal-box">
                    <h3 class="font-bold text-lg mb-4" v-if="selectedStaff && selectedDate">
                        Edit Availability for {{ selectedStaff.name }}
                        <div class="text-sm font-normal text-base-content/70">
                            {{ selectedDate.format('dddd, MMMM D, YYYY') }}
                        </div>
                    </h3>

                    <form @submit.prevent="saveAvailability" class="space-y-4">
                        <div v-for="(slot, index) in editingSlots" :key="index" class="flex items-center gap-4">
                            <div class="form-control flex-1">
                                <label class="label">
                                    <span class="label-text">Start Time</span>
                                </label>
                                <input 
                                    type="time" 
                                    v-model="slot.start_time"
                                    class="input input-bordered"
                                    required
                                />
                            </div>
                            <div class="form-control flex-1">
                                <label class="label">
                                    <span class="label-text">End Time</span>
                                </label>
                                <input 
                                    type="time" 
                                    v-model="slot.end_time"
                                    class="input input-bordered"
                                    required
                                />
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Status</span>
                                </label>
                                <select v-model="slot.status" class="select select-bordered">
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    <option value="on-leave">On Leave</option>
                                </select>
                            </div>
                            <button 
                                type="button"
                                class="btn btn-ghost btn-sm"
                                @click="removeSlot(index)"
                                :disabled="editingSlots.length === 1"
                            >
                                ×
                            </button>
                        </div>

                        <div class="flex justify-between">
                            <button 
                                type="button"
                                class="btn btn-ghost btn-sm"
                                @click="addSlot"
                            >
                                Add Time Slot
                            </button>
                            <div class="flex gap-2">
                                <button 
                                    type="button"
                                    class="btn"
                                    @click="closeModal"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="loading"
                                >
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
    business: Object,
    staff: Array,
    singleStaffMode: {
        type: Boolean,
        default: false
    }
});

const loading = ref(false);
const currentWeekStart = ref(dayjs().startOf('week'));
const selectedStaff = ref(null);
const selectedDate = ref(null);
const editingSlots = ref([]);

// Computed
const currentWeekEnd = computed(() => currentWeekStart.value.endOf('week'));
const weekDates = computed(() => {
    const dates = [];
    let current = currentWeekStart.value;
    while (current.isBefore(currentWeekEnd.value) || current.isSame(currentWeekEnd.value, 'day')) {
        dates.push(current);
        current = current.add(1, 'day');
    }
    return dates;
});

// Methods
const formatDateRange = (start, end) => {
    if (start.format('MMM YYYY') === end.format('MMM YYYY')) {
        return `${start.format('MMM D')} - ${end.format('D, YYYY')}`;
    }
    return `${start.format('MMM D')} - ${end.format('MMM D, YYYY')}`;
};

const formatTimeRange = (start, end) => {
    return `${dayjs(start, 'HH:mm:ss').format('h:mm A')} - ${dayjs(end, 'HH:mm:ss').format('h:mm A')}`;
};

const getAvailability = (staffId, date) => {
    const member = props.staff.find(s => s.id === staffId);
    return member?.availabilities?.[date.format('YYYY-MM-DD')] || null;
};

const previousWeek = () => {
    currentWeekStart.value = currentWeekStart.value.subtract(1, 'week');
};

const nextWeek = () => {
    currentWeekStart.value = currentWeekStart.value.add(1, 'week');
};

const editAvailability = (staff, date) => {
    selectedStaff.value = staff;
    selectedDate.value = date;
    
    const existing = getAvailability(staff.id, date);
    editingSlots.value = existing?.length ? existing.map(slot => ({
        start_time: dayjs(slot.start_time, 'HH:mm:ss').format('HH:mm'),
        end_time: dayjs(slot.end_time, 'HH:mm:ss').format('HH:mm'),
        is_available: slot.is_available,
        status: slot.status,
    })) : [{
        start_time: '09:00',
        end_time: '17:00',
        is_available: true,
        status: 'available',
    }];

    document.getElementById('edit_availability_modal').showModal();
};

const closeModal = () => {
    document.getElementById('edit_availability_modal').close();
    selectedStaff.value = null;
    selectedDate.value = null;
    editingSlots.value = [];
};

const addSlot = () => {
    editingSlots.value.push({
        start_time: '09:00',
        end_time: '17:00',
        is_available: true,
        status: 'available',
    });
};

const removeSlot = (index) => {
    editingSlots.value.splice(index, 1);
};

const saveAvailability = async () => {
    if (!selectedStaff.value || !selectedDate.value) return;

    loading.value = true;
    try {
        await axios.put(route('business.staff.availability.update', selectedStaff.value.id), {
            date: selectedDate.value.format('YYYY-MM-DD'),
            availabilities: editingSlots.value.map(slot => ({
                start_time: `${slot.start_time}:00`,
                end_time: `${slot.end_time}:00`,
                is_available: slot.status === 'available',
                status: slot.status,
            })),
        });

        closeModal();
        // Refresh data
        window.location.reload();
    } catch (error) {
        console.error('Failed to save availability:', error);
    } finally {
        loading.value = false;
    }
};

const syncWithBusinessHours = async (staff) => {
    loading.value = true;
    try {
        await axios.post(route('business.staff.availability.sync', staff.id), {
            start_date: currentWeekStart.value.format('YYYY-MM-DD'),
            end_date: currentWeekEnd.value.format('YYYY-MM-DD'),
        });

        // Refresh data
        window.location.reload();
    } catch (error) {
        console.error('Failed to sync availability:', error);
    } finally {
        loading.value = false;
    }
};

const syncAllWithBusinessHours = async () => {
    loading.value = true;
    try {
        await Promise.all(props.staff.map(staff => 
            syncWithBusinessHours(staff)
        ));
    } finally {
        loading.value = false;
    }
};
</script> 