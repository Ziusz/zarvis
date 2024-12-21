<template>
    <BusinessLayout>
        <Head title="Calendar" />
        
        <div class="h-[calc(100vh-4rem)]">
            <BusinessCalendar
                v-model="selectedDate"
                :appointments="appointments"
                :services="business.services"
                :staff="business.staff"
                @select-appointment="handleAppointmentSelect"
                @create-appointment="handleAppointmentCreate"
                @move-appointment="handleAppointmentMove"
            />
        </div>

        <!-- Appointment Details Modal -->
        <dialog id="appointment_details_modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4" v-if="selectedAppointment">
                    {{ selectedAppointment.title }}
                </h3>
                <div v-if="selectedAppointment" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Date</label>
                            <p>{{ formatDate(selectedAppointment.start_time) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Time</label>
                            <p>{{ formatTime(selectedAppointment.start_time) }} - {{ formatTime(selectedAppointment.end_time) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Status</label>
                            <div class="badge" :class="getStatusBadgeClass(selectedAppointment.status)">
                                {{ selectedAppointment.status }}
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Service</label>
                            <p>{{ selectedAppointment.service.name }}</p>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Customer Info -->
                    <div>
                        <h4 class="font-medium mb-2">Customer Information</h4>
                        <div class="flex items-center gap-4">
                            <img 
                                :src="selectedAppointment.customer.avatar" 
                                :alt="selectedAppointment.customer.name"
                                class="w-10 h-10 rounded-full"
                            >
                            <div>
                                <p class="font-medium">{{ selectedAppointment.customer.name }}</p>
                                <p class="text-sm text-base-content/70">{{ selectedAppointment.customer.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Info -->
                    <div v-if="selectedAppointment.staff">
                        <h4 class="font-medium mb-2">Assigned Staff</h4>
                        <div class="flex items-center gap-4">
                            <img 
                                :src="selectedAppointment.staff.avatar" 
                                :alt="selectedAppointment.staff.name"
                                class="w-10 h-10 rounded-full"
                            >
                            <div>
                                <p class="font-medium">{{ selectedAppointment.staff.name }}</p>
                                <p class="text-sm text-base-content/70">{{ selectedAppointment.staff.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="selectedAppointment.notes">
                        <h4 class="font-medium mb-2">Notes</h4>
                        <p class="text-sm">{{ selectedAppointment.notes }}</p>
                    </div>
                </div>
                
                <div class="modal-action">
                    <button class="btn btn-ghost" @click="closeAppointmentModal">Close</button>
                    <button 
                        v-if="selectedAppointment?.status === 'pending'"
                        class="btn btn-success"
                        @click="confirmAppointment"
                    >
                        Confirm
                    </button>
                    <button 
                        v-if="['pending', 'confirmed'].includes(selectedAppointment?.status)"
                        class="btn btn-error"
                        @click="cancelAppointment"
                    >
                        Cancel
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    </BusinessLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import BusinessCalendar from '@/Components/BusinessCalendar.vue';

const props = defineProps({
    business: {
        type: Object,
        required: true,
    },
});

// State
const selectedDate = ref(new Date());
const selectedAppointment = ref(null);
const appointments = ref([]);

// Methods
const formatDate = (date) => {
    return dayjs(date).format('dddd, MMMM D, YYYY');
};

const formatTime = (time) => {
    return dayjs(time).format('h:mm A');
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'confirmed':
            return 'badge-success';
        case 'pending':
            return 'badge-warning';
        case 'cancelled':
            return 'badge-error';
        default:
            return 'badge-ghost';
    }
};

const handleAppointmentSelect = (appointment) => {
    selectedAppointment.value = appointment;
    document.getElementById('appointment_details_modal').showModal();
};

const handleAppointmentCreate = async (appointment) => {
    await loadAppointments();
};

const handleAppointmentMove = async ({ appointment, newTime }) => {
    try {
        await axios.put(route('business.appointments.move', appointment.id), {
            start_time: newTime
        });
        await loadAppointments();
    } catch (error) {
        console.error('Failed to move appointment:', error);
    }
};

const closeAppointmentModal = () => {
    document.getElementById('appointment_details_modal').close();
    selectedAppointment.value = null;
};

const confirmAppointment = async () => {
    try {
        await axios.post(route('business.appointments.confirm', selectedAppointment.value.id));
        await loadAppointments();
        closeAppointmentModal();
    } catch (error) {
        console.error('Failed to confirm appointment:', error);
    }
};

const cancelAppointment = async () => {
    try {
        await axios.post(route('business.appointments.cancel', selectedAppointment.value.id));
        await loadAppointments();
        closeAppointmentModal();
    } catch (error) {
        console.error('Failed to cancel appointment:', error);
    }
};

const loadAppointments = async () => {
    try {
        const response = await axios.get(route('business.appointments.index'), {
            params: {
                start_date: dayjs(selectedDate.value).startOf('month').format('YYYY-MM-DD'),
                end_date: dayjs(selectedDate.value).endOf('month').format('YYYY-MM-DD'),
            }
        });
        appointments.value = response.data.appointments;
    } catch (error) {
        console.error('Failed to load appointments:', error);
    }
};

// Load initial data
onMounted(() => {
    loadAppointments();
});
</script> 