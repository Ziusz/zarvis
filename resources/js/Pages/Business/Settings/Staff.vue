<template>
    <SettingsLayout :business="business">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium">Manage Staff</h3>
                <button 
                    class="btn btn-primary"
                    @click="openAddStaffModal"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Staff Member
                </button>
            </div>

            <!-- Staff List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Empty State -->
                <div v-if="!staff || staff.length === 0" class="lg:col-span-3">
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium">No staff members yet</h3>
                        <p class="mt-2 text-base-content/70">Get started by adding your first staff member.</p>
                    </div>
                </div>

                <!-- Staff Cards -->
                <div v-else v-for="member in staff" :key="member.id" class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <img 
                                    :src="member.avatar" 
                                    :alt="member.name"
                                    class="w-12 h-12 rounded-full"
                                >
                                <div>
                                    <h3 class="font-medium">{{ member.name }}</h3>
                                    <p class="text-sm text-base-content/70">{{ member.role }}</p>
                                </div>
                            </div>
                            <span :class="[
                                'badge',
                                member.status === 'active' ? 'badge-success' : 'badge-error'
                            ]">
                                {{ member.status }}
                            </span>
                        </div>

                        <div class="divider my-2"></div>

                        <!-- Contact Info -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ member.email }}
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ member.phone }}
                            </div>
                        </div>

                        <!-- Services -->
                        <div v-if="member.services?.length > 0" class="mt-4">
                            <h4 class="text-sm font-medium mb-2">Services</h4>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="service in member.services" 
                                    :key="service.id"
                                    class="badge badge-primary badge-sm"
                                >
                                    {{ service.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Specialties -->
                        <div v-if="member.specialties?.length > 0" class="mt-4">
                            <h4 class="text-sm font-medium mb-2">Specialties</h4>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="specialty in member.specialties" 
                                    :key="specialty"
                                    class="badge badge-secondary badge-sm"
                                >
                                    {{ specialty }}
                                </span>
                            </div>
                        </div>

                        <!-- Languages -->
                        <div v-if="member.languages?.length > 0" class="mt-4">
                            <h4 class="text-sm font-medium mb-2">Languages</h4>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="language in member.languages" 
                                    :key="language"
                                    class="badge badge-ghost badge-sm"
                                >
                                    {{ language }}
                                </span>
                            </div>
                        </div>

                        <div class="card-actions justify-end mt-4">
                            <button 
                                class="btn btn-ghost btn-sm"
                                @click="openAvailabilityModal(member)"
                            >
                                Availability
                            </button>
                            <button 
                                class="btn btn-ghost btn-sm"
                                @click="openEditStaffModal(member)"
                            >
                                Edit
                            </button>
                            <button 
                                class="btn btn-error btn-sm"
                                @click="confirmDelete(member)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Staff Modal -->
        <Modal
            :show="showStaffModal"
            @close="closeStaffModal"
            size="lg"
        >
            <template #title>
                {{ isEditing ? 'Edit Staff Member' : 'Add Staff Member' }}
            </template>

            <template #content>
                <form @submit.prevent="submitStaffForm" class="space-y-6">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input 
                                type="text" 
                                v-model="form.name"
                                class="input input-bordered"
                                :class="{ 'input-error': errors.name }"
                            >
                            <label v-if="errors.name" class="label">
                                <span class="label-text-alt text-error">{{ errors.name }}</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input 
                                type="email" 
                                v-model="form.email"
                                class="input input-bordered"
                                :class="{ 'input-error': errors.email }"
                            >
                            <label v-if="errors.email" class="label">
                                <span class="label-text-alt text-error">{{ errors.email }}</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Phone</span>
                            </label>
                            <input 
                                type="tel" 
                                v-model="form.phone"
                                class="input input-bordered"
                                :class="{ 'input-error': errors.phone }"
                            >
                            <label v-if="errors.phone" class="label">
                                <span class="label-text-alt text-error">{{ errors.phone }}</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Role</span>
                            </label>
                            <select 
                                v-model="form.role"
                                class="select select-bordered"
                                :class="{ 'select-error': errors.role }"
                            >
                                <option value="staff">Staff</option>
                                <option value="manager">Manager</option>
                            </select>
                            <label v-if="errors.role" class="label">
                                <span class="label-text-alt text-error">{{ errors.role }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Services -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Services</span>
                            <button 
                                type="button"
                                class="btn btn-xs btn-ghost"
                                @click="toggleAllServices"
                                v-if="services.length > 0"
                            >
                                {{ allServicesSelected ? 'Deselect All' : 'Select All' }}
                            </button>
                        </label>
                        <div v-if="services.length > 0" class="grid grid-cols-2 gap-2 p-4 border rounded-lg">
                            <label 
                                v-for="service in services" 
                                :key="service.id"
                                class="flex items-center gap-2 cursor-pointer hover:bg-base-200 p-2 rounded-lg"
                            >
                                <input 
                                    type="checkbox"
                                    :value="service.id"
                                    v-model="form.services"
                                    class="checkbox checkbox-primary checkbox-sm"
                                >
                                <span>{{ service.name }}</span>
                            </label>
                        </div>
                        <div v-else class="alert alert-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>No services available. Please add services first.</span>
                        </div>
                        <label v-if="errors.services" class="label">
                            <span class="label-text-alt text-error">{{ errors.services }}</span>
                        </label>
                    </div>

                    <!-- Specialties -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Specialties</span>
                        </label>
                        <input 
                            type="text" 
                            v-model="specialtyInput"
                            @keydown.enter.prevent="addSpecialty"
                            placeholder="Type and press Enter to add"
                            class="input input-bordered"
                        >
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span 
                                v-for="specialty in form.specialties" 
                                :key="specialty"
                                class="badge badge-secondary gap-2"
                            >
                                {{ specialty }}
                                <button 
                                    type="button"
                                    @click="removeSpecialty(specialty)"
                                    class="btn btn-ghost btn-xs btn-circle"
                                >
                                    ×
                                </button>
                            </span>
                        </div>
                    </div>

                    <!-- Languages -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Languages</span>
                        </label>
                        <input 
                            type="text" 
                            v-model="languageInput"
                            @keydown.enter.prevent="addLanguage"
                            placeholder="Type and press Enter to add"
                            class="input input-bordered"
                        >
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span 
                                v-for="language in form.languages" 
                                :key="language"
                                class="badge badge-ghost gap-2"
                            >
                                {{ language }}
                                <button 
                                    type="button"
                                    @click="removeLanguage(language)"
                                    class="btn btn-ghost btn-xs btn-circle"
                                >
                                    ×
                                </button>
                            </span>
                        </div>
                    </div>

                    <!-- Experience -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Experience</span>
                        </label>
                        <textarea 
                            v-model="form.experience"
                            class="textarea textarea-bordered"
                            :class="{ 'textarea-error': errors.experience }"
                            rows="3"
                        ></textarea>
                        <label v-if="errors.experience" class="label">
                            <span class="label-text-alt text-error">{{ errors.experience }}</span>
                        </label>
                    </div>

                    <!-- Status (only for editing) -->
                    <div v-if="isEditing" class="form-control">
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select 
                            v-model="form.status"
                            class="select select-bordered"
                            :class="{ 'select-error': errors.status }"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <label v-if="errors.status" class="label">
                            <span class="label-text-alt text-error">{{ errors.status }}</span>
                        </label>
                    </div>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <button 
                        class="btn btn-ghost"
                        @click="closeStaffModal"
                    >
                        Cancel
                    </button>
                    <button 
                        class="btn btn-primary"
                        @click="submitStaffForm"
                        :disabled="processing"
                    >
                        {{ isEditing ? 'Update' : 'Add' }} Staff Member
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="showDeleteModal"
            @close="closeDeleteModal"
        >
            <template #title>
                Remove Staff Member
            </template>

            <template #content>
                <p>Are you sure you want to remove {{ selectedStaff?.name }}? This action cannot be undone.</p>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <button 
                        class="btn btn-ghost"
                        @click="closeDeleteModal"
                    >
                        Cancel
                    </button>
                    <button 
                        class="btn btn-error"
                        @click="deleteStaff"
                        :disabled="processing"
                    >
                        Remove Staff Member
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Add Availability Modal -->
        <Modal
            :show="showAvailabilityModal"
            @close="closeAvailabilityModal"
            size="xl"
        >
            <template #title>
                {{ selectedStaff ? `${selectedStaff.name}'s Availability` : 'Staff Availability' }}
            </template>

            <template #content>
                <div class="space-y-6">
                    <!-- Week Navigation -->
                    <div class="flex justify-between items-center bg-base-200 p-4 rounded-lg">
                        <button 
                            class="btn btn-ghost btn-sm"
                            @click="previousWeek"
                        >
                            ← Previous Week
                        </button>
                        <h3 class="text-lg font-medium">
                            {{ formatDateRange(currentWeek.start, currentWeek.end) }}
                        </h3>
                        <button 
                            class="btn btn-ghost btn-sm"
                            @click="nextWeek"
                        >
                            Next Week →
                        </button>
                    </div>

                    <!-- Weekly Calendar -->
                    <div class="space-y-4">
                        <div v-for="date in weekDays" :key="date.format('YYYY-MM-DD')" 
                            class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow"
                        >
                            <div class="card-body">
                                <h3 class="card-title text-lg">{{ date.format('dddd, MMMM D') }}</h3>
                                
                                <!-- Time Slots -->
                                <div class="space-y-2 mt-4">
                                    <div v-for="(slot, index) in getDaySlots(date)" :key="index" 
                                        class="flex items-center gap-4 p-3 border rounded-lg bg-base-100 hover:bg-base-200 transition-colors"
                                    >
                                        <div class="flex-1 flex items-center gap-4">
                                            <div class="flex items-center gap-2">
                                                <input 
                                                    type="time" 
                                                    v-model="slot.start_time"
                                                    class="input input-bordered input-sm w-32"
                                                >
                                                <span class="text-base-content/70">to</span>
                                                <input 
                                                    type="time" 
                                                    v-model="slot.end_time"
                                                    class="input input-bordered input-sm w-32"
                                                >
                                            </div>
                                            <select 
                                                v-model="slot.status"
                                                class="select select-bordered select-sm"
                                            >
                                                <option value="available">Available</option>
                                                <option value="unavailable">Unavailable</option>
                                                <option value="on-leave">On Leave</option>
                                            </select>
                                        </div>
                                        <button 
                                            @click="removeTimeSlot(date, index)"
                                            class="btn btn-ghost btn-sm btn-circle"
                                            :disabled="getDaySlots(date).length === 1"
                                        >
                                            ×
                                        </button>
                                    </div>
                                    
                                    <button 
                                        @click="addTimeSlot(date)"
                                        class="btn btn-ghost btn-sm w-full"
                                    >
                                        + Add Time Slot
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-between">
                    <button 
                        class="btn btn-primary"
                        @click="syncWithBusinessHours"
                        :disabled="processing"
                    >
                        Sync with Business Hours
                    </button>
                    <div class="flex gap-2">
                        <button 
                            class="btn btn-ghost"
                            @click="closeAvailabilityModal"
                        >
                            Cancel
                        </button>
                        <button 
                            class="btn btn-primary"
                            @click="saveAvailability"
                            :disabled="processing"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </SettingsLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import Modal from '@/Components/Modal.vue';
import dayjs from 'dayjs';

const props = defineProps({
    business: {
        type: Object,
        required: true,
    },
    staff: {
        type: Array,
        default: () => [],
    },
});

// Services helpers
const services = computed(() => {
    return props.business?.services || [];
});

const allServicesSelected = computed(() => {
    return services.value.length > 0 && form.services.length === services.value.length;
});

const toggleAllServices = () => {
    if (allServicesSelected.value) {
        form.services = [];
    } else {
        form.services = services.value.map(s => s.id);
    }
};

// Form state
const form = reactive({
    name: '',
    email: '',
    phone: '',
    role: 'staff',
    specialties: [],
    experience: '',
    languages: [],
    services: [],
    status: 'active',
});

// Watch for changes in services and update form.services if empty
watch(() => services.value, (newServices) => {
    if (newServices.length > 0) {
        form.services = newServices.map(s => s.id);
    }
}, { immediate: true });

const errors = ref({});
const processing = ref(false);
const isEditing = ref(false);
const selectedStaff = ref(null);

// Modal state
const showStaffModal = ref(false);
const showDeleteModal = ref(false);
const showAvailabilityModal = ref(false);
const currentWeek = reactive({
    start: dayjs().startOf('week'),
    end: dayjs().endOf('week')
});
const availabilitySlots = ref({});

// Input state for tags
const specialtyInput = ref('');
const languageInput = ref('');

// Modal handlers
const openAddStaffModal = () => {
    isEditing.value = false;
    resetForm();
    showStaffModal.value = true;
};

const openEditStaffModal = (member) => {
    isEditing.value = true;
    selectedStaff.value = member;
    form.name = member.name;
    form.email = member.email;
    form.phone = member.phone;
    form.role = member.role;
    form.specialties = [...(member.specialties || [])];
    form.experience = member.experience;
    form.languages = [...(member.languages || [])];
    form.services = member.services.map(s => s.id);
    form.status = member.status;
    showStaffModal.value = true;
};

const closeStaffModal = () => {
    showStaffModal.value = false;
    resetForm();
};

const confirmDelete = (member) => {
    selectedStaff.value = member;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedStaff.value = null;
};

// Form handlers
const resetForm = () => {
    form.name = '';
    form.email = '';
    form.phone = '';
    form.role = 'staff';
    form.specialties = [];
    form.experience = '';
    form.languages = [];
    form.services = services.value.map(s => s.id);
    form.status = 'active';
    errors.value = {};
    selectedStaff.value = null;
};

const addSpecialty = () => {
    if (specialtyInput.value.trim() && !form.specialties.includes(specialtyInput.value.trim())) {
        form.specialties.push(specialtyInput.value.trim());
        specialtyInput.value = '';
    }
};

const removeSpecialty = (specialty) => {
    form.specialties = form.specialties.filter(s => s !== specialty);
};

const addLanguage = () => {
    if (languageInput.value.trim() && !form.languages.includes(languageInput.value.trim())) {
        form.languages.push(languageInput.value.trim());
        languageInput.value = '';
    }
};

const removeLanguage = (language) => {
    form.languages = form.languages.filter(l => l !== language);
};

const submitStaffForm = () => {
    if (processing.value) return;
    
    processing.value = true;
    errors.value = {};
    
    const url = isEditing.value 
        ? route('business.staff.update', selectedStaff.value.id)
        : route('business.staff.store');
    
    const method = isEditing.value ? 'put' : 'post';
    
    router[method](url, form, {
        preserveScroll: true,
        onSuccess: () => {
            closeStaffModal();
            processing.value = false;
        },
        onError: (e) => {
            errors.value = e;
            processing.value = false;
        },
    });
};

const deleteStaff = () => {
    processing.value = true;
    
    router.delete(`/business/staff/${selectedStaff.value.id}`, {
        onSuccess: () => {
            closeDeleteModal();
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

// Availability methods
const openAvailabilityModal = (member) => {
    selectedStaff.value = member;
    showAvailabilityModal.value = true;
    loadAvailability();
};

const closeAvailabilityModal = () => {
    showAvailabilityModal.value = false;
    selectedStaff.value = null;
    availabilitySlots.value = {};
};

const previousWeek = () => {
    currentWeek.start = currentWeek.start.subtract(1, 'week');
    currentWeek.end = currentWeek.end.subtract(1, 'week');
    loadAvailability();
};

const nextWeek = () => {
    currentWeek.start = currentWeek.start.add(1, 'week');
    currentWeek.end = currentWeek.end.add(1, 'week');
    loadAvailability();
};

const formatDateRange = (start, end) => {
    if (start.format('MMM YYYY') === end.format('MMM YYYY')) {
        return `${start.format('MMM D')} - ${end.format('D, YYYY')}`;
    }
    return `${start.format('MMM D')} - ${end.format('MMM D, YYYY')}`;
};

const weekDays = computed(() => {
    const days = [];
    let current = currentWeek.start;
    while (current.isBefore(currentWeek.end) || current.isSame(currentWeek.end, 'day')) {
        days.push(current);
        current = current.add(1, 'day');
    }
    return days;
});

// Get business hours for a specific day
const getBusinessHours = (date) => {
    const dayOfWeek = date.format('dddd').toLowerCase();
    const hours = props.business.opening_hours?.[dayOfWeek];
    
    if (!hours) return null;

    // Handle both old and new format
    if (hours.is_open !== undefined) {
        return hours.is_open ? {
            start: hours.start,
            end: hours.end
        } : null;
    }

    return hours.closed ? null : {
        start: hours.open,
        end: hours.close
    };
};

const getDaySlots = (date) => {
    const dateKey = date.format('YYYY-MM-DD');
    if (!availabilitySlots.value[dateKey]) {
        // Get business hours for this day
        const businessHours = getBusinessHours(date);
        if (businessHours) {
            availabilitySlots.value[dateKey] = [{
                start_time: businessHours.start,
                end_time: businessHours.end,
                status: 'available'
            }];
        } else {
            // If business is closed, set as unavailable
            availabilitySlots.value[dateKey] = [{
                start_time: '09:00',
                end_time: '17:00',
                status: 'unavailable'
            }];
        }
    }
    return availabilitySlots.value[dateKey];
};

const addTimeSlot = (date) => {
    const dateKey = date.format('YYYY-MM-DD');
    const businessHours = getBusinessHours(date);
    if (!availabilitySlots.value[dateKey]) {
        availabilitySlots.value[dateKey] = [];
    }
    availabilitySlots.value[dateKey].push({
        start_time: businessHours?.start || '09:00',
        end_time: businessHours?.end || '17:00',
        status: businessHours ? 'available' : 'unavailable'
    });
};

const removeTimeSlot = (date, index) => {
    const dateKey = date.format('YYYY-MM-DD');
    if (availabilitySlots.value[dateKey] && availabilitySlots.value[dateKey].length > 1) {
        availabilitySlots.value[dateKey].splice(index, 1);
    }
};

const loadAvailability = async () => {
    if (!selectedStaff.value) return;
    
    try {
        const response = await axios.get(route('business.staff.availability.show', selectedStaff.value.id), {
            params: {
                start_date: currentWeek.start.format('YYYY-MM-DD'),
                end_date: currentWeek.end.format('YYYY-MM-DD')
            }
        });
        
        availabilitySlots.value = response.data.availabilities;
    } catch (error) {
        console.error('Failed to load availability:', error);
    }
};

const syncWithBusinessHours = async () => {
    if (!selectedStaff.value) return;
    
    processing.value = true;
    try {
        await axios.post(route('business.staff.availability.sync', selectedStaff.value.id), {
            start_date: currentWeek.start.format('YYYY-MM-DD'),
            end_date: currentWeek.end.format('YYYY-MM-DD')
        });
        
        await loadAvailability();
    } catch (error) {
        console.error('Failed to sync with business hours:', error);
    } finally {
        processing.value = false;
    }
};

const saveAvailability = async () => {
    if (!selectedStaff.value) return;
    
    processing.value = true;
    try {
        const updates = Object.entries(availabilitySlots.value).map(([date, slots]) => ({
            date,
            availabilities: slots
        }));
        
        await axios.put(route('business.staff.availability.update', selectedStaff.value.id), {
            updates
        });
        
        closeAvailabilityModal();
    } catch (error) {
        console.error('Failed to save availability:', error);
    } finally {
        processing.value = false;
    }
};
</script> 