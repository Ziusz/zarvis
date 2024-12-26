<template>
    <BusinessLayout>
        <Head title="Analytics" />
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Analytics</h1>
            <div class="flex gap-2">
                <select v-model="timeRange" class="select select-bordered">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-title">Total Revenue</div>
                    <div class="stat-value">{{ formatPrice(stats.total_revenue) }}</div>
                    <div class="stat-desc" :class="stats.revenue_trend >= 0 ? 'text-success' : 'text-error'">
                        {{ stats.revenue_trend >= 0 ? '↗' : '↘' }} {{ Math.abs(stats.revenue_trend) }}% from previous period
                    </div>
                </div>
            </div>

            <!-- Total Bookings -->
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="stat-title">Total Bookings</div>
                    <div class="stat-value">{{ stats.total_bookings }}</div>
                    <div class="stat-desc" :class="stats.bookings_trend >= 0 ? 'text-success' : 'text-error'">
                        {{ stats.bookings_trend >= 0 ? '↗' : '↘' }} {{ Math.abs(stats.bookings_trend) }}% from previous period
                    </div>
                </div>
            </div>

            <!-- New Customers -->
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="stat-title">New Customers</div>
                    <div class="stat-value">{{ stats.new_customers }}</div>
                    <div class="stat-desc" :class="stats.customers_trend >= 0 ? 'text-success' : 'text-error'">
                        {{ stats.customers_trend >= 0 ? '↗' : '↘' }} {{ Math.abs(stats.customers_trend) }}% from previous period
                    </div>
                </div>
            </div>

            <!-- Average Rating -->
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div class="stat-title">Average Rating</div>
                    <div class="stat-value">{{ stats.average_rating.toFixed(1) }}</div>
                    <div class="stat-desc" :class="stats.rating_trend >= 0 ? 'text-success' : 'text-error'">
                        {{ stats.rating_trend >= 0 ? '↗' : '↘' }} {{ Math.abs(stats.rating_trend) }}% from previous period
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Revenue Chart -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Revenue Over Time</h2>
                    <div class="h-[300px]">
                        <canvas ref="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bookings Chart -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Bookings Over Time</h2>
                    <div class="h-[300px]">
                        <canvas ref="bookingsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Popular Services -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Popular Services</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="service in stats.popular_services" :key="service.id">
                                    <td>{{ service.name }}</td>
                                    <td>{{ service.bookings_count }}</td>
                                    <td>{{ formatPrice(service.revenue) }}</td>
                                    <td>
                                        <div class="rating rating-sm">
                                            <input 
                                                v-for="i in 5" 
                                                :key="i"
                                                type="radio" 
                                                :class="'mask mask-star-2 ' + (i <= service.rating ? 'bg-warning' : 'bg-base-300')"
                                                disabled
                                            />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Staff Performance -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Staff Performance</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="member in stats.staff_performance" :key="member.id">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar">
                                                <div class="w-8 h-8 rounded-full">
                                                    <img :src="member.avatar" :alt="member.name">
                                                </div>
                                            </div>
                                            <div>{{ member.name }}</div>
                                        </div>
                                    </td>
                                    <td>{{ member.bookings_count }}</td>
                                    <td>{{ formatPrice(member.revenue) }}</td>
                                    <td>
                                        <div class="rating rating-sm">
                                            <input 
                                                v-for="i in 5" 
                                                :key="i"
                                                type="radio" 
                                                :class="'mask mask-star-2 ' + (i <= member.rating ? 'bg-warning' : 'bg-base-300')"
                                                disabled
                                            />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

// State
const timeRange = ref('30');
const revenueChart = ref(null);
const bookingsChart = ref(null);
let revenueChartInstance = null;
let bookingsChartInstance = null;

// Methods
const formatPrice = (price) => {
    return new Intl.NumberFormat('pl-PL', {
        style: 'currency',
        currency: 'PLN'
    }).format(price);
};

const initCharts = () => {
    // Destroy existing charts if they exist
    if (revenueChartInstance) revenueChartInstance.destroy();
    if (bookingsChartInstance) bookingsChartInstance.destroy();

    // Revenue Chart
    revenueChartInstance = new Chart(revenueChart.value, {
        type: 'line',
        data: {
            labels: props.stats.revenue_chart.labels,
            datasets: [{
                label: 'Revenue',
                data: props.stats.revenue_chart.data,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Bookings Chart
    bookingsChartInstance = new Chart(bookingsChart.value, {
        type: 'bar',
        data: {
            labels: props.stats.bookings_chart.labels,
            datasets: [{
                label: 'Bookings',
                data: props.stats.bookings_chart.data,
                backgroundColor: 'rgb(54, 162, 235)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
};

// Watch for time range changes
watch(timeRange, (newValue) => {
    router.get(route('business.analytics'), { range: newValue }, {
        preserveState: true,
        preserveScroll: true,
        only: ['stats'],
    });
});

// Initialize charts when component mounts
onMounted(() => {
    initCharts();
});

// Watch for stats changes to update charts
watch(() => props.stats, () => {
    initCharts();
}, { deep: true });
</script> 