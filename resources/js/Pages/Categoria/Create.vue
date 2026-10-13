<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed, watch } from 'vue';
import { CheckIcon } from '@heroicons/vue/20/solid';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

// Sugestoes: saturacao moderada, boas com texto claro ou escuro
const paleta = ['#1A7F56', '#2563EB', '#7C3AED', '#DB2777', '#DC2626', '#EA580C', '#CA8A04', '#0891B2', '#52525B', '#18181B'];

const props = defineProps({
    title: {
        type: String,
    }
})

const form = useForm({
    nome: '',
    descricao: '',
    hex_cor: '#3B82F6',
})

const recentColors = ref(JSON.parse(localStorage.getItem('recentColors') || '[]'));

const hasErrorMessage = computed(() => {
    return Object.keys(form.errors).length > 0;
});

const isDarkColor = (hexColor) => {
    if (!hexColor || hexColor.length < 6) return false;
    const hex = hexColor.replace('#', '');

    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);

    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance < 0.5;
};

const isValidHex = (hex) => {
    return /^#[0-9A-Fa-f]{6}$/.test(hex);
};

const addToRecentColors = (color) => {
    if (!isValidHex(color)) return;

    const recent = recentColors.value.filter(c => c !== color);
    recent.unshift(color);
    recentColors.value = recent.slice(0, 10); // Manter apenas 10 cores recentes

    localStorage.setItem('recentColors', JSON.stringify(recentColors.value));
};

const generateRandomColor = () => {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    form.hex_cor = color;
    addToRecentColors(color);
};

watch(() => form.hex_cor, (newColor) => {
    if (isValidHex(newColor)) {
        addToRecentColors(newColor);
    }
});

const submit = () => {
    form.post(route('categorias.store'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Categoria salva com sucesso!');
        },
        onError: (errors) => {
            console.log('Erro ao salvar categoria:', errors);
        }
    });
};
</script>

<template>

    <Head :title="title" />
    <AppLayout :title="title">
        <div class="mx-auto max-w-3xl">
            <Alert v-if="hasErrorMessage" type="danger" title="Não foi possível salvar"
                message="Corrija os campos indicados e tente novamente." class="mb-6" />

            <div class="mb-8">
                <h2 class="text-3xl font-semibold text-gray-900">Nova categoria</h2>
                <p class="mt-1 text-gray-600">Escolha um nome e uma cor. A cor aparece nos presentes dessa categoria.</p>
            </div>

            <div class="surface p-5 sm:p-8">
                <form class="space-y-8" @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <InputLabel for="nome" value="Nome da categoria" :required="true" />
                            <TextInput id="nome" type="text" class="mt-1.5 block w-full" v-model="form.nome" required
                                autofocus autocomplete="off" />
                            <InputError class="mt-2" :message="form.errors.nome" />
                        </div>

                        <div>
                            <InputLabel for="descricao" value="Descrição" />
                            <textarea id="descricao" name="descricao" rows="3" class="mt-1.5 block w-full"
                                v-model="form.descricao" placeholder="Para que tipo de presente serve"></textarea>
                            <InputError class="mt-2" :message="form.errors.descricao" />
                        </div>

                        <!-- Cor -->
                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-800">
                                Cor <span class="text-red-500">*</span>
                            </legend>

                            <div class="mt-2 flex flex-wrap gap-2" role="radiogroup" aria-label="Cores sugeridas">
                                <button v-for="cor in paleta" :key="cor" type="button" role="radio"
                                    :aria-checked="form.hex_cor.toUpperCase() === cor" :aria-label="`Cor ${cor}`"
                                    class="press flex size-9 items-center justify-center rounded-full ring-offset-2"
                                    :class="form.hex_cor.toUpperCase() === cor ? 'ring-2 ring-gray-900' : 'ring-1 ring-black/10 hover:ring-2 hover:ring-gray-300'"
                                    :style="{ backgroundColor: cor }" @click="form.hex_cor = cor">
                                    <CheckIcon v-if="form.hex_cor.toUpperCase() === cor" class="size-4"
                                        :class="isDarkColor(cor) ? 'text-white' : 'text-gray-900'" aria-hidden="true" />
                                </button>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <input id="hex_cor" type="color" v-model="form.hex_cor" aria-label="Escolher outra cor"
                                    class="size-10 cursor-pointer rounded-md border border-gray-300 bg-surface p-1" />
                                <input type="text" v-model="form.hex_cor" maxlength="7" placeholder="#000000"
                                    aria-label="Código hexadecimal da cor"
                                    class="w-32 font-mono text-sm uppercase"
                                    @input="$event.target.value = $event.target.value.toUpperCase()" />
                                <button type="button" class="btn-ghost" @click="generateRandomColor">
                                    <ArrowPathIcon class="size-4" aria-hidden="true" />
                                    Sortear
                                </button>
                            </div>

                            <p v-if="form.hex_cor && !isValidHex(form.hex_cor)" class="mt-2 text-sm text-red-600" role="alert">
                                Formato inválido. Use #RRGGBB, por exemplo #FF5733.
                            </p>
                            <InputError class="mt-2" :message="form.errors.hex_cor" />

                            <!-- Previa como aparece no presente -->
                            <div class="mt-5 rounded-xl bg-gray-50 p-4">
                                <p class="text-xs font-medium text-gray-500">Como vai aparecer</p>
                                <span v-if="isValidHex(form.hex_cor)"
                                    class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-sm font-medium transition-colors duration-200"
                                    :style="{ backgroundColor: form.hex_cor, color: isDarkColor(form.hex_cor) ? '#ffffff' : '#18181b' }">
                                    {{ form.nome || 'Nome da categoria' }}
                                </span>
                            </div>
                        </fieldset>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-6">
                        <Link :href="route('categorias.index')" class="btn-secondary">Cancelar</Link>
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            {{ form.processing ? 'Salvando...' : 'Criar categoria' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
