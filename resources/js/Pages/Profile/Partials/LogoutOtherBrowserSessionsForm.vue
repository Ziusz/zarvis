<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import ActionSection from '@/Components/ActionSection.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    sessions: Array,
});

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmLogout = () => {
    confirmingLogout.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;

    form.reset();
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium">Browser Sessions</h3>
                <p class="mt-1 text-sm text-base-content/70">
                    Manage and log out your active sessions on other browsers and devices.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                    Done.
                </ActionMessage>

                <PrimaryButton 
                    @click="confirmLogout"
                    class="btn btn-primary gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Log Out Other Sessions
                </PrimaryButton>
            </div>
        </div>

        <div class="divider"></div>

        <div class="max-w-xl">
            <div class="alert alert-info mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm">
                        If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.
                    </p>
                </div>
            </div>

            <!-- Other Browser Sessions -->
            <div v-if="sessions.length > 0" class="space-y-4">
                <div v-for="(session, i) in sessions" :key="i" class="card bg-base-200">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-4">
                            <div class="bg-base-100 p-2 rounded-lg">
                                <svg v-if="session.agent.is_desktop" class="size-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                </svg>

                                <svg v-else class="size-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                            </div>

                            <div class="flex-1">
                                <h3 class="font-medium">
                                    {{ session.agent.platform ? session.agent.platform : 'Unknown' }} - {{ session.agent.browser ? session.agent.browser : 'Unknown' }}
                                </h3>

                                <div class="flex items-center gap-2 mt-1 text-sm text-base-content/70">
                                    <span>{{ session.ip_address }}</span>
                                    <span class="text-xs">•</span>
                                    <span v-if="session.is_current_device" class="badge badge-success">This device</span>
                                    <span v-else>Last active {{ session.last_active }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Out Other Devices Confirmation Modal -->
            <DialogModal :show="confirmingLogout" @close="closeModal">
                <template #title>
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Log Out Other Browser Sessions
                    </div>
                </template>

                <template #content>
                    <div class="space-y-4">
                        <p class="text-sm text-base-content/70">
                            Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.
                        </p>

                        <div class="form-control">
                            <div class="relative">
                                <TextInput
                                    ref="passwordInput"
                                    v-model="form.password"
                                    type="password"
                                    class="input input-bordered w-full pr-10"
                                    placeholder="Enter your password to confirm"
                                    autocomplete="current-password"
                                    @keyup.enter="logoutOtherBrowserSessions"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>
                    </div>
                </template>

                <template #footer>
                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="closeModal" class="btn btn-ghost">
                            Cancel
                        </SecondaryButton>

                        <PrimaryButton
                            class="btn btn-primary"
                            :class="{ 'loading': form.processing }"
                            :disabled="form.processing"
                            @click="logoutOtherBrowserSessions"
                        >
                            Log Out Other Sessions
                        </PrimaryButton>
                    </div>
                </template>
            </DialogModal>
        </div>
    </div>
</template>
