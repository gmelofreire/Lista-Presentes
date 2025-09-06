<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed } from 'vue';

const form = useForm({
    nome: '',
    descricao: '',
    visibilidade: '',
    data_evento: '',
    image_url: null
})

defineProps({
    title: {
        type: String,
    },
    listas: {
        type: Array,
    }
})

const hasErrorMessage = computed(() => Object.keys(form.errors).length > 0);
const imagePreview = ref(null);

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) {
            console.log('Arquivo original:', (file.size / 1024 / 1024).toFixed(2), 'MB');

            let processedFile = file;

            // Comprimir se for maior que 5MB
            if (file.size > 5 * 1024 * 1024) {
                console.log('Comprimindo imagem...');
                processedFile = await compressImage(file, 5, 0.8);
                console.log('Arquivo comprimido:', (processedFile.size / 1024 / 1024).toFixed(2), 'MB');
            }

            form.image_url = processedFile;

            // Criar URL para preview
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

const submit = () => {
    form.post(route('listas.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Lista salva com sucesso!');
        },
        onError: (errors) => {
            console.log('Erro ao salvar lista:', errors);
        }
    });
};

const compressImage = (file, maxSizeMB = 5, quality = 0.8) => {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = () => {
            // Calcular novo tamanho mantendo proporção
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

            // Desenhar imagem redimensionada
            ctx.drawImage(img, 0, 0, width, height);

            // Converter para blob com qualidade especificada
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
                            <h2 class="text-2xl font-bold text-gray-900">Nova Lista de Presente</h2>
                        </div>
                        <div class="mt-6">
                            <form>
                                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <div class="col-span-full">
                                        <InputLabel for="image_url" value="Foto do Presente" />

                                        <!-- Área de Upload -->
                                        <div v-if="!imagePreview"
                                            class=" mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg cursor-pointer border-gray-300 hover:border-indigo-500 hover:bg-indigo-50 transition"
                                            @click="$refs.fileInput.click()">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor"
                                                    fill="none" viewBox="0 0 48 48">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M28 8H12a4 4 0 00-4 4v24a4 4 0 004 4h24a4 4 0 004-4V20l-12-12z" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600 justify-center">
                                                    <span
                                                        class="relative font-medium text-indigo-600 hover:text-indigo-500">
                                                        Clique para enviar
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500">PNG, JPG até 5MB</p>
                                            </div>
                                        </div>

                                        <input type="file" ref="fileInput" id="image_url" name="image_url"
                                            accept="image/*" class="hidden" @change="handleFileChange" />

                                        <div v-if="imagePreview" class="mt-4 relative">
                                            <img :src="imagePreview" alt="Preview da imagem"
                                                class="w-full h-56 object-cover rounded-lg border shadow-sm" />
                                            <button type="button" @click="removeImage"
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                                ×
                                            </button>
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.image_url" />
                                    </div>
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
                                        <div class="flex justify-between">
                                            <Link :href="route('listas.index')">
                                            <button
                                                class="border bg-white text-black px-4 py-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                Cancelar
                                            </button>
                                            </Link>
                                            <button @click="submit" type="button"
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