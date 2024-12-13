<template>
    <SettingsLayout :business="business">
        <div class="space-y-6">
            <!-- Add New Service Form -->
            <div class="card bg-base-200 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title text-lg font-semibold mb-4">Add New Service</h3>
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="service-name" value="Service Name" />
                                <TextInput
                                    id="service-name"
                                    v-model="form.newService.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="errors['newService.name']" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="service-description" value="Description" />
                                <textarea
                                    id="service-description"
                                    v-model="form.newService.description"
                                    class="textarea textarea-bordered w-full h-24 mt-1"
                                    placeholder="Describe your service..."
                                />
                                <InputError :message="errors['newService.description']" class="mt-2" />
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="service-duration" value="Duration (minutes)" />
                                <TextInput
                                    id="service-duration"
                                    v-model="form.newService.duration"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="errors['newService.duration']" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="service-price" value="Price" />
                                <TextInput
                                    id="service-price"
                                    v-model="form.newService.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="errors['newService.price']" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="service-capacity" value="Capacity (clients per session)" />
                                <TextInput
                                    id="service-capacity"
                                    v-model="form.newService.capacity"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="errors['newService.capacity']" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <PrimaryButton
                            :class="{ 'opacity-25': processing }"
                            :disabled="processing"
                            @click="addService"
                        >
                            Add Service
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Existing Services List -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Your Services</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div v-for="service in form.services" :key="service.id" class="card bg-base-200 shadow-xl">
                        <div class="card-body">
                            <div class="flex justify-between items-start">
                                <div class="space-y-2">
                                    <h4 class="card-title">{{ service.name }}</h4>
                                    <p class="text-sm">{{ service.description }}</p>
                                    <div class="flex gap-4 text-sm">
                                        <span class="badge badge-primary">{{ service.duration }} min</span>
                                        <span class="badge badge-secondary">{{ service.price }} PLN</span>
                                        <span class="badge">Up to {{ service.capacity }} clients</span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button 
                                        class="btn btn-sm btn-ghost"
                                        @click="updateService(service)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button 
                                        class="btn btn-sm btn-ghost text-error"
                                        @click="deleteService(service.id)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
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

const processing = ref(false);

const form = ref({
    services: props.business.services || [],
    newService: {
        name: '',
        description: '',
        duration: 60,
        price: '',
        capacity: 1,
        status: 'active'
    }
});

const addService = () => {
    processing.value = true;
    router.post(route('business.services.store'), form.value.newService, {
        preserveScroll: true,
        onSuccess: () => {
            form.value.newService = {
                name: '',
                description: '',
                duration: 60,
                price: '',
                capacity: 1,
                status: 'active'
            };
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

const deleteService = (serviceId) => {
    if (confirm('Are you sure you want to delete this service?')) {
        processing.value = true;
        router.delete(route('business.services.destroy', serviceId), {
            preserveScroll: true,
            onSuccess: () => {
                processing.value = false;
            },
            onError: () => {
                processing.value = false;
            },
        });
    }
};

const updateService = (service) => {
    processing.value = true;
    router.put(route('business.services.update', service.id), service, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};</script> 