<template>
    <SettingsLayout :business="business">
        <div class="space-y-6">
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

            <!-- Save Button -->
            <div class="mt-6 flex justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                    @click="updateHours"
                >
                    <span v-if="processing">Saving...</span>
                    <span v-else>Save Changes</span>
                </PrimaryButton>
            </div>
        </div>
    </SettingsLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
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

const processing = ref(false);

const days = [
    { key: 'monday', label: 'Monday' },
    { key: 'tuesday', label: 'Tuesday' },
    { key: 'wednesday', label: 'Wednesday' },
    { key: 'thursday', label: 'Thursday' },
    { key: 'friday', label: 'Friday' },
    { key: 'saturday', label: 'Saturday' },
    { key: 'sunday', label: 'Sunday' },
];

const defaultWorkingHours = {
    monday: { is_open: true, start: '09:00', end: '17:00' },
    tuesday: { is_open: true, start: '09:00', end: '17:00' },
    wednesday: { is_open: true, start: '09:00', end: '17:00' },
    thursday: { is_open: true, start: '09:00', end: '17:00' },
    friday: { is_open: true, start: '09:00', end: '17:00' },
    saturday: { is_open: false, start: '09:00', end: '17:00' },
    sunday: { is_open: false, start: '09:00', end: '17:00' },
};

// Initialize form with default working hours
const form = ref({
    working_hours: { ...defaultWorkingHours }
});

onMounted(() => {
    let openingHours = props.business.opening_hours;
    if (typeof openingHours === 'string') {
        try {
            openingHours = JSON.parse(openingHours);
        } catch (e) {
            console.error('Error parsing opening hours:', e);
            openingHours = null;
        }
    }

    // Convert opening_hours format to working_hours format
    if (openingHours) {
        const convertedHours = {};
        Object.keys(openingHours).forEach(day => {
            convertedHours[day] = {
                is_open: !openingHours[day].closed,
                start: openingHours[day].open,
                end: openingHours[day].close,
            };
        });
        form.value.working_hours = convertedHours;
    }
});

const updateHours = () => {
    processing.value = true;

    router.post(route('business.settings.hours.update'), {
        _method: 'PUT',
        ...form.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};</script> 