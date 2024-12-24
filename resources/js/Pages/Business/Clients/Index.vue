<template>
    <BusinessLayout>
        <Head title="Clients Management" />
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Clients Management</h1>
            <button 
                class="btn btn-primary"
                @click="openAddModal"
            >
                Add New Client
            </button>
        </div>

        <!-- Search and Filters -->
        <div class="flex gap-4 mb-6">
            <div class="form-control flex-1">
                <div class="input-group">
                    <input 
                        type="text" 
                        v-model="search"
                        placeholder="Search clients..."
                        class="input input-bordered w-full"
                        @input="handleSearch"
                    />
                    <button class="btn btn-square">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Clients Table -->
        <div class="overflow-x-auto bg-base-100 rounded-lg shadow">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Bookings</th>
                        <th>Last Visit</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="client in clients" :key="client.id">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar">
                                    <div class="w-12 h-12 rounded-full">
                                        <img :src="client.avatar" :alt="client.name">
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold">{{ client.name }}</div>
                                    <div class="text-sm opacity-50">Added {{ formatDate(client.created_at) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ client.email }}</td>
                        <td>{{ client.phone }}</td>
                        <td>{{ client.total_bookings }}</td>
                        <td>{{ client.last_visit ? formatDate(client.last_visit) : 'Never' }}</td>
                        <td>
                            <div class="badge" :class="getStatusClass(client.status)">
                                {{ client.status }}
                            </div>
                        </td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <button 
                                    class="btn btn-ghost btn-sm"
                                    @click="viewClientDetails(client)"
                                >
                                    View
                                </button>
                                <button 
                                    class="btn btn-ghost btn-sm"
                                    @click="editClient(client)"
                                >
                                    Edit
                                </button>
                                <button 
                                    class="btn btn-ghost btn-sm text-error"
                                    @click="confirmDelete(client)"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Client Modal -->
        <dialog id="client_modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">
                    {{ editingClient ? 'Edit Client' : 'Add New Client' }}
                </h3>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="label">
                            <span class="label-text">Name</span>
                            <span class="label-text-alt text-error">*</span>
                        </label>
                        <input 
                            type="text" 
                            v-model="form.name"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': errors.name }"
                            required
                        />
                        <div v-if="errors.name" class="text-error text-sm mt-1">
                            {{ errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input 
                            type="email" 
                            v-model="form.email"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': errors.email }"
                        />
                        <div v-if="errors.email" class="text-error text-sm mt-1">
                            {{ errors.email }}
                        </div>
                    </div>

                    <div>
                        <label class="label">
                            <span class="label-text">Phone</span>
                        </label>
                        <input 
                            type="tel" 
                            v-model="form.phone"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': errors.phone }"
                        />
                        <div v-if="errors.phone" class="text-error text-sm mt-1">
                            {{ errors.phone }}
                        </div>
                    </div>

                    <div>
                        <label class="label">
                            <span class="label-text">Notes</span>
                        </label>
                        <textarea 
                            v-model="form.notes"
                            class="textarea textarea-bordered w-full"
                            :class="{ 'textarea-error': errors.notes }"
                            rows="3"
                        ></textarea>
                        <div v-if="errors.notes" class="text-error text-sm mt-1">
                            {{ errors.notes }}
                        </div>
                    </div>

                    <div class="modal-action">
                        <button type="button" class="btn" @click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            {{ editingClient ? 'Save Changes' : 'Add Client' }}
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>

        <!-- Delete Confirmation Modal -->
        <dialog id="delete_confirmation_modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Delete Client</h3>
                <p class="py-4" v-if="clientToDelete">
                    Are you sure you want to delete <span class="font-semibold">{{ clientToDelete.name }}</span>? 
                    This action cannot be undone.
                </p>
                <div class="modal-action">
                    <button class="btn" @click="closeDeleteModal">Cancel</button>
                    <button 
                        class="btn btn-error" 
                        :disabled="processing"
                        @click="deleteClient"
                    >
                        Delete Client
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>

        <!-- Client Details Modal -->
        <dialog id="client_details_modal" class="modal">
            <div class="modal-box" v-if="selectedClient">
                <h3 class="font-bold text-lg mb-4">Client Details</h3>
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="flex items-center gap-4">
                        <div class="avatar">
                            <div class="w-20 h-20 rounded-full">
                                <img :src="selectedClient.avatar" :alt="selectedClient.name">
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold">{{ selectedClient.name }}</h4>
                            <p class="text-base-content/70">Client since {{ formatDate(selectedClient.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Email</label>
                            <p>{{ selectedClient.email || 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Phone</label>
                            <p>{{ selectedClient.phone || 'Not provided' }}</p>
                        </div>
                    </div>

                    <!-- Booking History -->
                    <div>
                        <h4 class="font-medium mb-2">Recent Bookings</h4>
                        <div class="space-y-2">
                            <div 
                                v-for="booking in selectedClient.recent_bookings" 
                                :key="booking.id"
                                class="bg-base-200 p-3 rounded-lg"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">{{ booking.service?.name || 'Unnamed Service' }}</p>
                                        <p class="text-sm text-base-content/70">
                                            {{ formatDateTime(booking.start_time) }}
                                        </p>
                                    </div>
                                    <div class="badge" :class="getStatusClass(booking.status)">
                                        {{ booking.status }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="!selectedClient.recent_bookings?.length" class="text-base-content/70">
                                No bookings yet
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="selectedClient.notes">
                        <h4 class="font-medium mb-2">Notes</h4>
                        <p class="text-base-content/70">{{ selectedClient.notes }}</p>
                    </div>
                </div>
                <div class="modal-action">
                    <button class="btn" @click="closeDetailsModal">Close</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import dayjs from 'dayjs';
import { debounce } from 'lodash';

const props = defineProps({
    clients: {
        type: Array,
        required: true,
    },
});

// State
const search = ref('');
const processing = ref(false);
const editingClient = ref(null);
const selectedClient = ref(null);
const clientToDelete = ref(null);
const errors = ref({});
const form = useForm({
    name: '',
    email: '',
    phone: '',
    notes: '',
});

// Methods
const formatDate = (date) => {
    return dayjs(date).format('MMM D, YYYY');
};

const formatDateTime = (datetime) => {
    return dayjs(datetime).format('MMM D, YYYY h:mm A');
};

const getStatusClass = (status) => {
    const classes = {
        active: 'badge-success',
        inactive: 'badge-error',
        pending: 'badge-warning',
    };
    return classes[status] || 'badge-ghost';
};

const handleSearch = debounce(() => {
    router.get(route('business.clients.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

const openAddModal = () => {
    editingClient.value = null;
    form.reset();
    errors.value = {};
    document.getElementById('client_modal').showModal();
};

const editClient = (client) => {
    editingClient.value = client;
    form.name = client.name;
    form.email = client.email;
    form.phone = client.phone;
    form.notes = client.notes;
    errors.value = {};
    document.getElementById('client_modal').showModal();
};

const viewClientDetails = (client) => {
    selectedClient.value = client;
    document.getElementById('client_details_modal').showModal();
};

const closeModal = () => {
    document.getElementById('client_modal').close();
    editingClient.value = null;
    form.reset();
    errors.value = {};
};

const closeDetailsModal = () => {
    document.getElementById('client_details_modal').close();
    selectedClient.value = null;
};

const submitForm = () => {
    if (processing.value) return;
    
    processing.value = true;
    errors.value = {};
    
    if (editingClient.value) {
        form.put(route('business.clients.update', editingClient.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                processing.value = false;
            },
            onError: (err) => {
                errors.value = err;
                processing.value = false;
            },
        });
    } else {
        form.post(route('business.clients.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                processing.value = false;
            },
            onError: (err) => {
                errors.value = err;
                processing.value = false;
            },
        });
    }
};

const confirmDelete = (client) => {
    clientToDelete.value = client;
    document.getElementById('delete_confirmation_modal').showModal();
};

const closeDeleteModal = () => {
    document.getElementById('delete_confirmation_modal').close();
    clientToDelete.value = null;
};

const deleteClient = () => {
    if (!clientToDelete.value || processing.value) return;
    
    processing.value = true;
    
    router.delete(route('business.clients.destroy', clientToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};
</script> 