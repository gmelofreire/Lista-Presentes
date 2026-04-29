<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { StarIcon, InformationCircleIcon, TrashIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    mode: {
        type: String,
        default: 'create' // 'create' or 'edit'
    },
    title: {
        type: String,
    },
    presente: {
        type: Object,
        default: () => null
    },
    lista_id: {
        type: String,
    },
    categorias: {
        type: [Array, Object],
    }
});

const isEdit = computed(() => props.mode === 'edit');

const form = useForm({
    nome: props.presente?.nome || '',
    descricao: props.presente?.descricao || '',
    preco: props.presente?.preco || '',
    link: props.presente?.link || '',
    image_url: props.presente?.image_url || '',
    anotacoes: props.presente?.anotacoes || '',
    lista_id: props.lista_id || (props.presente?.lista_id || ''),
    avaliacao: props.presente?.avaliacao || 0,
    categoria_ids: props.presente?.categoria_ids || [],
});

const intensidadesDesejo = [
    'Quase não quero',
    'Seria legal ter',
    'Gostaria bastante',
    'Quero muito',
    'Preciso desse presente',
];

const imagePreview = ref(props.presente?.image_url || null);
const hoverRating = ref(0);
const showCategorias = ref(false);
const loadingUrl = ref(false);
const urlErro = ref('');
const showUrlWarning = ref(false);
const showDeleteModal = ref(false);

