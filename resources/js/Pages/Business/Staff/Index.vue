<template>
    <BusinessLayout>
        <Head title="Staff Management" />
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Staff Management</h1>
            <div class="flex gap-2">
                <Link 
                    :href="route('business.staff.availability.index')"
                    class="btn btn-primary"
                >
                    Manage Availability
                </Link>
                <button 
                    class="btn btn-primary"
                    @click="openAddStaffModal"
                >
                    Add Staff Member
                </button>
            </div>
        </div>

        <!-- Staff List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Empty State -->
            <div v-if="!staff.length" class="col-span-full">
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium">No staff members yet</h3>
                    <p class="mt-2 text-base-content/70">Get started by adding your first staff member.</p>
                    <button 
                        class="btn btn-primary mt-4"
                        @click="openAddStaffModal"
                    >
                        Add Staff Member
                    </button>
                </div>
            </div>

            <!-- Staff Cards -->
            <div v-for="member in staff" :key="member.id" class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <div class="flex justify-between items-center">
                        <h2 class="card-title">{{ member.name }}</h2>
                        <div class="badge badge-secondary">{{ member.role }}</div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm">{{ member.email }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="text-sm">{{ member.phone }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h3 class="font-medium mb-2">Services</h3>
                        <div class="flex flex-wrap gap-2">
                            <div 
                                v-for="service in member.services" 
                                :key="service.id"
                                class="badge badge-primary"
                            >
                                {{ service.name }}
                            </div>
                            <div v-if="!member.services.length" class="text-sm text-base-content/50">
                                No services assigned
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h3 class="font-medium mb-2">Specialties</h3>
                        <div class="flex flex-wrap gap-2">
                            <div 
                                v-for="specialty in member.specialties" 
                                :key="specialty"
                                class="badge"
                            >
                                {{ specialty }}
                            </div>
                            <div v-if="!member.specialties?.length" class="text-sm text-base-content/50">
                                No specialties listed
                            </div>
                        </div>
                    </div>

                    <div class="card-actions justify-end mt-4">
                        <Link 
                            :href="route('business.staff.availability.show', member.id)"
                            class="btn btn-sm btn-outline"
                        >
                            Availability
                        </Link>
                        <button 
                            class="btn btn-sm btn-outline"
                            @click="editStaff(member)"
                        >
                            Edit
                        </button>
                        <button 
                            class="btn btn-sm btn-error btn-outline"
                            @click="confirmDelete(member)"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Staff Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false">
            <template #title>
                <h3 class="font-bold text-lg">
                    {{ editingStaff ? 'Edit Staff Member' : 'Add Staff Member' }}
                </h3>
            </template>

            <template #content>
                <form id="staffForm" @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="errors?.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="errors?.email" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Phone" />
                        <TextInput
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors?.phone" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Role" />
                        <select 
                            id="role"
                            v-model="form.role"
                            class="select select-bordered w-full mt-1"
                            required
                        >
                            <option value="staff">Staff</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                        <InputError :message="errors?.role" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="services" value="Services" />
                        <div class="mt-2 space-y-2">
                            <label 
                                v-for="service in services" 
                                :key="service.id" 
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="checkbox"
                                    :value="service.id"
                                    v-model="form.services"
                                    class="checkbox"
                                />
                                <span>{{ service.name }}</span>
                            </label>
                        </div>
                        <InputError :message="errors?.services" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="specialties" value="Specialties" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <div 
                                v-for="specialty in form.specialties" 
                                :key="specialty"
                                class="badge gap-2"
                            >
                                {{ specialty }}
                                <button 
                                    type="button"
                                    @click="form.specialties = form.specialties.filter(s => s !== specialty)"
                                    class="btn btn-ghost btn-xs btn-circle"
                                >
                                    ×
                                </button>
                            </div>
                            <input
                                type="text"
                                class="input input-bordered input-sm"
                                placeholder="Add specialty..."
                                @keydown.enter.prevent="
                                    $event.target.value && !form.specialties.includes($event.target.value) && 
                                    form.specialties.push($event.target.value);
                                    $event.target.value = ''
                                "
                            />
                        </div>
                        <InputError :message="errors?.specialties" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="status" value="Status" />
                        <select 
                            id="status"
                            v-model="form.status"
                            class="select select-bordered w-full mt-1"
                            required
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <InputError :message="errors?.status" class="mt-2" />
                    </div>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <button 
                        type="button"
                        class="btn"
                        @click="showAddModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        form="staffForm"
                        class="btn btn-primary"
                        :class="{ 'loading': processing }"
                        :disabled="processing"
                    >
                        {{ editingStaff ? 'Save Changes' : 'Add Staff Member' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                <h3 class="font-bold text-lg">Confirm Delete</h3>
            </template>

            <template #content>
                <p>Are you sure you want to remove {{ staffToDelete?.name }}? This action cannot be undone.</p>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <button 
                        type="button"
                        class="btn"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="btn btn-error"
                        :class="{ 'loading': processing }"
                        :disabled="processing"
                        @click="deleteStaff"
                    >
                        Delete Staff Member
                    </button>
                </div>
            </template>
        </Modal>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    staff: {
        type: Array,
        required: true
    },
    services: {
        type: Array,
        required: true
    },
});

const processing = ref(false);
const showAddModal = ref(false);
const showDeleteModal = ref(false);
const editingStaff = ref(null);
const staffToDelete = ref(null);

const form = ref({
    name: '',
    email: '',
    phone: '',
    role: 'staff',
    specialties: [],
    services: [],
    status: 'active'
});

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        phone: '',
        role: 'staff',
        specialties: [],
        services: [],
        status: 'active'
    };
};

const openAddStaffModal = () => {
    editingStaff.value = null;
    resetForm();
    showAddModal.value = true;
};

const editStaff = (staff) => {
    editingStaff.value = staff;
    form.value = {
        name: staff.name,
        email: staff.email,
        phone: staff.phone,
        role: staff.role,
        specialties: staff.specialties || [],
        services: staff.services.map(s => s.id),
        status: staff.status
    };
    showAddModal.value = true;
};

const confirmDelete = (staff) => {
    staffToDelete.value = staff;
    showDeleteModal.value = true;
};

const submitForm = () => {
    if (processing.value) return;
    
    processing.value = true;
    
    if (editingStaff.value) {
        router.put(route('business.staff.update', editingStaff.value.id), form.value, {
            preserveScroll: true,
            onSuccess: () => {
                showAddModal.value = false;
                processing.value = false;
                resetForm();
            },
            onError: () => {
                processing.value = false;
            },
        });
    } else {
        router.post(route('business.staff.store'), form.value, {
            preserveScroll: true,
            onSuccess: () => {
                showAddModal.value = false;
                processing.value = false;
                resetForm();
            },
            onError: () => {
                processing.value = false;
            },
        });
    }
};

const deleteStaff = () => {
    if (!staffToDelete.value) return;
    
    processing.value = true;
    router.delete(route('business.staff.destroy', staffToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            processing.value = false;
            staffToDelete.value = null;
        },
        onError: () => {
            processing.value = false;
        },
    });
};
</script> 