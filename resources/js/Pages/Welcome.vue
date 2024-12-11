<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-base-200">
        <!-- Hero Section -->
        <div class="hero min-h-[70vh] bg-base-100">
            <div class="hero-content flex-col lg:flex-row-reverse gap-8">
                <img src="https://daisyui.com/images/stock/photo-1635805737707-575885ab0820.jpg" class="max-w-sm rounded-lg shadow-2xl" />
                <div>
                    <h1 class="text-5xl font-bold">Book Your Next Adventure!</h1>
                    <p class="py-6 text-lg">
                        Discover and book the best sports activities and entertainment venues in your area. 
                        From fitness classes to sports facilities, we've got you covered.
                    </p>
                    <div class="flex gap-4">
                        <Link v-if="canRegister" :href="route('register')" class="btn btn-primary">
                            Get Started
                        </Link>
                        <Link v-if="canLogin && !$page.props.auth.user" :href="route('login')" class="btn btn-outline">
                            Sign In
                        </Link>
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-primary">
                            Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold mb-4">Why Choose Us?</h2>
                    <p class="text-lg text-base-content/70">Experience the best booking platform for sports and entertainment</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Easy Booking -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body items-center text-center">
                            <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="card-title">Easy Booking</h3>
                            <p>Book your favorite activities in just a few clicks</p>
                        </div>
                    </div>

                    <!-- Real-time Availability -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body items-center text-center">
                            <div class="size-16 rounded-full bg-secondary/10 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="card-title">Real-time Availability</h3>
                            <p>See real-time schedules and availability</p>
                        </div>
                    </div>

                    <!-- Secure Payments -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body items-center text-center">
                            <div class="size-16 rounded-full bg-accent/10 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="card-title">Secure Payments</h3>
                            <p>Safe and secure payment processing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-base-100 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold mb-4">Ready to Get Started?</h2>
                    <p class="text-lg text-base-content/70 mb-8">Join thousands of happy customers booking their activities with us</p>
                    <div class="flex justify-center gap-4">
                        <Link v-if="canRegister" :href="route('register')" class="btn btn-primary">
                            Create Account
                        </Link>
                        <Link v-if="canLogin && !$page.props.auth.user" :href="route('login')" class="btn btn-outline">
                            Sign In
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer footer-center p-10 bg-base-300 text-base-content">
            <aside>
                <svg width="50" height="50" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" class="inline-block fill-current">
                    <path d="M22.672 15.226l-2.432.811.841 2.515c.33 1.019-.209 2.127-1.23 2.456-1.15.325-2.148-.321-2.463-1.226l-.84-2.518-5.013 1.677.84 2.517c.391 1.203-.434 2.542-1.831 2.542-.88 0-1.601-.564-1.86-1.314l-.842-2.516-2.431.809c-1.135.328-2.145-.317-2.463-1.229-.329-1.018.211-2.127 1.231-2.456l2.432-.809-1.621-4.823-2.432.808c-1.355.384-2.558-.59-2.558-1.839 0-.817.509-1.582 1.327-1.846l2.433-.809-.842-2.515c-.33-1.02.211-2.129 1.232-2.458 1.02-.329 2.13.209 2.461 1.229l.842 2.515 5.011-1.677-.839-2.517c-.403-1.238.484-2.553 1.843-2.553.819 0 1.585.509 1.85 1.326l.841 2.517 2.431-.81c1.02-.33 2.131.211 2.461 1.229.332 1.018-.21 2.126-1.23 2.456l-2.433.809 1.622 4.823 2.433-.809c1.242-.401 2.557.484 2.557 1.838 0 .819-.51 1.583-1.328 1.847m-8.992-6.428l-5.01 1.675 1.619 4.828 5.011-1.674-1.62-4.829z"></path>
                </svg>
                <p class="font-bold">
                    Sports Booking Ltd. <br/>Providing reliable booking services since 2024
                </p>
                <p>Copyright © 2024 - All rights reserved</p>
            </aside> 
            <nav>
                <div class="grid grid-flow-col gap-4">
                    <a class="link link-hover">About us</a>
                    <a class="link link-hover">Contact</a>
                    <a class="link link-hover">Terms</a>
                    <a class="link link-hover">Privacy</a>
                </div>
            </nav>
        </footer>
    </div>
</template>
