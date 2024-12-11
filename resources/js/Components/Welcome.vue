<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="min-h-screen">
        <div class="hero bg-base-200">
            <div class="hero-content text-center py-12">
                <div class="max-w-3xl">
                    <ApplicationLogo class="h-12 w-auto mx-auto" />

                    <h1 class="text-4xl font-bold mt-8 text-base-content">
                        Welcome to your Booking System!
                    </h1>

                    <p class="py-6 text-base-content/70">
                        This is your new booking system powered by Laravel Jetstream and DaisyUI. We've crafted a beautiful and functional 
                        starting point for your business. Manage bookings, handle customers, and grow your business with our powerful tools.
                    </p>
                </div>
            </div>
        </div>

        <div class="container mx-auto p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Documentation Card -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-6 stroke-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <h2 class="card-title">Documentation</h2>
                        </div>
                        
                        <p class="text-base-content/70">
                            Our comprehensive documentation covers every aspect of the booking system. Learn how to manage bookings, 
                            handle customers, and customize the system to your needs.
                        </p>
                        
                        <div class="card-actions justify-end">
                            <a href="https://laravel.com/docs" class="btn btn-primary btn-sm">
                                Explore Docs
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="size-4 fill-current">
                                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Video Tutorials Card -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-6 stroke-primary">
                                <path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            <h2 class="card-title">Video Tutorials</h2>
                        </div>
                        
                        <p class="text-base-content/70">
                            Watch our video tutorials to quickly get started with the system. Learn best practices and tips 
                            for managing your business effectively.
                        </p>
                        
                        <div class="card-actions justify-end">
                            <a href="https://laracasts.com" class="btn btn-primary btn-sm">
                                Watch Tutorials
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="size-4 fill-current">
                                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- UI Components Card -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-6 stroke-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <h2 class="card-title">Beautiful UI</h2>
                        </div>
                        
                        <p class="text-base-content/70">
                            Built with DaisyUI and Tailwind CSS, our system provides a modern and responsive interface 
                            that your customers will love to use.
                        </p>
                        
                        <div class="card-actions justify-end">
                            <a href="https://daisyui.com" class="btn btn-primary btn-sm">
                                Explore Components
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="size-4 fill-current">
                                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Security Card -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-6 stroke-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            <h2 class="card-title">Security First</h2>
                        </div>
                        
                        <p class="text-base-content/70">
                            Your data security is our priority. Built-in authentication, authorization, and data protection 
                            features keep your business information safe.
                        </p>
                        
                        <div class="card-actions justify-end">
                            <a href="#" class="btn btn-primary btn-sm">
                                Learn More
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="size-4 fill-current">
                                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
