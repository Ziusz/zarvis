<template>
    <SettingsLayout :business="business">
        <div class="space-y-6">
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
    </SettingsLayout>
</template>

<script setup>
import { ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({
    business: {
        type: Object,
        required: true,
    },
});</script> 