<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatCurrency } from '@/utils';

const props = defineProps({
    business: Object,
    services: Object, // Paginated services
});

const activeTab = ref('services');
const selectedCategory = ref('all');

const filteredServices = computed(() => {
    if (selectedCategory.value === 'all') {
        return props.services.data;
    }
    return props.services.data.filter(service => 
        service.category_id === selectedCategory.value
    );
});

const uniqueCategories = computed(() => {
    const categories = new Set();
    props.services.data.forEach(service => {
        if (service.category) {
            categories.add(service.category);
        }
    });
    return Array.from(categories);
});
</script>

<template>
    <AppLayout>
        <Head :title="business.name" />

        <!-- Hero Section with Cover Image -->
        <div class="relative h-64 md:h-96 bg-base-300">
            <img 
                :src="business.cover_image || '/images/placeholder-cover.jpg'" 
                :alt="business.name"
                class="w-full h-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-base-300 to-transparent"></div>
            
            <!-- Business Info Overlay -->
            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                <div class="container mx-auto flex items-end gap-6">
                    <img 
                        :src="business.logo || '/images/placeholder.jpg'" 
                        :alt="business.name"
                        class="w-24 h-24 md:w-32 md:h-32 rounded-lg shadow-lg object-cover"
                    />
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h1 class="text-3xl md:text-4xl font-bold">{{ business.name }}</h1>
                            <div v-if="business.is_verified" class="badge badge-primary">Verified</div>
                        </div>
                        <p class="text-lg opacity-90 max-w-2xl">{{ business.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <!-- Quick Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Location -->
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-body">
                        <h3 class="card-title text-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Location
                        </h3>
                        <p>{{ business.address }}</p>
                        <a 
                            :href="`https://maps.google.com/?q=${business.address}`" 
                            target="_blank"
                            class="link link-primary"
                        >
                            View on Map
                        </a>
                    </div>
                </div>

                <!-- Contact -->
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-body">
                        <h3 class="card-title text-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Contact
                        </h3>
                        <div class="space-y-2">
                            <p>{{ business.phone }}</p>
                            <p>{{ business.email }}</p>
                            <a 
                                v-if="business.website"
                                :href="business.website"
                                target="_blank"
                                class="link link-primary"
                            >
                                Visit Website
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Hours -->
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-body">
                        <h3 class="card-title text-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Business Hours
                        </h3>
                        <div class="space-y-1">
                            <div v-for="(hours, day) in business.business_hours" :key="day" class="flex justify-between">
                                <span class="capitalize">{{ day }}</span>
                                <span>{{ hours.join(' - ') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs tabs-boxed justify-center mb-8">
                <button 
                    class="tab"
                    :class="{ 'tab-active': activeTab === 'services' }"
                    @click="activeTab = 'services'"
                >
                    Services
                </button>
                <button 
                    class="tab"
                    :class="{ 'tab-active': activeTab === 'reviews' }"
                    @click="activeTab = 'reviews'"
                >
                    Reviews
                </button>
                <button 
                    class="tab"
                    :class="{ 'tab-active': activeTab === 'gallery' }"
                    @click="activeTab = 'gallery'"
                >
                    Gallery
                </button>
            </div>

            <!-- Services Tab -->
            <div v-if="activeTab === 'services'" class="space-y-8">
                <!-- Category Filter -->
                <div class="flex justify-center flex-wrap gap-2">
                    <button 
                        class="btn btn-sm"
                        :class="{ 'btn-primary': selectedCategory === 'all' }"
                        @click="selectedCategory = 'all'"
                    >
                        All Services
                    </button>
                    <button 
                        v-for="category in uniqueCategories"
                        :key="category.id"
                        class="btn btn-sm"
                        :class="{ 'btn-primary': selectedCategory === category.id }"
                        @click="selectedCategory = category.id"
                    >
                        {{ category.name }}
                    </button>
                </div>

                <!-- Services Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="service in filteredServices" :key="service.id" class="card bg-base-100 shadow-xl">
                        <figure>
                            <img 
                                :src="service.images?.main || '/images/placeholder.jpg'" 
                                :alt="service.name"
                                class="h-48 w-full object-cover"
                            />
                        </figure>
                        <div class="card-body">
                            <h3 class="card-title">
                                {{ service.name }}
                                <div class="badge badge-secondary">{{ service.duration }}min</div>
                            </h3>
                            <p class="line-clamp-2">{{ service.description }}</p>
                            
                            <!-- Service Details -->
                            <div class="space-y-2 my-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold">
                                        {{ formatCurrency(service.price) }}
                                    </span>
                                    <div class="badge badge-outline">
                                        {{ service.settings?.skill_level }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Capacity: {{ service.capacity }} people
                                </div>
                            </div>

                            <!-- Action -->
                            <div class="card-actions justify-end">
                                <button class="btn btn-primary">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="services.links.length > 3" class="flex justify-center mt-8">
                    <div class="btn-group">
                        <Link
                            v-for="link in services.links"
                            :key="link.label"
                            :href="link.url"
                            class="btn"
                            :class="{
                                'btn-active': link.active,
                                'btn-disabled': !link.url
                            }"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div v-else-if="activeTab === 'reviews'" class="text-center py-12">
                <div class="text-4xl mb-4">🌟</div>
                <h3 class="text-2xl font-bold mb-2">Reviews Coming Soon!</h3>
                <p class="text-base-content/70">
                    We're working on bringing you customer reviews. Stay tuned!
                </p>
            </div>

            <!-- Gallery Tab -->
            <div v-else-if="activeTab === 'gallery'" class="text-center py-12">
                <div class="text-4xl mb-4">📸</div>
                <h3 class="text-2xl font-bold mb-2">Gallery Coming Soon!</h3>
                <p class="text-base-content/70">
                    Check back soon to see photos of our facilities and services!
                </p>
            </div>
        </div>
    </AppLayout>
</template> 