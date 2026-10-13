<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { MoonIcon, SunIcon } from '@heroicons/vue/24/outline';

// Tema escolhido pelo usuario fica em localStorage ('claro' | 'escuro').
// Sem escolha, segue o sistema. O <html> ja recebe a classe antes da pintura (app.blade.php).
const escuro = ref(false);
let consulta = null;

const ler = () => {
    try {
        return localStorage.getItem('tema');
    } catch (e) {
        return null;
    }
};

const aplicar = (valor) => {
    escuro.value = valor;
    document.documentElement.classList.toggle('dark', valor);
};

const aoMudarSistema = (evento) => {
    if (!ler()) aplicar(evento.matches);
};

const alternar = () => {
    const proximo = !escuro.value;
    aplicar(proximo);
    try {
        localStorage.setItem('tema', proximo ? 'escuro' : 'claro');
    } catch (e) {
        // sem armazenamento: vale so nesta visita
    }
};

onMounted(() => {
    escuro.value = document.documentElement.classList.contains('dark');
    consulta = window.matchMedia('(prefers-color-scheme: dark)');
    consulta.addEventListener('change', aoMudarSistema);
});

onUnmounted(() => consulta?.removeEventListener('change', aoMudarSistema));
</script>

<template>
    <button type="button" class="press rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
        :aria-label="escuro ? 'Mudar para o tema claro' : 'Mudar para o tema escuro'" :aria-pressed="escuro"
        @click="alternar">
        <SunIcon v-if="escuro" class="size-6" aria-hidden="true" />
        <MoonIcon v-else class="size-6" aria-hidden="true" />
    </button>
</template>
