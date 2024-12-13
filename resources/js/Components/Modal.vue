<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md', // xs, sm, md, lg, xl
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const modalRef = ref(null);
const showSlot = ref(props.show);

watch(() => props.show, () => {
    showSlot.value = props.show;
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.closeable) {
        e.preventDefault();
        if (props.show) {
            close();
        }
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const modalSizeClass = computed(() => {
    return {
        'xs': 'modal-xs',
        'sm': 'modal-sm',
        'md': '',  // default size
        'lg': 'modal-lg',
        'xl': 'modal-xl',
    }[props.size];
});
</script>

<template>
    <Transition
        leave-active-class="duration-200"
    >
        <div 
            v-show="show"
            class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
        >
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div 
                    v-show="show"
                    class="fixed inset-0 transform transition-all"
                    @click="close"
                >
                    <div class="absolute inset-0 bg-base-200 opacity-75" />
                </div>
            </Transition>

            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div 
                    v-show="show"
                    class="mb-6 bg-base-100 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto"
                    :class="{
                        'sm:max-w-sm': size === 'sm',
                        'sm:max-w-md': size === 'md',
                        'sm:max-w-lg': size === 'lg',
                        'sm:max-w-xl': size === 'xl',
                        'sm:max-w-2xl': size === '2xl',
                    }"
                >
                    <div class="relative">
                        <!-- Title -->
                        <div v-if="$slots.title" class="p-6 pb-0">
                            <h3 class="text-lg font-medium">
                                <slot name="title" />
                            </h3>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <slot name="content" />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="px-6 py-4 bg-base-200 text-right">
                            <slot name="footer" />
                        </div>
                        
                        <button 
                            v-if="closeable" 
                            class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            @click="close"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
.modal-box {
    @apply relative;
    max-height: calc(100vh - 5em);
    overflow-y: auto;
}

.modal {
    overflow-y: hidden;
}
</style>
