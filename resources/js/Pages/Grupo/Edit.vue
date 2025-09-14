<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    title: {
        type: String,
    },
    grupo: {
        type: Object,
        required: true
    },
    amizades: {
        type: [Array, Object],
        default: () => []
    }
})

const form = useForm({
    nome: props.grupo.nome || '',
    descricao: props.grupo.descricao || '',
    image_url: props.grupo.image_url || '',
    integrante_ids: props.grupo.integrantes ? props.grupo.integrantes.map(i => i.id) : []
})

// Filtrar amizades baseado no termo de busca
const filteredAmizades = computed(() => {
    if (!searchTerm.value) {
        return props.amizades;
    }
    return props.amizades.filter(amizade => {
        const nome = amizade.nome || amizade.name || '';
        const email = amizade.email || '';
        return nome.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
               email.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
});

const hasErrorMessage = computed(() => Object.keys(form.errors).length > 0);
const imagePreview = ref(props.grupo.image_url || null);
const searchTerm = ref('');

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
    form.post(route('grupos.update', props.grupo.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Grupo atualizado com sucesso!');
        },
        onError: (errors) => {
            console.log('Erro ao atualizar grupo:', errors);
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

const toggleIntegrante = (integranteId) => {
    const index = form.integrante_ids.indexOf(integranteId);
    if (index > -1) {
        form.integrante_ids.splice(index, 1);
    } else {
        form.integrante_ids.push(integranteId);
    }
};

const isIntegranteSelected = (integranteId) => {
    return form.integrante_ids.includes(integranteId);
};

const getInitials = (nome) => {
    if (!nome) return '?';
    const words = nome.split(' ');
    if (words.length >= 2) {
        return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
    }
    return nome.charAt(0).toUpperCase();
};

// Fechar dropdown ao clicar fora
const handleClickOutside = (event) => {
    const dropdown = document.querySelector('.integrante-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        showIntegrantes.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Head :title="title" />
    <AppLayout :title="title">
        <div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <Alert v-if="hasErrorMessage" type="danger" title="Erro ao atualizar grupo"
                            message="Por favor, corrija os erros abaixo e tente novamente." class="mb-6" />
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Editar Grupo</h2>
                        </div>
                        <div class="mt-6">
                            <form>
                                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <div class="col-span-full">
                                        <InputLabel for="image_url" value="Foto do grupo" />

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
                                        <InputLabel for="nome" value="Nome do Grupo" :required="true" />
                                        <TextInput id="nome" type="text" class="mt-1 block w-full" v-model="form.nome"
                                            required autofocus autocomplete="nome" />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>
                                    <div class="col-span-full">
                                        <InputLabel for="descricao" value="Descrição" />
                                        <textarea id="descricao" name="descricao" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            v-model="form.descricao" placeholder="Descreva seu grupo..."></textarea>
                                        <InputError class="mt-2" :message="form.errors.descricao" />
                                    </div>

                                    <div class="col-span-full">
                                        <InputLabel for="integrantes" value="Integrantes" />
                                        <div class="mt-1">
                                            <!-- Barra de busca -->
                                            <div class="mb-3">
                                                <TextInput
                                                    v-model="searchTerm"
                                                    type="text"
                                                    placeholder="Buscar amigos..."
                                                    class="w-full"
                                                />
                                            </div>
                                            
                                            <div class="bg-white border border-gray-300 rounded-md shadow-sm max-h-60 overflow-auto">
                                                <div v-if="!props.amizades || props.amizades.length === 0"
                                                    class="px-3 py-4 text-gray-500 text-sm text-center">
                                                    Nenhum amigo disponível para adicionar ao grupo
                                                </div>
                                                <div v-else-if="filteredAmizades.length === 0"
                                                    class="px-3 py-4 text-gray-500 text-sm text-center">
                                                    Nenhum amigo encontrado com "{{ searchTerm }}"
                                                </div>
                                                <div v-else class="py-2">
                                                    <div v-for="amizade in filteredAmizades" :key="amizade.id"
                                                    @click="toggleIntegrante(amizade.id)"
                                                    class="flex items-center space-x-3 px-3 py-2 hover:bg-gray-50 cursor-pointer transition-colors duration-150"
                                                    :class="{ 'bg-indigo-50': isIntegranteSelected(amizade.id) }">
                                                        <div class="flex-shrink-0">
                                                            <div class="w-4 h-4 border-2 rounded flex items-center justify-center transition-all duration-150"
                                                                :class="isIntegranteSelected(amizade.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300'">
                                                                <svg v-if="isIntegranteSelected(amizade.id)"
                                                                    class="w-3 h-3 text-white" fill="currentColor"
                                                                    viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                        clip-rule="evenodd"></path>
                                                                </svg>
                                                            </div>
                                                        </div>

                                                        <!-- Avatar do integrante -->
                                                        <div v-if="amizade.perfil?.image_url"
                                                            class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-semibold">
                                                            <img :src="amizade.perfil.image_url" alt="Avatar"
                                                                class="w-full h-full rounded-full">
                                                        </div>
                                                        <div v-else
                                                            class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-semibold">
                                                            {{ getInitials(amizade.nome || amizade.name) }}
                                                        </div>

                                                        <!-- Nome do integrante -->
                                                        <div class="flex-1">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ amizade.nome || amizade.name }}
                                                            </div>
                                                            <div v-if="amizade.email"
                                                                class="text-xs text-gray-500 truncate">
                                                                {{ amizade.username }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-if="form.integrante_ids.length > 0" class="mt-2 text-sm text-gray-600">
                                                {{ form.integrante_ids.length - 1 }} integrante(s) selecionado(s)
                                            </div>
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.integrante_ids" />
                                    </div>
                                    <div class="col-span-full">
                                        <div class="flex justify-between">
                                            <Link :href="route('grupos.show', grupo.id)">
                                            <button
                                                class="border bg-white text-black px-4 py-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                Cancelar
                                            </button>
                                            </Link>
                                            <button @click="submit" type="button"
                                                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                Atualizar
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