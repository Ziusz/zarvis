<template>
    <BusinessLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-base-content leading-tight">
                Calendar
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-base-100 overflow-hidden shadow-xl sm:rounded-lg">
                    <BusinessCalendar
                        :appointments="appointments"
                        :initial-date="currentDate"
                        :initial-view="currentView"
                        @update:date="handleDateChange"
                        @update:view="handleViewChange"
                        @appointment-created="handleAppointmentCreated"
                        @appointment-moved="handleAppointmentMoved"
                        @appointment-selected="handleAppointmentSelected"
                    />
                </div>
            </div>
        </div>

        <!-- Appointment Details Modal -->
        <dialog id="appointment_details_modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Appointment Details</h3>
                <div v-if="selectedAppointment">
                    <div class="space-y-4">
                        <div>
                            <label class="font-medium">Customer</label>
                            <p>{{ selectedAppointment.customer?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="font-medium">Service</label>
                            <p>{{ selectedAppointment.service?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="font-medium">Date & Time</label>
                            <p>{{ formatDateTime(selectedAppointment.start_time) }}</p>
                        </div>
                        <div>
                            <label class="font-medium">Duration</label>
                            <p>{{ formatDuration(selectedAppointment.start_time, selectedAppointment.end_time) }}</p>
                        </div>
                        <div>
                            <label class="font-medium">Status</label>
                            <p>{{ selectedAppointment.status }}</p>
                        </div>
                        <div>
                            <label class="font-medium">Notes</label>
                            <p>{{ selectedAppointment.notes || 'No notes' }}</p>
                        </div>
                    </div>
                    <div class="modal-action">
                        <button class="btn btn-error" @click="handleDeleteAppointment">Delete</button>
                        <button class="btn" @click="closeAppointmentModal">Close</button>
                    </div>
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
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import BusinessCalendar from '@/Components/BusinessCalendar.vue';
import dayjs from 'dayjs';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    initialDate: {
        type: [Date, String],
        default: () => new Date()
    },
    initialView: {
        type: String,
        default: 'week'
    }
});

// State
const currentDate = ref(new Date());
const currentView = ref('week');
const selectedAppointment = ref(null);
const appointments = ref([]);

// Methods
const handleDateChange = (date) => {
    currentDate.value = date;
    refreshAppointments();
};

const handleViewChange = (view) => {
    currentView.value = view;
    refreshAppointments();
};

const handleAppointmentCreated = (appointment) => {
    axios.post('/business/appointments', {
        ...appointment,
        start_time: dayjs(appointment.start_time).format('YYYY-MM-DD HH:mm:ss')
    })
    .then(() => {
        refreshAppointments();
    })
    .catch(error => {
        console.error('Failed to create appointment:', error);
    });
};

const handleAppointmentMoved = ({ appointment, newStart, newEnd }) => {
    axios.put(`/business/appointments/${appointment.id}/move`, {
        start_time: dayjs(newStart).format('YYYY-MM-DD HH:mm:ss')
    })
    .then(() => {
        refreshAppointments();
    })
    .catch(error => {
        console.error('Failed to move appointment:', error);
    });
};

const handleAppointmentSelected = (appointment) => {
    selectedAppointment.value = appointment;
    document.getElementById('appointment_details_modal').showModal();
};

const handleDeleteAppointment = () => {
    if (confirm('Are you sure you want to delete this appointment?')) {
        axios.delete(`/business/appointments/${selectedAppointment.value.id}`)
        .then(() => {
            closeAppointmentModal();
            refreshAppointments();
        })
        .catch(error => {
            console.error('Failed to delete appointment:', error);
        });
    }
};

const closeAppointmentModal = () => {
    document.getElementById('appointment_details_modal').close();
    selectedAppointment.value = null;
};

const refreshAppointments = () => {
    const start = dayjs(currentDate.value).startOf(currentView.value);
    const end = dayjs(currentDate.value).endOf(currentView.value);
    
    axios.get('/business/appointments', {
        params: {
            start_date: start.format('YYYY-MM-DD'),
            end_date: end.format('YYYY-MM-DD'),
            view: currentView.value
        }
    })
    .then(response => {
        appointments.value = response.data;
    })
    .catch(error => {
        console.error('Failed to load appointments:', error);
        if (error.response?.data?.errors) {
            console.error('Validation errors:', error.response.data.errors);
        }
    });
};

// Helper Methods
const formatDateTime = (datetime) => {
    return dayjs(datetime).format('MMM D, YYYY h:mm A');
};

const formatDuration = (start, end) => {
    const duration = dayjs(end).diff(dayjs(start), 'minute');
    const hours = Math.floor(duration / 60);
    const minutes = duration % 60;
    return `${hours}h ${minutes}m`;
};

onMounted(() => {
    refreshAppointments();
});
</script> 