<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    category: Object,
    businesses: Object, // Paginated businesses
    filters: Object,
});

const search = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort || 'recommended');
const selectedServices = ref(props.filters.services || []);

watch([search, sortBy, selectedServices], () => {
    router.get(
        route('categories.show', props.category.slug),
        {
            search: search.value,
            sort: sortBy.value,
            services: selectedServices.value,
        },
        { preserveState: true }
    );
});
</script>

<template>
    <AppLayout>
        <Head :title="category.name" />

        <!-- Hero Section -->
        <div class="hero bg-base-200">
            <div class="hero-content text-center py-12">
                <div>
                    <h1 class="text-4xl font-bold mb-4">{{ category.name }}</h1>
                    <p class="text-lg max-w-2xl">{{ category.description }}</p>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Filters Section -->
            <div class="flex flex-col md:flex-row gap-6 mb-8">
                <!-- Search -->
                <div class="form-control flex-1">
                    <div class="input-group">
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Search businesses..." 
                            class="input input-bordered w-full" 
                        />
                        <button class="btn btn-square">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Sort -->
                <select v-model="sortBy" class="select select-bordered w-full md:w-48">
                    <option value="recommended">Recommended</option>
                    <option value="rating">Highest Rated</option>
                    <option value="reviews">Most Reviews</option>
                    <option value="newest">Newest</option>
                </select>
            </div>

            <!-- Results Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="business in businesses.data" :key="business.id" class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
                    <!-- Business Image -->
                    <figure class="relative">
                        <img 
                            :src="business.logo || '/images/placeholder.jpg'" 
                            :alt="business.name"
                            class="h-48 w-full object-cover"
                        />
                        <div v-if="business.is_verified" class="absolute top-4 right-4 badge badge-primary">Verified</div>
                    </figure>

                    <div class="card-body">
                        <!-- Business Info -->
                        <h2 class="card-title">
                            {{ business.name }}
                        </h2>
                        <p class="line-clamp-2">{{ business.description }}</p>

                        <!-- Stats -->
                        <div class="flex gap-4 my-2 text-sm">
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ business.address }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                {{ business.services_count }} Services
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="flex flex-wrap gap-2 my-2">
                            <div v-for="cat in business.categories" :key="cat.id" class="badge badge-outline">
                                {{ cat.name }}
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="card-actions justify-end mt-4">
                            <Link 
                                :href="route('businesses.show', business.slug)"
                                class="btn btn-primary"
                            >
                                View Services
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="businesses.links.length > 3" class="flex justify-center mt-8">
                <div class="btn-group">
                    <Link
                        v-for="link in businesses.links"
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

            <!-- No Results -->
            <div v-if="businesses.data.length === 0" class="text-center py-12">
                <div class="text-4xl mb-4">😢</div>
                <h3 class="text-2xl font-bold mb-2">No Businesses Found</h3>
                <p class="text-base-content/70">
                    Try adjusting your search or filters to find what you're looking for.
                </p>
            </div>
        </div>
    </AppLayout>
</template> 