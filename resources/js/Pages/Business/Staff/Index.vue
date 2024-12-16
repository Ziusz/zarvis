<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
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
            <dialog id="staff_modal" class="modal">
                <!-- Modal content here -->
            </dialog>

            <!-- Delete Confirmation Modal -->
            <dialog id="delete_modal" class="modal">
                <!-- Modal content here -->
            </dialog>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    staff: Array,
    services: Array,
});

// ... rest of your script
</script> 