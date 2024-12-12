<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    slug: '',
    description: '',
    street_address: '',
    city: '',
    postal_code: '',
    nip: '',
    phone: '',
    email: '',
    website: '',
    logo: null,
    cover_image: null,
    opening_hours: {
        monday: { open: '09:00', close: '17:00', closed: false },
        tuesday: { open: '09:00', close: '17:00', closed: false },
        wednesday: { open: '09:00', close: '17:00', closed: false },
        thursday: { open: '09:00', close: '17:00', closed: false },
        friday: { open: '09:00', close: '17:00', closed: false },
        saturday: { open: '10:00', close: '15:00', closed: false },
        sunday: { open: '10:00', close: '15:00', closed: true },
    },
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

const validateNIP = (nip) => {
    if (!nip) return true; // Optional field
    const weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
    const cleanNIP = nip.replace(/[^0-9]/g, '');
    
    if (cleanNIP.length !== 10) return false;
    
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += weights[i] * parseInt(cleanNIP[i]);
    }
    
    const checksum = sum % 11;
    return checksum === parseInt(cleanNIP[9]);
};

const submit = () => {
    if (form.nip && !validateNIP(form.nip)) {
        form.setError('nip', 'Invalid NIP number');
        return;
    }
    
    form.post(route('businesses.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Register Business" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold">
                        Register Your Business
                    </h2>
                    <p class="mt-2 text-base-content/70">
                        Start managing your bookings and growing your business
                    </p>
                </div>

                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-control">
                                    <InputLabel for="name" value="Business Name" class="label" />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="input input-bordered w-full"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="form-control">
                                    <InputLabel for="slug" value="URL Slug" class="label" />
                                    <TextInput
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        class="input input-bordered w-full"
                                        required
                                    />
                                    <p class="text-xs text-base-content/70 mt-1">
                                        This will be your business URL: example.com/businesses/{{ form.slug || 'your-business' }}
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.slug" />
                                </div>
                            </div>

                            <div class="form-control">
                                <InputLabel for="description" value="Description" class="label" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="textarea textarea-bordered h-24"
                                    required
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-control">
                                    <InputLabel for="email" value="Business Email" class="label" />
                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="input input-bordered w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div class="form-control">
                                    <InputLabel for="phone" value="Phone Number" class="label" />
                                    <TextInput
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="input input-bordered w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Address Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="form-control">
                                        <InputLabel for="street_address" value="Street Address" class="label" />
                                        <TextInput
                                            id="street_address"
                                            v-model="form.street_address"
                                            type="text"
                                            class="input input-bordered w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.street_address" />
                                    </div>

                                    <div class="form-control">
                                        <InputLabel for="city" value="City" class="label" />
                                        <TextInput
                                            id="city"
                                            v-model="form.city"
                                            type="text"
                                            class="input input-bordered w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.city" />
                                    </div>

                                    <div class="form-control">
                                        <InputLabel for="postal_code" value="Postal Code" class="label" />
                                        <TextInput
                                            id="postal_code"
                                            v-model="form.postal_code"
                                            type="text"
                                            class="input input-bordered w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.postal_code" />
                                    </div>

                                    <div class="form-control">
                                        <InputLabel for="nip" value="NIP (Tax ID)" class="label" />
                                        <TextInput
                                            id="nip"
                                            v-model="form.nip"
                                            type="text"
                                            class="input input-bordered w-full"
                                            placeholder="e.g., 1234567890"
                                            maxlength="10"
                                        />
                                        <p class="text-xs text-base-content/70 mt-1">
                                            10-digit Tax Identification Number (optional)
                                        </p>
                                        <InputError class="mt-2" :message="form.errors.nip" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-control">
                                <InputLabel for="website" value="Website (Optional)" class="label" />
                                <TextInput
                                    id="website"
                                    v-model="form.website"
                                    type="url"
                                    class="input input-bordered w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.website" />
                            </div>

                            <!-- Opening Hours -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Opening Hours</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="day in days" :key="day.key" class="card bg-base-200">
                                        <div class="card-body p-4">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium">{{ day.label }}</span>
                                                <label class="label cursor-pointer">
                                                    <span class="label-text mr-2">Closed</span>
                                                    <input
                                                        type="checkbox"
                                                        v-model="form.opening_hours[day.key].closed"
                                                        class="toggle toggle-primary"
                                                    />
                                                </label>
                                            </div>
                                            <div v-if="!form.opening_hours[day.key].closed" class="grid grid-cols-2 gap-2 mt-2">
                                                <div class="form-control">
                                                    <input
                                                        type="time"
                                                        v-model="form.opening_hours[day.key].open"
                                                        class="input input-bordered input-sm"
                                                    />
                                                </div>
                                                <div class="form-control">
                                                    <input
                                                        type="time"
                                                        v-model="form.opening_hours[day.key].close"
                                                        class="input input-bordered input-sm"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :class="{ 'loading': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Register Business
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 