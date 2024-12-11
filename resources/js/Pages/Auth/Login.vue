<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout title="Log in">
        <div class="min-h-[80vh] flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold">
                    Welcome back!
                </h2>
                <p class="mt-2 text-base-content/70">
                    Please sign in to your account
                </p>
            </div>

            <div class="card w-full max-w-md bg-base-100 shadow-xl">
                <div class="card-body">
                    <div v-if="status" class="alert alert-success mb-4">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit">
                        <div class="form-control">
                            <InputLabel for="email" value="Email" class="label" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="input input-bordered w-full"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="form-control mt-4">
                            <InputLabel for="password" value="Password" class="label" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="input input-bordered w-full"
                                required
                                autocomplete="current-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="form-control mt-4">
                            <label class="cursor-pointer label justify-start gap-2">
                                <Checkbox v-model:checked="form.remember" name="remember" class="checkbox checkbox-primary" />
                                <span class="label-text">Remember me</span>
                            </label>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <Link 
                                v-if="canResetPassword" 
                                :href="route('password.request')" 
                                class="link link-primary text-sm"
                            >
                                Forgot your password?
                            </Link>

                            <PrimaryButton 
                                :class="{ 'loading': form.processing }" 
                                :disabled="form.processing"
                            >
                                Log in
                            </PrimaryButton>
                        </div>

                        <div class="divider mt-8">OR</div>

                        <div class="text-center">
                            <p class="text-base-content/70">
                                Don't have an account?
                                <Link :href="route('register')" class="link link-primary ml-1">
                                    Sign up
                                </Link>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
