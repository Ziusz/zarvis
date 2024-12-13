<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    business: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const activeTab = ref('profile');

// Default working hours
const defaultWorkingHours = {
    monday: { is_open: true, start: '09:00', end: '17:00' },
    tuesday: { is_open: true, start: '09:00', end: '17:00' },
    wednesday: { is_open: true, start: '09:00', end: '17:00' },
    thursday: { is_open: true, start: '09:00', end: '17:00' },
    friday: { is_open: true, start: '09:00', end: '17:00' },
    saturday: { is_open: false, start: '09:00', end: '17:00' },
    sunday: { is_open: false, start: '09:00', end: '17:00' },
};

// Initialize form with default values
const form = ref({
    name: props.business.name || '',
    description: props.business.description || '',
    street_address: props.business.street_address || '',
    city: props.business.city || '',
    postal_code: props.business.postal_code || '',
    nip: props.business.nip || '',
    phone: props.business.phone || '',
    email: props.business.email || '',
    website: props.business.website || '',
    logo: null,
    cover_image: null,
    working_hours: {},
});

// Initialize working hours in mounted hook
onMounted(() => {
    console.log('Opening hours from DB:', props.business.opening_hours);
    
    // Parse opening_hours if it's a JSON string
    let openingHours = props.business.opening_hours;
    if (typeof openingHours === 'string') {
        try {
            openingHours = JSON.parse(openingHours);
        } catch (e) {
            console.error('Error parsing opening hours:', e);
            openingHours = null;
        }
    }

    // Initialize working hours with defaults for each day if not set
    const initialHours = {};
    days.forEach(day => {
        initialHours[day.key] = {
            is_open: openingHours?.[day.key]?.is_open ?? defaultWorkingHours[day.key].is_open,
            start: openingHours?.[day.key]?.start ?? defaultWorkingHours[day.key].start,
            end: openingHours?.[day.key]?.end ?? defaultWorkingHours[day.key].end,
        };
    });

    form.value.working_hours = initialHours;
    console.log('Initialized working hours:', form.value.working_hours);
});

const days = [
    { key: 'monday', label: 'Monday' },
    { key: 'tuesday', label: 'Tuesday' },
    { key: 'wednesday', label: 'Wednesday' },
    { key: 'thursday', label: 'Thursday' },
    { key: 'friday', label: 'Friday' },
    { key: 'saturday', label: 'Saturday' },
    { key: 'sunday', label: 'Sunday' },
];

