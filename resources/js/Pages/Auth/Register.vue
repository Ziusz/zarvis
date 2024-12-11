<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen bg-base-200 flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <AuthenticationCardLogo class="mx-auto" />
            <h2 class="mt-6 text-3xl font-bold">
                Create your account
            </h2>
            <p class="mt-2 text-base-content/70">
                Join us and start booking your activities
            </p>
        </div>

        <div class="card w-full max-w-md bg-base-100 shadow-xl">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="form-control">
                        <InputLabel for="name" value="Name" class="label" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="input input-bordered w-full"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="form-control mt-4">
                        <InputLabel for="email" value="Email" class="label" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="input input-bordered w-full"
                            required
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
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="form-control mt-4">
                        <InputLabel for="password_confirmation" value="Confirm Password" class="label" />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="input input-bordered w-full"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="form-control mt-4">
                        <label class="cursor-pointer label justify-start gap-2">
                            <Checkbox id="terms" v-model:checked="form.terms" name="terms" required class="checkbox checkbox-primary" />
                            <span class="label-text">
                                I agree to the <a target="_blank" :href="route('terms.show')" class="link link-primary">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="link link-primary">Privacy Policy</a>
                            </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.terms" />
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <Link 
                            :href="route('login')" 
                            class="link link-primary text-sm"
                        >
                            Already have an account?
                        </Link>

                        <PrimaryButton 
                            :class="{ 'loading': form.processing }" 
                            :disabled="form.processing"
                        >
                            Register
                        </PrimaryButton>
                    </div>

                    <div class="divider mt-8">OR</div>

                    <div class="text-center">
                        <button type="button" class="btn btn-outline w-full gap-2">
                            <svg class="size-5" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
                            </svg>
                            Sign up with Google
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
