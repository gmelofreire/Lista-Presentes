<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { CheckIcon } from '@heroicons/vue/20/solid';


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
        <div class="mx-auto max-w-3xl">
            <Alert v-if="hasErrorMessage" type="danger" title="Não foi possível salvar"
                message="Corrija os campos indicados e tente novamente." class="mb-6" />

            <div class="mb-8">
                <h2 class="text-3xl font-semibold text-gray-900">Editar grupo</h2>
                <p class="mt-1 text-gray-600">Atualize imagem, descrição e quem participa.</p>
            </div>

            <div class="surface p-5 sm:p-8">
                <form class="space-y-8" @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-5">
                        <!-- Imagem -->
                        <div>
                            <InputLabel for="image_url" value="Imagem do grupo" />
                            <button v-if="!imagePreview" type="button"
                                class="press mt-2 flex w-full flex-col items-center rounded-xl border-2 border-dashed border-gray-300 px-6 py-10 hover:border-brand-500 hover:bg-brand-50/50"
                                @click="$refs.fileInput.click()">
                                <PhotoIcon class="size-10 text-gray-400" aria-hidden="true" />
                                <span class="mt-2 text-sm font-medium text-brand-700">Escolher imagem</span>
                                <span class="mt-1 text-xs text-gray-500">PNG ou JPG, até 5MB</span>
                            </button>
                            <input type="file" ref="fileInput" id="image_url" name="image_url" accept="image/*"
                                class="hidden" @change="handleFileChange" />
                            <div v-if="imagePreview" class="relative mt-2 overflow-hidden rounded-xl">
                                <img :src="imagePreview" alt="Pré-visualização da imagem" class="aspect-[16/7] w-full object-cover" />
                                <button type="button" @click="removeImage"
                                    class="press absolute right-3 top-3 flex size-8 items-center justify-center rounded-full bg-surface/90 text-gray-700 shadow-sm backdrop-blur hover:bg-surface">
                                    <XMarkIcon class="size-4" aria-hidden="true" />
                                    <span class="sr-only">Remover imagem</span>
                                </button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.image_url" />
                        </div>

                        <div>
                            <InputLabel for="nome" value="Nome do grupo" :required="true" />
                            <TextInput id="nome" type="text" class="mt-1.5 block w-full" v-model="form.nome" required
                                autofocus autocomplete="off" />
                            <InputError class="mt-2" :message="form.errors.nome" />
                        </div>

                        <div>
                            <InputLabel for="descricao" value="Descrição" />
                            <textarea id="descricao" name="descricao" rows="3" class="mt-1.5 block w-full"
                                v-model="form.descricao" placeholder="Para que serve o grupo e quem participa"></textarea>
                            <InputError class="mt-2" :message="form.errors.descricao" />
                        </div>

                        <!-- Integrantes -->
                        <div>
                            <InputLabel for="integrantes" value="Integrantes" />
                            <input id="integrantes" v-model="searchTerm" type="search" placeholder="Buscar amigos"
                                class="mt-1.5 block w-full" />

                            <div class="mt-3 max-h-64 overflow-auto rounded-xl border border-gray-200">
                                <p v-if="!props.amizades || props.amizades.length === 0"
                                    class="px-4 py-8 text-center text-sm text-gray-500">
                                    Você ainda não tem amigos para adicionar. Convide pessoas depois pela página do grupo.
                                </p>
                                <p v-else-if="filteredAmizades.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                                    Nenhum amigo encontrado com "{{ searchTerm }}"
                                </p>
                                <ul v-else class="divide-y divide-gray-100">
                                    <li v-for="amizade in filteredAmizades" :key="amizade.id">
                                        <button type="button" @click="toggleIntegrante(amizade.id)"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors duration-150 hover:bg-gray-50"
                                            :class="{ 'bg-brand-50': isIntegranteSelected(amizade.id) }">
                                            <span class="flex size-5 shrink-0 items-center justify-center rounded border-2 transition-colors duration-150"
                                                :class="isIntegranteSelected(amizade.id) ? 'border-brand-600 bg-brand-600' : 'border-gray-300'">
                                                <CheckIcon v-if="isIntegranteSelected(amizade.id)" class="size-3.5 text-white" aria-hidden="true" />
                                            </span>
                                            <img v-if="amizade.perfil?.image_url" :src="amizade.perfil.image_url" alt=""
                                                class="size-9 shrink-0 rounded-full object-cover" />
                                            <span v-else
                                                class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-800">
                                                {{ getInitials(amizade.nome || amizade.name) }}
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-medium text-gray-900">
                                                    {{ amizade.nome || amizade.name }}
                                                </span>
                                                <span v-if="amizade.username" class="block truncate text-xs text-gray-500">
                                                    @{{ amizade.username }}
                                                </span>
                                            </span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <p v-if="form.integrante_ids.length > 1" class="mt-2 text-sm text-gray-600">
                                {{ form.integrante_ids.length - 1 }} {{ (form.integrante_ids.length - 1) === 1 ? 'integrante selecionado' : 'integrantes selecionados' }}
                            </p>
                            <InputError class="mt-2" :message="form.errors.integrante_ids" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-6">
                        <Link :href="route('grupos.show', grupo.id)" class="btn-secondary">Cancelar</Link>
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
