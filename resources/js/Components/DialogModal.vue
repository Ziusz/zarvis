<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :size="size"
        :closeable="closeable"
        @close="close"
    >
        <div class="modal-header">
            <h3 class="font-bold text-lg">
                <slot name="title" />
            </h3>
        </div>

        <div class="modal-body py-4">
            <slot name="content" />
        </div>

        <div class="modal-action">
            <slot name="footer" />
        </div>
    </Modal>
</template>

<style scoped>
.modal-header {
    @apply mb-4 pr-6;
}
.modal-body {
    @apply text-sm;
}
.modal-action {
    @apply mt-6;
}
</style>
