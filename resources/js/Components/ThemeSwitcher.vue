<script setup>
import { ref, onMounted } from 'vue';

const currentTheme = ref('light');

const themes = [
    {
        name: 'light',
        icon: 'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z'
    },
    {
        name: 'dark',
        icon: 'M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z'
    },
    {
        name: 'system',
        icon: 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25'
    }
];

const setTheme = (theme) => {
    // If theme is system, check system preferences
    if (theme === 'system') {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-theme', 'night');
            localStorage.setItem('theme', 'system');
            currentTheme.value = 'system';
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'system');
            currentTheme.value = 'system';
        }
    } else {
        // Set theme directly if light or dark
        document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'night' : 'light');
        localStorage.setItem('theme', theme);
        currentTheme.value = theme;
    }
};

// Watch for system theme changes
const watchSystemTheme = () => {
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (currentTheme.value === 'system') {
                setTheme('system');
            }
        });
    }
};

onMounted(() => {
    // Get saved theme or use system theme
    const savedTheme = localStorage.getItem('theme') || 'system';
    setTheme(savedTheme);
    watchSystemTheme();
});
</script>

<template>
    <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle">
            <!-- Light icon -->
            <svg v-if="currentTheme === 'light'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path :d="themes.find(t => t.name === 'light').icon" />
            </svg>
            <!-- Dark icon -->
            <svg v-else-if="currentTheme === 'dark'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path :d="themes.find(t => t.name === 'dark').icon" />
            </svg>
            <!-- System icon -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path :d="themes.find(t => t.name === 'system').icon" />
            </svg>
        </label>
        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
            <li class="menu-title">Choose Theme</li>
            <li v-for="theme in themes" :key="theme.name">
                <button 
                    @click="setTheme(theme.name)"
                    :class="{ 'active': currentTheme === theme.name }"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path :d="theme.icon" />
                    </svg>
                    {{ theme.name.charAt(0).toUpperCase() + theme.name.slice(1) }}
                </button>
            </li>
        </ul>
    </div>
</template> 