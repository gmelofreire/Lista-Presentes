<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
    },
    lista: {
        type: [Object, Array],
    }
})

const form = useForm({
    nome: props.lista.nome || '',
    descricao: props.lista.descricao || '',
    visibilidade: props.lista.visibilidade || '',
    data_evento: props.lista.data_evento || '',
    image_url: props.lista.image_url || null,
})

const submit = () => {
    form.post(route('listas.update', props.lista.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Lista editada com sucesso!');
        },
        onError: (errors) => {
            console.log('Erro ao editar lista:', errors);
        }
    });
};

const hasErrorMessage = computed(() => Object.keys(form.errors).length > 0);
const imagePreview = ref(props.lista.image_url || null);

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) {
            console.log('Arquivo original:', (file.size / 1024 / 1024).toFixed(2), 'MB');

            let processedFile = file;

            if (file.size > 5 * 1024 * 1024) {
                console.log('Comprimindo imagem...');
                processedFile = await compressImage(file, 5, 0.8);
                console.log('Arquivo comprimido:', (processedFile.size / 1024 / 1024).toFixed(2), 'MB');
            }

            form.image_url = processedFile;

            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.value = e.target.result;
            };
            reader.readAsDataURL(processedFile);
        } else {
            alert('Por favor, selecione apenas arquivos de imagem.');
            event.target.value = '';
        }
    }
};

const removeImage = () => {
    imagePreview.value = null;
    form.image_url = null;
    document.getElementById('image_url').value = '';
};

const compressImage = (file, maxSizeMB = 5, quality = 0.8) => {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = () => {
            let { width, height } = img;
            const maxDimension = 1200; // pixels

            if (width > height && width > maxDimension) {
                height = (height * maxDimension) / width;
                width = maxDimension;
            } else if (height > maxDimension) {
                width = (width * maxDimension) / height;
                height = maxDimension;
            }

            canvas.width = width;
            canvas.height = height;

            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob(resolve, 'image/jpeg', quality);
        };

        img.src = URL.createObjectURL(file);
    });
};
</script>

<template>

    <Head :title="title" />
    <AppLayout :title="title">
        <div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <Alert v-if="hasErrorMessage" type="danger" title="Erro ao salvar perfil"
                            message="Por favor, corrija os erros abaixo e tente novamente." class="mb-6" />
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Editar Lista de Presente</h2>
                        </div>
                        <div class="mt-6">
                            <form @submit.prevent="submit">
                                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <div class="sm:col-span-1">
                                        <InputLabel for="nome" value="Nome da Lista" :required="true" />
                                        <TextInput id="nome" type="text" class="mt-1 block w-full" v-model="form.nome"
                                            required autofocus autocomplete="nome" />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>
                                    <div class="col-span-full">
                                        <InputLabel for="descricao" value="Descrição" />
                                        <textarea id="descricao" name="descricao" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            v-model="form.descricao"
                                            placeholder="Descreva sua lista de presentes..."></textarea>
                                        <InputError class="mt-2" :message="form.errors.descricao" />
                                    </div>
                                    <div class="sm:col-span-1">
                                        <InputLabel for="visibilidade" value="Visibilidade" :required="true" />
                                        <select id="visibilidade" name="visibilidade"
                                            class="rounded-md mt-1 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full"
                                            v-model="form.visibilidade">
                                            <option value="">Selecione</option>
                                            <option value="publica">Pública</option>
                                            <option value="privada">Privada</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.visibilidade" />
                                    </div>
                                    <div class="sm:col-span-1">
                                        <InputLabel for="data_evento" value="Data do Evento"
                                            hint="Caso seja para um evento" />
                                        <TextInput id="data_evento" type="date" class="mt-1 block w-full"
                                            v-model="form.data_evento" autofocus autocomplete="data_evento" />
                                        <InputError class="mt-2" :message="form.errors.data_evento" />
                                    </div>
                                    <div class="col-span-full">
                                        <InputLabel for="image_url" value="Foto de capa" />
                                        <div v-if="imagePreview" class="mt-4">
                                            <div class="relative">
                                                <img :src="imagePreview" alt="Preview da imagem de capa"
                                                    class="w-full h-56 object-cover rounded-lg border-2 border-gray-200 shadow-sm">
                                                <button type="button" @click="removeImage"
                                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                    ×
                                                </button>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-2">Preview da imagem selecionada</p>
                                        </div>
                                        <div class="mt-2">
                                            <input type="file" name="image_url" id="image_url" accept="image/*"
                                                @change="handleFileChange"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.image_url" />
                                    </div>
                                    <div class="col-span-full">
                                        <div class="flex justify-end">
                                            <button type="submit"
                                                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                Salvar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>