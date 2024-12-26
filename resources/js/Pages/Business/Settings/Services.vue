<template>
    <BusinessLayout>
        <Head title="Services Management" />
        
        <div class="space-y-6">
            <!-- Header with Add Button -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Services Management</h1>
                <button 
                    class="btn btn-primary btn-sm gap-2"
                    @click="showAddModal = true"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Service
                </button>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="service in business.services" :key="service.id" class="card bg-base-200 shadow-xl">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <h4 class="card-title">{{ service.name }}</h4>
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm btn-square">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </label>
                                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                    <li>
                                        <a @click="editService(service)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a @click="deleteService(service.id)" class="text-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-sm opacity-70">{{ service.description }}</p>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <div class="badge badge-primary">{{ service.duration }} min</div>
                            <div class="badge badge-secondary">{{ service.price }} PLN</div>
                            <div class="badge badge-outline">Up to {{ service.capacity }} clients</div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!business.services?.length" class="col-span-full">
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium">No services yet</h3>
                        <p class="mt-1 text-sm opacity-70">Get started by adding your first service.</p>
                        <button 
                            class="btn btn-primary mt-4"
                            @click="showAddModal = true"
                        >
                            Add Service
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Service Modal -->
            <Modal :show="showAddModal" @close="closeModal">
                <template #title>
                    <h3 class="font-bold text-lg">
                        {{ editingService ? 'Edit Service' : 'Add New Service' }}
                    </h3>
                </template>
                
                <template #content>
                    <form id="serviceForm" @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <InputLabel for="service-name" value="Service Name" />
                            <TextInput
                                id="service-name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="service-description" value="Description" />
                            <textarea
                                id="service-description"
                                v-model="form.description"
                                class="textarea textarea-bordered w-full h-24 mt-1"
                                placeholder="Describe your service..."
                            />
                            <InputError :message="errors.description" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <InputLabel for="service-duration" value="Duration (min)" />
                                <TextInput
                                    id="service-duration"
                                    v-model="form.duration"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="errors.duration" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="service-price" value="Price" />
                                <TextInput
                                    id="service-price"
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="errors.price" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="service-capacity" value="Capacity" />
                                <TextInput
                                    id="service-capacity"
                                    v-model="form.capacity"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="errors.capacity" class="mt-2" />
                            </div>
                        </div>
                    </form>
                </template>

                <template #footer>
                    <div class="flex justify-end gap-2">
                        <button 
                            type="button" 
                            class="btn btn-ghost"
                            @click="closeModal"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            form="serviceForm"
                            class="btn btn-primary"
                            :class="{ 'loading': processing }"
                            :disabled="processing"
                        >
                            {{ editingService ? 'Save Changes' : 'Add Service' }}
                        </button>
                    </div>
                </template>
            </Modal>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import EmptyState from '@/Components/EmptyState.vue';

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
const showAddModal = ref(false);
const editingService = ref(null);

// Initialize form with default values
const form = ref({
    name: '',
    description: '',
    duration: 60,
    price: '',
    capacity: 1,
    status: 'active'
});

const resetForm = () => {
    form.value = {
        name: '',
        description: '',
        duration: 60,
        price: '',
        capacity: 1,
        status: 'active'
    };
};

const closeModal = () => {
    showAddModal.value = false;
    editingService.value = null;
    resetForm();
};

const editService = (service) => {
    editingService.value = service;
    form.value = { ...service };
    showAddModal.value = true;
};

const submitForm = () => {
    if (processing.value) return;
    
    processing.value = true;
    
    if (editingService.value) {
        router.put(`/business/services/${editingService.value.id}`, form.value, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                processing.value = false;
            },
            onError: () => {
                processing.value = false;
            },
        });
    } else {
        router.post('/business/services', form.value, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                processing.value = false;
            },
            onError: () => {
                processing.value = false;
            },
        });
    }
};

const deleteService = (serviceId) => {
    if (confirm('Are you sure you want to delete this service?')) {
        processing.value = true;
        router.delete(`/business/services/${serviceId}`, {
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
</script> 