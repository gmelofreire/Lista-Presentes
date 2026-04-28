<script setup>
import { ref, watch, onUnmounted } from 'vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const props = defineProps({
    show: Boolean,
    imageSrc: String,
});

const emit = defineEmits(['close', 'crop']);

const cropper = ref(null);
const imageRef = ref(null);

watch(() => props.show, (newVal) => {
    if (newVal && props.imageSrc) {
        setTimeout(() => {
            if (cropper.value) {
                cropper.value.destroy();
            }
            cropper.value = new Cropper(imageRef.value, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        }, 100);
    }
});

onUnmounted(() => {
    if (cropper.value) {
        cropper.value.destroy();
        cropper.value = null;
    }
});

const handleCrop = () => {
    if (cropper.value) {
        const canvas = cropper.value.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        canvas.toBlob((blob) => {
            emit('crop', blob);
        }, 'image/jpeg', 0.9);
    }
};

const handleClose = () => {
    if (cropper.value) {
        cropper.value.destroy();
        cropper.value = null;
    }
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="handleClose"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Cortar Imagem
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-3">
                                    Arraste e redimensione para escolher a área da imagem.
                                </p>
                                <div class="max-h-96 overflow-hidden">
                                    <img ref="imageRef" :src="imageSrc" class="max-w-full" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" @click="handleCrop">
                        Confirmar
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="handleClose">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.cropper-view-box,
.cropper-face {
    border-radius: 50%;
}

.cropper-view-box {
    box-shadow: 0 0 0 1px #39f;
    outline: none;
}
</style>