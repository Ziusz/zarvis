<template>
    <div class="flex h-full">
        <!-- Left Sidebar -->
        <div class="w-64 bg-base-200 p-4 border-r">
            <!-- View Type Selector -->
            <div class="mb-6">
                <select v-model="viewType" class="select select-bordered w-full">
                    <option value="day">Day View</option>
                    <option value="week">Week View</option>
                    <option value="month">Month View</option>
                </select>
            </div>

            <!-- Mini Calendar -->
            <div class="calendar-mini bg-base-100 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <button class="btn btn-ghost btn-sm" @click="previousMonth">←</button>
                    <span class="font-medium">{{ currentMonthName }} {{ currentYear }}</span>
                    <button class="btn btn-ghost btn-sm" @click="nextMonth">→</button>
                </div>
                
                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1 text-center text-sm">
                    <!-- Week days -->
                    <div v-for="day in ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']" :key="day" class="p-1 text-base-content/70">
                        {{ day }}
                    </div>
                    <!-- Calendar days -->
                    <div 
                        v-for="date in calendarDays" 
                        :key="date.format('YYYY-MM-DD')"
                        class="p-1 rounded-lg cursor-pointer hover:bg-base-300 transition-colors aspect-square flex items-center justify-center"
                        :class="{
                            'bg-primary text-primary-content': isSelectedDate(date),
                            'opacity-30': !isCurrentMonth(date),
                            'bg-accent/10': isToday(date) && !isSelectedDate(date)
                        }"
                        @click="selectDate(date)"
                    >
                        {{ date.format('D') }}
                    </div>
                </div>
            </div>

            <!-- Week Navigation -->
            <div class="space-y-2">
                <button 
                    v-for="offset in [-1, 0, 1, 2, 3, 4]" 
                    :key="offset"
                    class="btn btn-ghost w-full justify-start text-left"
                    :class="{ 'btn-active': isCurrentWeek(offset) }"
                    @click="goToWeek(offset)"
                >
                    {{ getWeekLabel(offset) }}
                </button>
            </div>
        </div>

        <!-- Main Calendar Area -->
        <div class="flex-1 flex flex-col h-full">
            <!-- Top Navigation -->
            <div class="flex justify-between items-center p-4 border-b">
                <div class="flex items-center gap-4">
                    <button class="btn btn-ghost btn-sm" @click="previousPeriod">←</button>
                    <div class="text-lg font-medium">{{ currentPeriodLabel }}</div>
                    <button class="btn btn-ghost btn-sm" @click="nextPeriod">→</button>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-ghost btn-sm" @click="goToToday">Today</button>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="flex-1 overflow-y-auto">
                <!-- Day View -->
                <div v-if="viewType === 'day'" class="grid grid-cols-1 divide-y">
                    <div 
                        v-for="hour in hours" 
                        :key="hour" 
                        class="relative h-16"
                        @dragover.prevent
                        @drop="handleDrop($event, hour)"
                        @click="handleTimeSlotClick(hour)"
                    >
                        <!-- Hour Label -->
                        <div class="absolute left-0 -mt-3 w-16 text-xs text-base-content/70 text-right pr-2">
                            {{ formatHour(hour) }}
                        </div>
                        <!-- Appointment Slots -->
                        <div class="ml-16 h-full relative">
                            <div 
                                v-for="appointment in getAppointmentsForHour(hour)" 
                                :key="appointment.id"
                                class="absolute rounded-lg p-2 text-sm cursor-pointer"
                                :class="getAppointmentClasses(appointment)"
                                :style="getAppointmentStyles(appointment)"
                                @click.stop="selectAppointment(appointment)"
                                draggable="true"
                                @dragstart="handleDragStart($event, appointment)"
                            >
                                {{ appointment.title }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Week View -->
                <div v-else-if="viewType === 'week'" class="relative">
                    <!-- Time Labels -->
                    <div class="absolute left-0 top-0 w-16 pt-14 bg-base-100 z-10">
                        <div v-for="hour in hours" :key="hour" class="h-12">
                            <div class="text-xs text-base-content/70 text-right pr-2">
                                {{ formatHour(hour) }}
                            </div>
                        </div>
                    </div>

                    <!-- Days Header -->
                    <div class="grid grid-cols-7 ml-16 border-b bg-base-100 sticky top-0 z-10">
                        <div 
                            v-for="day in weekDays" 
                            :key="day.toISOString()" 
                            class="p-3 text-center border-l first:border-l-0"
                        >
                            <div class="font-medium">{{ day.format('ddd') }}</div>
                            <div class="text-sm text-base-content/70">{{ day.format('MMM D') }}</div>
                        </div>
                    </div>

                    <!-- Time Grid -->
                    <div class="ml-16 grid grid-cols-7">
                        <div 
                            v-for="day in weekDays" 
                            :key="day.toISOString()" 
                            class="border-l first:border-l-0"
                        >
                            <div 
                                v-for="hour in hours" 
                                :key="hour"
                                class="relative h-12 border-b"
                                :class="{ 'border-base-300': hour % 1 === 0 }"
                                @dragover.prevent
                                @drop="handleDrop($event, hour, day)"
                                @click="handleTimeSlotClick(hour, day)"
                            >
                                <!-- Half-hour divider -->
                                <div class="absolute w-full h-px bg-base-200 top-1/2"></div>
                                
                                <!-- Appointments -->
                                <div 
                                    v-for="appointment in getAppointmentsForHourAndDay(hour, day)" 
                                    :key="appointment.id"
                                    class="absolute z-10 rounded-lg p-1 text-xs cursor-pointer left-0 right-1 overflow-hidden"
                                    :class="getAppointmentClasses(appointment)"
                                    :style="getAppointmentStyles(appointment)"
                                    @click.stop="selectAppointment(appointment)"
                                    draggable="true"
                                    @dragstart="handleDragStart($event, appointment)"
                                >
                                    <div class="font-medium truncate">{{ appointment.title }}</div>
                                    <div class="text-xs opacity-90 truncate">{{ formatTime(appointment.start_time) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Month View -->
                <div v-else class="grid grid-cols-7 divide-x h-full">
                    <div v-for="day in monthDays" :key="day.date.toISOString()" class="min-h-[100px] p-2">
                        <div 
                            class="text-sm mb-2 cursor-pointer hover:bg-base-200 rounded p-1"
                            :class="{ 'opacity-50': !day.isCurrentMonth }"
                            @click="selectDate(day.date)"
                        >
                            {{ day.date.format('D') }}
                        </div>
                        <div class="space-y-1">
                            <div 
                                v-for="appointment in getAppointmentsForDay(day.date)" 
                                :key="appointment.id"
                                class="text-xs p-1 rounded cursor-pointer truncate"
                                :class="getAppointmentClasses(appointment)"
                                @click.stop="selectAppointment(appointment)"
                                draggable="true"
                                @dragstart="handleDragStart($event, appointment)"
                            >
                                {{ formatTime(appointment.start_time) }} {{ appointment.title }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Appointment Modal -->
    <dialog id="create_appointment_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Create Appointment</h3>
            <form @submit.prevent="createAppointment" class="space-y-4">
                <div>
                    <label class="label">
                        <span class="label-text">Service</span>
                    </label>
                    <select v-model="newAppointment.service_id" class="select select-bordered w-full" required>
                        <option v-for="service in services" :key="service.id" :value="service.id">
                            {{ service.name }} ({{ service.duration }}min)
                        </option>
                    </select>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text">Staff Member</span>
                    </label>
                    <select v-model="newAppointment.staff_id" class="select select-bordered w-full" required>
                        <option v-for="staff in availableStaff" :key="staff.id" :value="staff.id">
                            {{ staff.name }}
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text">Date</span>
                        </label>
                        <input 
                            type="date" 
                            v-model="newAppointment.date"
                            class="input input-bordered w-full"
                            required
                        >
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text">Time</span>
                        </label>
                        <input 
                            type="time" 
                            v-model="newAppointment.time"
                            class="input input-bordered w-full"
                            required
                        >
                    </div>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text">Customer</span>
                    </label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            v-model="customerSearch"
                            class="input input-bordered flex-1"
                            placeholder="Search customers..."
                            @input="searchCustomers"
                        >
                        <button 
                            type="button"
                            class="btn btn-primary"
                            @click="showCreateCustomer = true"
                        >
                            New
                        </button>
                    </div>
                    <!-- Customer Search Results -->
                    <div v-if="customerSearchResults.length" class="mt-2">
                        <div 
                            v-for="customer in customerSearchResults" 
                            :key="customer.id"
                            class="p-2 hover:bg-base-200 cursor-pointer"
                            @click="selectCustomer(customer)"
                        >
                            {{ customer.name }} ({{ customer.email }})
                        </div>
                    </div>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text">Notes</span>
                    </label>
                    <textarea 
                        v-model="newAppointment.notes"
                        class="textarea textarea-bordered w-full"
                        rows="3"
                    ></textarea>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn" @click="closeCreateModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Appointment</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import dayjs from 'dayjs';
import { debounce } from 'lodash';
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import weekOfYear from 'dayjs/plugin/weekOfYear';
import isBetween from 'dayjs/plugin/isBetween';
import weekday from 'dayjs/plugin/weekday';
import localeData from 'dayjs/plugin/localeData';
import updateLocale from 'dayjs/plugin/updateLocale';

// Extend dayjs with required plugins
dayjs.extend(isSameOrBefore);
dayjs.extend(isSameOrAfter);
dayjs.extend(weekOfYear);
dayjs.extend(isBetween);
dayjs.extend(weekday);
dayjs.extend(localeData);
dayjs.extend(updateLocale);

// Configure locale to start week on Monday
dayjs.updateLocale('en', {
    weekStart: 1,
    weekdays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
});

const props = defineProps({
    appointments: {
        type: Array,
        default: () => []
    },
    initialDate: {
        type: [Date, String],
        default: () => new Date()
    },
    initialView: {
        type: String,
        default: 'week'
    }
});

const emit = defineEmits(['update:date', 'appointment-created', 'appointment-moved', 'appointment-selected']);

// State
const currentDate = ref(dayjs(props.initialDate));
const viewType = ref(props.initialView);
const draggedAppointment = ref(null);
const newAppointment = ref({
    service_id: '',
    staff_id: '',
    date: '',
    time: '',
    customer_id: '',
    notes: ''
});
const customerSearch = ref('');
const customerSearchResults = ref([]);
const showCreateCustomer = ref(false);

// Hours for day/week view
const hours = Array.from({ length: 24 }, (_, i) => i);

// Computed Properties
const currentMonthName = computed(() => currentDate.value.format('MMMM'));
const currentYear = computed(() => currentDate.value.format('YYYY'));

const calendarDays = computed(() => {
    const days = [];
    const firstDay = dayjs(currentDate.value).startOf('month');
    const lastDay = dayjs(currentDate.value).endOf('month');
    const startDay = firstDay.startOf('week');
    const endDay = lastDay.endOf('week');

    let current = startDay;
    while (current.isBefore(endDay) || current.isSame(endDay, 'day')) {
        days.push(current);
        current = current.add(1, 'day');
    }

    return days;
});

const weekDays = computed(() => {
    const days = [];
    const weekStart = dayjs(currentDate.value).startOf('week');
    
    for (let i = 0; i < 7; i++) {
        const day = weekStart.add(i, 'day');
        days.push(day);
    }
    return days;
});

const monthDays = computed(() => {
    const days = [];
    const monthStart = currentDate.value.startOf('month');
    const monthEnd = currentDate.value.endOf('month');
    
    const calStart = monthStart.startOf('week');
    const calEnd = monthEnd.endOf('week');

    let current = calStart;
    while (current.isBefore(calEnd) || current.isSame(calEnd, 'day')) {
        days.push({
            date: current,
            isCurrentMonth: current.month() === monthStart.month()
        });
        current = current.add(1, 'day');
    }
    return days;
});

const currentPeriodLabel = computed(() => {
    switch (viewType.value) {
        case 'day':
            return currentDate.value.format('dddd, MMMM D, YYYY');
        case 'week':
            const weekStart = dayjs(currentDate.value).startOf('week');
            const weekEnd = weekStart.add(6, 'days');
            return `${weekStart.format('MMM D')} - ${weekEnd.format('MMM D, YYYY')}`;
        case 'month':
            return `${currentMonthName.value} ${currentYear.value}`;
    }
});

// Navigation Methods
const previousPeriod = () => {
    switch (viewType.value) {
        case 'day':
            currentDate.value = currentDate.value.subtract(1, 'day');
            break;
        case 'week':
            currentDate.value = currentDate.value.subtract(1, 'week');
            break;
        case 'month':
            currentDate.value = currentDate.value.subtract(1, 'month');
            break;
    }
    emit('update:date', currentDate.value.toDate());
};

const nextPeriod = () => {
    switch (viewType.value) {
        case 'day':
            currentDate.value = currentDate.value.add(1, 'day');
            break;
        case 'week':
            currentDate.value = currentDate.value.add(1, 'week');
            break;
        case 'month':
            currentDate.value = currentDate.value.add(1, 'month');
            break;
    }
    emit('update:date', currentDate.value.toDate());
};

const goToToday = () => {
    currentDate.value = dayjs();
    emit('update:date', currentDate.value.toDate());
};

const previousMonth = () => {
    currentDate.value = dayjs(currentDate.value).subtract(1, 'month');
    emit('update:date', currentDate.value.toDate());
};

const nextMonth = () => {
    currentDate.value = dayjs(currentDate.value).add(1, 'month');
    emit('update:date', currentDate.value.toDate());
};

const selectDate = (date) => {
    currentDate.value = dayjs(date);
    emit('update:date', currentDate.value.toDate());
};

// Appointment Methods
const getAppointmentsForHour = (hour, day = currentDate.value) => {
    return props.appointments.filter(appointment => {
        const appointmentTime = dayjs(appointment.start_time);
        return appointmentTime.hour() === hour && 
               appointmentTime.isSame(day, 'day');
    });
};

const getAppointmentsForHourAndDay = (hour, day) => {
    return props.appointments.filter(appointment => {
        const appointmentTime = dayjs(appointment.start_time);
        return appointmentTime.hour() === hour && 
               appointmentTime.isSame(day, 'day');
    });
};

const getAppointmentsForDay = (date) => {
    return props.appointments.filter(appointment => {
        const appointmentTime = dayjs(appointment.start_time);
        return appointmentTime.isSame(date, 'day');
    });
};

const getAppointmentClasses = (appointment) => {
    return {
        'bg-primary text-primary-content': appointment.type === 'client',
        'bg-secondary text-secondary-content': appointment.type === 'custom',
        'bg-accent text-accent-content': appointment.type === 'blocked'
    };
};

const getAppointmentStyles = (appointment) => {
    const start = dayjs(appointment.start_time);
    const end = dayjs(appointment.end_time);
    const duration = end.diff(start, 'minute');
    const startMinutes = start.hour() * 60 + start.minute();
    
    return {
        height: `${(duration / 60) * 48}px`,
        top: `${(startMinutes / 60) * 48}px`,
        width: '95%'
    };
};

// Drag & Drop Methods
const handleDragStart = (event, appointment) => {
    draggedAppointment.value = appointment;
    event.dataTransfer.effectAllowed = 'move';
};

const handleDrop = (event, hour, day = currentDate.value) => {
    if (!draggedAppointment.value) return;

    const appointment = draggedAppointment.value;
    const oldStart = dayjs(appointment.start_time);
    const oldEnd = dayjs(appointment.end_time);
    const duration = oldEnd.diff(oldStart, 'minute');

    const newStart = day.hour(hour).minute(0);
    const newEnd = newStart.add(duration, 'minute');

    emit('appointment-moved', {
        appointment,
        newStart: newStart.toDate(),
        newEnd: newEnd.toDate()
    });

    draggedAppointment.value = null;
};

const selectAppointment = (appointment) => {
    emit('appointment-selected', appointment);
};

// Helper Methods
const formatHour = (hour) => {
    if (hour === 0) {
        return '12 AM';
    } else if (hour === 12) {
        return '12 PM';
    } else if (hour > 12) {
        return `${hour - 12} PM`;
    } else {
        return `${hour} AM`;
    }
};

const formatTime = (time) => {
    return dayjs(time).format('h:mm A');
};

const isSelectedDate = (date) => {
    return dayjs(date).isSame(dayjs(currentDate.value), 'day');
};

const getWeekLabel = (offset) => {
    const date = dayjs().add(offset, 'week');
    const start = date.startOf('week');
    const end = start.add(6, 'days');
    return offset === 0 
        ? 'This Week' 
        : `${start.format('MMM D')} - ${end.format('MMM D')}`;
};

const isCurrentWeek = (offset) => {
    const targetWeek = dayjs().add(offset, 'week');
    return currentDate.value.isSame(targetWeek, 'isoWeek');
};

const goToWeek = (offset) => {
    const targetDate = dayjs().add(offset, 'week');
    currentDate.value = targetDate;
    viewType.value = 'week';
    emit('update:date', currentDate.value.toDate());
};

// Watch for view type changes
watch(viewType, (newValue) => {
    // Emit view type change if needed
    emit('update:view', newValue);
});

// Initialize
onMounted(() => {
    // Any initialization if needed
});

const availableStaff = computed(() => {
    if (!newAppointment.value.service_id) return props.staff;
    const service = props.services.find(s => s.id === newAppointment.value.service_id);
    if (!service) return props.staff;
    return props.staff.filter(staff => 
        staff.services.some(s => s.id === service.id)
    );
});

const searchCustomers = debounce(async () => {
    if (customerSearch.value.length < 2) {
        customerSearchResults.value = [];
        return;
    }
    
    try {
        const response = await axios.get('/api/customers/search', {
            params: { query: customerSearch.value }
        });
        customerSearchResults.value = response.data;
    } catch (error) {
        console.error('Failed to search customers:', error);
    }
}, 300);

const selectCustomer = (customer) => {
    newAppointment.value.customer_id = customer.id;
    customerSearch.value = customer.name;
    customerSearchResults.value = [];
};

const createAppointment = async () => {
    try {
        const response = await axios.post('/api/appointments', {
            ...newAppointment.value,
            start_time: dayjs(newAppointment.value.date + ' ' + newAppointment.value.time)
                .format('YYYY-MM-DD HH:mm:ss')
        });
        
        emit('create-appointment', response.data);
        closeCreateModal();
    } catch (error) {
        console.error('Failed to create appointment:', error);
    }
};

const closeCreateModal = () => {
    document.getElementById('create_appointment_modal').close();
    newAppointment.value = {
        service_id: '',
        staff_id: '',
        date: '',
        time: '',
        customer_id: '',
        notes: ''
    };
    customerSearch.value = '';
    customerSearchResults.value = [];
};

const isCurrentMonth = (date) => {
    return dayjs(date).month() === dayjs(currentDate.value).month();
};

const isToday = (date) => {
    return dayjs(date).isSame(dayjs(), 'day');
};

const handleTimeSlotClick = (hour, day = currentDate.value) => {
    const time = day.hour(hour);
    openCreateModal(time);
};

// ... rest of your existing methods ...
</script>

<style scoped>
.calendar-mini {
    font-size: 0.875rem;
}

.calendar-mini .grid {
    gap: 2px;
}

.calendar-mini .grid > div {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Time Grid Styles */
.time-grid {
    display: grid;
    grid-template-columns: 4rem repeat(7, 1fr);
}

.time-slot {
    position: relative;
    height: 48px;
    border-bottom: 1px solid var(--base-200);
}

.time-slot:hover {
    background-color: rgba(var(--b2) / 0.1);
}

.time-label {
    position: sticky;
    left: 0;
    background-color: var(--base-100);
    z-index: 10;
    padding-right: 0.5rem;
    text-align: right;
    font-size: 0.75rem;
    color: var(--base-content-70);
}

/* Appointment Styles */
.appointment {
    position: absolute;
    left: 1px;
    right: 1px;
    z-index: 10;
    border-radius: 0.375rem;
    padding: 0.25rem;
    font-size: 0.75rem;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
}

.appointment:hover {
    transform: scale(1.02);
    z-index: 20;
}
</style> 