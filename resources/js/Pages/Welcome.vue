<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    categories: Array,
    featuredBusinesses: Array,
});

const activeTab = ref('all');
</script>

<template>
    <Head title="Welcome" />

    <GuestLayout>
        <!-- Hero Section -->
        <div class="hero min-h-[70vh] bg-base-200">
            <div class="hero-content text-center">
                <div class="max-w-3xl">
                    <h1 class="text-5xl font-bold mb-8">Find and Book Amazing Services</h1>
                    <p class="text-xl mb-8">Discover the best sports, fitness, and entertainment services in your area.</p>
                    
                    <!-- Search Bar -->
                    <div class="join w-full max-w-2xl">
                        <input type="text" placeholder="Search services..." class="input input-bordered join-item flex-1" />
                        <button class="btn btn-primary join-item">Search</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Section -->
        <div class="py-16 bg-base-100">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Browse Categories</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="category in categories" :key="category.id" class="card bg-base-200 hover:shadow-xl transition-shadow">
                        <Link :href="route('categories.show', category.slug)" class="card-body text-center">
                            <div class="text-4xl mb-4">
                                <i :class="category.icon"></i>
                            </div>
                            <h3 class="card-title justify-center">{{ category.name }}</h3>
                            <p class="text-sm opacity-70">{{ category.description }}</p>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Businesses -->
        <div class="py-16 bg-base-300">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Featured Businesses</h2>

                <!-- Category Tabs -->
                <div class="tabs tabs-boxed justify-center mb-8">
                    <button 
                        class="tab" 
                        :class="{ 'tab-active': activeTab === 'all' }"
                        @click="activeTab = 'all'"
                    >
                        All
                    </button>
                    <button 
                        v-for="category in categories.slice(0, 4)" 
                        :key="category.id"
                        class="tab"
                        :class="{ 'tab-active': activeTab === category.id }"
                        @click="activeTab = category.id"
                    >
                        {{ category.name }}
                    </button>
                </div>

                <!-- Business Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="business in featuredBusinesses" :key="business.id" class="card bg-base-100 shadow-xl">
                        <figure>
                            <img :src="business.logo" :alt="business.name" class="h-48 w-full object-cover" />
                        </figure>
                        <div class="card-body">
                            <h3 class="card-title">
                                {{ business.name }}
                                <div class="badge badge-primary" v-if="business.is_verified">Verified</div>
                            </h3>
                            <p class="line-clamp-2">{{ business.description }}</p>
                            <div class="flex gap-2 mt-2">
                                <div class="badge badge-outline" v-for="category in business.categories?.slice(0, 3)" :key="category.id">
                                    {{ category.name }}
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-4">
                                <Link :href="route('businesses.show', business.slug)" class="btn btn-primary">
                                    View Services
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-16 bg-base-100">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Why Choose Us</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Easy Booking -->
                    <div class="card bg-base-200">
                        <div class="card-body items-center text-center">
                            <div class="text-4xl mb-4">📅</div>
                            <h3 class="card-title">Easy Booking</h3>
                            <p>Book your favorite services in just a few clicks</p>
                        </div>
                    </div>

                    <!-- Verified Providers -->
                    <div class="card bg-base-200">
                        <div class="card-body items-center text-center">
                            <div class="text-4xl mb-4">✓</div>
                            <h3 class="card-title">Verified Providers</h3>
                            <p>All businesses are verified for quality service</p>
                        </div>
                    </div>

                    <!-- Instant Confirmation -->
                    <div class="card bg-base-200">
                        <div class="card-body items-center text-center">
                            <div class="text-4xl mb-4">⚡</div>
                            <h3 class="card-title">Instant Confirmation</h3>
                            <p>Get immediate confirmation for your bookings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="py-16 bg-primary text-primary-content">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold mb-6">Ready to Get Started?</h2>
                <p class="mb-8 text-lg">Join thousands of happy customers booking services every day</p>
                <div class="flex justify-center gap-4">
                    <Link :href="route('register')" class="btn btn-secondary">
                        Sign Up Now
                    </Link>
                    <Link :href="route('businesses.register')" class="btn btn-outline">
                        List Your Business
                    </Link>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
