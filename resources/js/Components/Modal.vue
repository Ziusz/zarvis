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
    if (props.show) {
        document.body.style.overflow = 'hidden';
        showSlot.value = true;
    } else {
        document.body.style.overflow = null;
        showSlot.value = false;
    }
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

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});

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
    <div>
        <input 
            type="checkbox" 
            :checked="show" 
            @change="close" 
            class="modal-toggle" 
        />
        
        <div 
            class="modal" 
            :class="{ 'modal-open': show }"
            ref="modalRef"
            role="dialog"
            aria-modal="true"
        >
            <div class="modal-box" :class="modalSizeClass">
                <slot v-if="showSlot" />
                
                <button 
                    v-if="closeable" 
                    class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                    @click="close"
                >
                    ✕
                </button>
            </div>
            
            <label 
                v-if="closeable"
                class="modal-backdrop" 
                @click="close"
            />
        </div>
    </div>
</template>

<style scoped>
.modal-box {
    @apply relative;
    max-height: calc(100vh - 5em);
}
</style>
