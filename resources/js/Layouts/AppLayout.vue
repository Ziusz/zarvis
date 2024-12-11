<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-base-200">
        <Head :title="title" />

        <Banner />

        <!-- Navbar -->
        <div class="navbar bg-base-100 shadow-lg">
            <div class="navbar-start">
                <!-- Mobile Menu -->
                <div class="dropdown">
                    <label tabindex="0" class="btn btn-ghost lg:hidden" @click="showingNavigationDropdown = !showingNavigationDropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <ul tabindex="0" :class="{'hidden': !showingNavigationDropdown}" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li>
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Dashboard
                            </ResponsiveNavLink>
                        </li>
                    </ul>
                </div>

                <!-- Logo -->
                <Link :href="route('dashboard')" class="btn btn-ghost">
                    <ApplicationMark class="h-8 w-auto" />
                </Link>
            </div>

            <!-- Desktop Menu -->
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1">
                    <li>
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </NavLink>
                    </li>
                </ul>
            </div>

            <div class="navbar-end">
                <!-- Theme Switcher -->
                <ThemeSwitcher />

                <!-- Teams Dropdown -->
                <div v-if="$page.props.jetstream.hasTeamFeatures" class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost">
                        {{ $page.props.auth.user.current_team.name }}
                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        <li class="menu-title">Manage Team</li>
                        <li>
                            <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                Team Settings
                            </DropdownLink>
                        </li>
                        <li v-if="$page.props.jetstream.canCreateTeams">
                            <DropdownLink :href="route('teams.create')">
                                Create New Team
                            </DropdownLink>
                        </li>

                        <!-- Team Switcher -->
                        <template v-if="$page.props.auth.user.all_teams.length > 1">
                            <li class="menu-title">Switch Teams</li>
                            <li v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                <form @submit.prevent="switchToTeam(team)">
                                    <DropdownLink as="button">
                                        <div class="flex items-center">
                                            <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 size-5 text-success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div>{{ team.name }}</div>
                                        </div>
                                    </DropdownLink>
                                </form>
                            </li>
                        </template>
                    </ul>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                        <div v-if="$page.props.jetstream.managesProfilePhotos" class="w-10 rounded-full">
                            <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                        </div>
                        <span v-else class="text-xl">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </span>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li class="menu-title">Manage Account</li>
                        <li>
                            <DropdownLink :href="route('profile.show')">
                                Profile
                            </DropdownLink>
                        </li>
                        <li v-if="$page.props.jetstream.hasApiFeatures">
                            <DropdownLink :href="route('api-tokens.index')">
                                API Tokens
                            </DropdownLink>
                        </li>
                        <div class="divider my-0"></div>
                        <li>
                            <form @submit.prevent="logout">
                                <DropdownLink as="button">
                                    Log Out
                                </DropdownLink>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-base-100 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