const hasErrorMessage = computed(() => {
    return Object.keys(form.errors).length > 0;
});

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) {
            let processedFile = file;

            if (file.size > 5 * 1024 * 1024) {
                processedFile = await compressImage(file, 5, 0.8);
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

const compressImage = (file, maxSizeMB = 5, quality = 0.8) => {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = () => {
            let { width, height } = img;
            const maxDimension = 1200;

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

const removeImage = () => {
    imagePreview.value = null;
    form.image_url = '';
    document.getElementById('image_url').value = '';
};

const buscarDadosUrl = async () => {
    if (!form.link || loadingUrl.value) return;
    
    showUrlWarning.value = true;
};

const confirmUrlFetch = async () => {
    showUrlWarning.value = false;
    
    if (!form.link || loadingUrl.value) return;
    
    try {
        new URL(form.link);
    } catch (e) {
        urlErro.value = 'URL inválida';
        return;
    }
    
    urlErro.value = '';
    loadingUrl.value = true;
    
    try {
        const response = await axios.post(route('presentes.buscar-dados-url'), { url: form.link });
        
        const data = response.data;
        
        if (!data.error) {
            if (data.nome && !form.nome) {
                form.nome = data.nome;
            }
            if (data.descricao && !form.descricao) {
                form.descricao = data.descricao;
            }
            if (data.imagem && !form.image_url && !imagePreview.value) {
                imagePreview.value = data.imagem;
            }
            if (data.preco && !form.preco) {
                form.preco = data.preco;
            }
        } else {
            urlErro.value = data.error || 'Erro ao buscar dados';
        }
    } catch (error) {
        console.error('Erro ao buscar dados da URL:', error);
    } finally {
        loadingUrl.value = false;
    }
};

const handleLinkInput = () => {
    urlErro.value = '';
};

const submit = () => {
    let useFormData = false;
    
    if (imagePreview.value && !imagePreview.value.startsWith('data:')) {
        form.image_url = imagePreview.value;
    } else if (form.image_url instanceof File || (form.image_url && form.image_url.startsWith('data:'))) {
        useFormData = true;
    }
    
    const routeName = isEdit.value ? 'presentes.update' : 'presentes.store';
    const routeParams = isEdit.value ? props.presente.id : form.lista_id;
    
    form.post(route(routeName, routeParams), {
        forceFormData: useFormData,
        preserveScroll: true,
    });
};

const getStarColor = (starNumber) => {
    const activeRating = hoverRating.value || form.avaliacao;
    return starNumber <= activeRating ? 'text-yellow-400' : 'text-gray-300';
};

const toggleCategoria = (categoriaId) => {
    const index = form.categoria_ids.indexOf(categoriaId);
    if (index > -1) {
        form.categoria_ids.splice(index, 1);
    } else {
        form.categoria_ids.push(categoriaId);
    }
};

const isCategoriaSelected = (categoriaId) => {
    return form.categoria_ids.includes(categoriaId);
};

const isDarkColor = (hexColor) => {
    if (!hexColor || hexColor.length < 6) return false;
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance < 0.5;
};

const handleClickOutside = (event) => {
    const dropdown = document.querySelector('.categoria-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        showCategorias.value = false;
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
    <div>
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <Alert v-if="hasErrorMessage" type="danger" title="Erro ao salvar"
                        message="Por favor, corrija os erros abaixo e tente novamente." class="mb-6" />
                    
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ isEdit ? 'Editar Presente' : 'Novo Presente' }}
                        </h2>
                        <button v-if="isEdit" @click="showDeleteModal = true"
                            class="text-white bg-red-600 hover:bg-red-800 font-medium px-4 py-2 rounded-md flex items-center gap-2">
                            <TrashIcon class="w-5 h-5" />
                            apagar
                        </button>
                    </div>
                    
                    <form>
                        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="col-span-full">
                                <InputLabel for="image_url" value="Foto do Presente" />
                                <div v-if="!imagePreview"
                                    class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg cursor-pointer border-gray-300 hover:border-indigo-500 hover:bg-indigo-50 transition"
                                    @click="$refs.fileInput.click()">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor"
                                            fill="none" viewBox="0 0 48 48">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M28 8H12a4 4 0 00-4 4v24a4 4 0 004 4h24a4 4 0 004-4V20l-12-12z" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <span class="relative font-medium text-indigo-600 hover:text-indigo-500">
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
                                        class="w-full h-56 object-contain rounded-lg border shadow-sm" />
                                    <button type="button" @click="removeImage"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                        ×
                                    </button>
                                </div>
                                <InputError class="mt-2" :message="form.errors.image_url" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel for="nome" value="Nome do Presente" :required="true" />
                                <TextInput id="nome" type="text" class="mt-1 block w-full" v-model="form.nome"
                                    required autofocus autocomplete="nome" />
                                <InputError class="mt-2" :message="form.errors.nome" />
                            </div>
                            
                            <div class="sm:col-span-1">
                                <InputLabel for="preco" value="Preço" />
                                <TextInput id="preco" type="number" class="mt-1 block w-full"
                                    v-model="form.preco" autofocus autocomplete="preco" />
                                <InputError class="mt-2" :message="form.errors.preco" />
                            </div>
                            
                            <div class="col-span-full">
                                <InputLabel for="descricao" value="Descrição" />
                                <textarea id="descricao" name="descricao" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.descricao"
                                    placeholder="Descreva sua lista de presentes..."></textarea>
                                <InputError class="mt-2" :message="form.errors.descricao" />
                            </div>
                            
                            <div class="col-span-full">
                                <InputLabel for="link" value="Link" />
                                <div class="flex gap-2 mt-1">
                                    <TextInput id="link" type="text" class="flex-1" v-model="form.link"
                                        autofocus autocomplete="link" @blur="handleLinkInput" @keyup.enter="buscarDadosUrl" />
                                    <button type="button" @click="buscarDadosUrl"
                                        :disabled="!form.link || loadingUrl"
                                        class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap text-sm">
                                        <span v-if="loadingUrl">Buscando...</span>
                                        <span v-else>Buscar Dados</span>
                                    </button>
                                </div>
                                <p v-if="urlErro" class="mt-1 text-sm text-red-600">{{ urlErro }}</p>
                                <InputError class="mt-2" :message="form.errors.link" />
                            </div>
                            
                            <div class="col-span-full">
                                <InputLabel for="categoria_ids" value="Categorias" />
                                <div class="mt-2 relative categoria-dropdown">
                                    <button type="button" @click="showCategorias = !showCategorias"
                                        class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 text-left shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2 flex-wrap">
                                                <span v-if="form.categoria_ids.length === 0"
                                                    class="text-gray-500">
                                                    Selecione as categorias
                                                </span>
                                                <div v-else class="flex items-center space-x-1 flex-wrap">
                                                    <div v-for="categoriaId in form.categoria_ids"
                                                        :key="categoriaId"
                                                        class="inline-flex items-center space-x-1 bg-gray-100 rounded-full px-2 py-1 text-xs">
                                                        <div class="w-3 h-3 rounded-full border border-white shadow-sm"
                                                            :style="{ backgroundColor: categorias.find(c => c.id === categoriaId)?.hex_cor || '#6B7280' }">
                                                        </div>
                                                        <span class="text-gray-700">
                                                            {{categorias.find(c => c.id === categoriaId)?.nome}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                                :class="{ 'rotate-180': showCategorias }" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </button>
                                    <div v-show="showCategorias"
                                        class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                        @click.stop>
                                        <div v-if="!categorias || categorias.length === 0"
                                            class="px-3 py-2 text-gray-500 text-sm">
                                            Nenhuma categoria disponível
                                        </div>
                                        <div v-else class="py-1">
                                            <div v-for="categoria in categorias" :key="categoria.id"
                                                @click="toggleCategoria(categoria.id)"
                                                class="flex items-center space-x-3 px-3 py-2 hover:bg-gray-50 cursor-pointer transition-colors duration-150"
                                                :class="{ 'bg-indigo-50': isCategoriaSelected(categoria.id) }">
                                                <div class="flex-shrink-0">
                                                    <div class="w-4 h-4 border-2 rounded flex items-center justify-center transition-all duration-150"
                                                        :class="isCategoriaSelected(categoria.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300'">
                                                        <svg v-if="isCategoriaSelected(categoria.id)"
                                                            class="w-3 h-3 text-white" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm flex items-center justify-center"
                                                    :style="{ backgroundColor: categoria.hex_cor || '#6B7280' }">
                                                    <span class="text-xs font-semibold"
                                                        :class="isDarkColor(categoria.hex_cor) ? 'text-white' : 'text-gray-800'">
                                                        {{ categoria.nome?.charAt(0)?.toUpperCase() }}
                                                    </span>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ categoria.nome }}
                                                    </div>
                                                    <div v-if="categoria.descricao"
                                                        class="text-xs text-gray-500 truncate">
                                                        {{ categoria.descricao }}
                                                    </div>
                                                </div>
                                                <div class="text-xs text-gray-400 font-mono">
                                                    {{ categoria.hex_cor }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.categoria_ids" />
                            </div>
                            
                            <div class="col-span-full">
                                <InputLabel for="anotacoes" value="Anotações" />
                                <textarea id="anotacoes" name="anotacoes" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.anotacoes"
                                    placeholder="Adicione anotações sobre o presente..."></textarea>
                                <InputError class="mt-2" :message="form.errors.anotacoes" />
                            </div>
                            
                            <div class="col-span-full">
                                <InputLabel for="anotacoes" value="Avaliação" />
                                <div class="flex justify-center">
                                    <span v-for="n in 5" :key="n" class="cursor-pointer"
                                        @click="form.avaliacao = n" @mouseenter="hoverRating = n"
                                        @mouseleave="hoverRating = 0">
                                        <StarIcon :class="[
                                            'size-7 transition-colors duration-200',
                                            getStarColor(n)
                                        ]" />
                                    </span>
                                </div>
                                <div class="text-center mt-2">
                                    {{ intensidadesDesejo[(hoverRating || form.avaliacao) - 1] || 'Selecione uma avaliação' }}
                                </div>
                                <InputError class="mt-2" :message="form.errors.anotacoes" />
                            </div>
                            
                            <div class="col-span-full">
                                <div class="flex justify-between">
                                    <slot name="cancel">
                                        <Link :href="isEdit ? route('listas.show', presente.lista_id) : route('listas.show', lista_id)">
                                            <button type="button"
                                                class="border bg-white text-black px-4 py-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                Cancelar
                                            </button>
                                        </Link>
                                    </slot>
                                    <button @click="submit" type="button"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        {{ isEdit ? 'Atualizar' : 'Salvar' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <TransitionRoot as="template" :show="showUrlWarning">
        <Dialog class="relative z-10" @close="showUrlWarning = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>
            </TransitionChild>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6">
                            <div>
                                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-blue-100">
                                    <InformationCircleIcon class="size-6 text-blue-600" />
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <DialogTitle as="h3" class="text-base font-semibold text-gray-900">Atenção</DialogTitle>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">Nem sempre todas as informações (como preço e descrição) são preenchidas automaticamente, dependendo do site de origem. Você pode precisar preencher alguns campos manualmente.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-6 flex gap-2">
                                <button type="button" class="inline-flex w-full justify-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs hover:bg-gray-300" @click="showUrlWarning = false">Cancelar</button>
                                <button type="button" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500" @click="confirmUrlFetch">Continuar</button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <TransitionRoot as="template" :show="showDeleteModal">
        <Dialog class="relative z-10" @close="showDeleteModal = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>
            </TransitionChild>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6">
                            <div>
                                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-100">
                                    <TrashIcon class="size-6 text-red-600" />
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <DialogTitle as="h3" class="text-base font-semibold text-gray-900">Confirmar exclusão</DialogTitle>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">Tem certeza que deseja excluir este presente? Esta ação não pode ser desfeita.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-6 flex gap-2">
                                <button type="button" class="inline-flex w-full justify-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs hover:bg-gray-300" @click="showDeleteModal = false">Cancelar</button>
                                <Link :href="route('presentes.destroy', presente.id)" method="delete" as="button" type="button"
                                    class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500">
                                    Excluir
                                </Link>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>