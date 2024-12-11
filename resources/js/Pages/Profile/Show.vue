<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">
                    Profile Settings
                </h2>
                <div class="tabs tabs-boxed">
                    <a class="tab tab-active">General</a>
                    <a class="tab">Security</a>
                    <a class="tab">Sessions</a>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-4xl mx-auto">
                <!-- Profile Information -->
                <div v-if="$page.props.jetstream.canUpdateProfileInformation" class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <UpdateProfileInformationForm :user="$page.props.auth.user" />
                    </div>
                </div>

                <!-- Password Update -->
                <div v-if="$page.props.jetstream.canUpdatePassword" class="card bg-base-100 shadow-xl mt-8">
                    <div class="card-body">
                        <UpdatePasswordForm />
                    </div>
                </div>

                <!-- Two Factor Authentication -->
                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication" class="card bg-base-100 shadow-xl mt-8">
                    <div class="card-body">
                        <TwoFactorAuthenticationForm
                            :requires-confirmation="confirmsTwoFactorAuthentication"
                        />
                    </div>
                </div>

                <!-- Browser Sessions -->
                <div class="card bg-base-100 shadow-xl mt-8">
                    <div class="card-body">
                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                    </div>
                </div>

                <!-- Delete Account -->
                <div v-if="$page.props.jetstream.hasAccountDeletionFeatures" class="card bg-base-100 shadow-xl mt-8">
                    <div class="card-body">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
