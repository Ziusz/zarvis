<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';

defineProps({
    title: String,
});
</script>

<template>
    <div class="min-h-screen bg-base-200">
        <Head :title="title" />

        <!-- Navbar -->
        <div class="navbar bg-base-100 shadow-lg">
            <div class="navbar-start">
                <!-- Logo -->
                <Link href="/" class="btn btn-ghost">
                    <ApplicationMark class="h-8 w-auto" />
                </Link>
            </div>

            <div class="navbar-center">
                <h1 class="text-xl font-semibold">{{ title }}</h1>
            </div>

            <div class="navbar-end">
                <!-- Theme Switcher -->
                <ThemeSwitcher />

                <!-- Auth Links -->
                <div class="hidden sm:flex items-center gap-2 ml-4">
                    <Link 
                        v-if="route().current('login')"
                        :href="route('register')" 
                        class="btn btn-ghost"
                    >
                        Register
                    </Link>
                    <Link 
                        v-else-if="route().current('register')"
                        :href="route('login')" 
                        class="btn btn-ghost"
                    >
                        Login
                    </Link>
                    <template v-else>
                        <Link 
                            :href="route('login')" 
                            class="btn btn-ghost"
                        >
                            Login
                        </Link>
                        <Link 
                            :href="route('register')" 
                            class="btn btn-primary"
                        >
                            Get Started
                        </Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template> 