const tabs = [
    { key: 'profile', label: 'Business Profile', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { key: 'hours', label: 'Working Hours', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'services', label: 'Services', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
    { key: 'staff', label: 'Staff Management', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
];

const processing = ref(false);

const updateProfile = () => {
    processing.value = true;

    // Ensure working_hours is properly formatted
    const formData = {
        ...form.value,
        working_hours: Object.keys(form.value.working_hours).reduce((acc, day) => {
            acc[day] = {
                is_open: form.value.working_hours[day].is_open,
                start: form.value.working_hours[day].start,
                end: form.value.working_hours[day].end,
            };
            return acc;
        }, {}),
    };

    router.post(route('business.settings.update'), {
        _method: 'PUT',
        ...formData,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

const handleLogoUpload = (e) => {
    const file = e.target.files[0];
    form.value.logo = file;
};

const handleCoverImageUpload = (e) => {
    const file = e.target.files[0];
    form.value.cover_image = file;
};
</script>

<template>
    <AppLayout>
        <Head :title="`${business.name} - Settings`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-base-100 shadow-xl rounded-box">
                    <!-- Tabs -->
                    <div class="tabs tabs-boxed bg-base-200 p-2 rounded-t-box">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            class="tab tab-lg gap-2"
                            :class="{ 'tab-active': activeTab === tab.key }"
                            @click="activeTab = tab.key"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :d="tab.icon"
                                />
                            </svg>
                            {{ tab.label }}
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Business Profile -->
                        <div v-if="activeTab === 'profile'" class="space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-6">
                                    <div>
                                        <InputLabel for="name" value="Business Name" />
                                        <TextInput
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="errors.name" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="description" value="Description" />
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            class="textarea textarea-bordered w-full h-24 mt-1"
                                            placeholder="Describe your business..."
                                        />
                                        <InputError :message="errors.description" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="street_address" value="Street Address" />
                                        <TextInput
                                            id="street_address"
                                            v-model="form.street_address"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="errors.street_address" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="city" value="City" />
                                        <TextInput
                                            id="city"
                                            v-model="form.city"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="errors.city" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="postal_code" value="Postal Code" />
                                        <TextInput
                                            id="postal_code"
                                            v-model="form.postal_code"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="errors.postal_code" class="mt-2" />
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-6">
                                    <div>
                                        <InputLabel for="nip" value="NIP (Tax ID)" />
                                        <TextInput
                                            id="nip"
                                            v-model="form.nip"
                                            type="text"
                                            maxlength="10"
                                            class="mt-1 block w-full"
                                            placeholder="0000000000"
                                        />
                                        <InputError :message="errors.nip" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="phone" value="Phone" />
                                        <TextInput
                                            id="phone"
                                            v-model="form.phone"
                                            type="tel"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="errors.phone" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="email" value="Email" />
                                        <TextInput
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="errors.email" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="website" value="Website" />
                                        <TextInput
                                            id="website"
                                            v-model="form.website"
                                            type="url"
                                            class="mt-1 block w-full"
                                            placeholder="https://"
                                        />
                                        <InputError :message="errors.website" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel value="Business Logo" />
                                        <div class="mt-2">
                                            <label class="block">
                                                <span class="sr-only">Choose logo</span>
                                                <input
                                                    type="file"
                                                    class="block w-full text-sm text-base-content
                                                        file:mr-4 file:py-2 file:px-4
                                                        file:rounded-full file:border-0
                                                        file:text-sm file:font-semibold
                                                        file:bg-primary file:text-white
                                                        hover:file:bg-primary/90"
                                                    accept="image/*"
                                                    @change="handleLogoUpload"
                                                />
                                            </label>
                                        </div>
                                        <InputError :message="errors.logo" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel value="Cover Image" />
                                        <div class="mt-2">
                                            <label class="block">
                                                <span class="sr-only">Choose cover image</span>
                                                <input
                                                    type="file"
                                                    class="block w-full text-sm text-base-content
                                                        file:mr-4 file:py-2 file:px-4
                                                        file:rounded-full file:border-0
                                                        file:text-sm file:font-semibold
                                                        file:bg-primary file:text-white
                                                        hover:file:bg-primary/90"
                                                    accept="image/*"
                                                    @change="handleCoverImageUpload"
                                                />
                                            </label>
                                        </div>
                                        <InputError :message="errors.cover_image" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Working Hours -->
                        <div v-if="activeTab === 'hours'" class="space-y-6">
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Open</th>
                                            <th>Opening Time</th>
                                            <th>Closing Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="day in days" :key="day.key">
                                            <td class="font-medium">{{ day.label }}</td>
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    class="toggle toggle-primary"
                                                    v-model="form.working_hours[day.key].is_open"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="time"
                                                    class="input input-bordered w-full max-w-xs"
                                                    v-model="form.working_hours[day.key].start"
                                                    :disabled="!form.working_hours[day.key].is_open"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="time"
                                                    class="input input-bordered w-full max-w-xs"
                                                    v-model="form.working_hours[day.key].end"
                                                    :disabled="!form.working_hours[day.key].is_open"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Services -->
                        <div v-if="activeTab === 'services'" class="space-y-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium">Manage Services</h3>
                                <button class="btn btn-primary btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add New Service
                                </button>
                            </div>

                            <!-- Service list will be implemented in the next iteration -->
                            <div class="alert alert-info">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Service management will be available in the next update!</span>
                            </div>
                        </div>

                        <!-- Staff Management -->
                        <div v-if="activeTab === 'staff'" class="space-y-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium">Manage Staff</h3>
                                <button class="btn btn-primary btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Staff Member
                                </button>
                            </div>

                            <!-- Staff list will be implemented in the next iteration -->
                            <div class="alert alert-info">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Staff management will be available in the next update!</span>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="mt-6 flex justify-end">
                            <PrimaryButton
                                :class="{ 'opacity-25': processing }"
                                :disabled="processing"
                                @click="updateProfile"
                            >
                                <span v-if="processing">Saving...</span>
                                <span v-else>Save Changes</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 