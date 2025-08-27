<template>
    <input
        :id="id"
        ref="input"
        :value="displayValue"
        :class="inputClass"
        type="text"
        :placeholder="placeholder"
        @input="handleInput"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
        maxlength="15"
    />
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    id: {
        type: String,
        required: false,
    },
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '(11) 99999-9999',
    },
    class: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const input = ref();

// Classe CSS padrão do TextInput
const inputClass = computed(() => {
    return `border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm ${props.class}`;
});

// Remove todos os caracteres não numéricos
const cleanPhone = (value) => {
    return value.replace(/\D/g, '');
};

// Aplica a máscara de telefone brasileiro
const applyMask = (value) => {
    const cleaned = cleanPhone(value);
    
    if (cleaned.length <= 10) {
        // Telefone fixo: (11) 1234-5678
        return cleaned
            .replace(/^(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        // Celular: (11) 99999-9999
        return cleaned
            .replace(/^(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d)/, '$1-$2');
    }
};

// Valor formatado para exibição
const displayValue = computed(() => {
    return applyMask(props.modelValue || '');
});

// Manipula a entrada do usuário
const handleInput = (event) => {
    const value = event.target.value;
    const cleaned = cleanPhone(value);
    
    // Limita a 11 dígitos (DDD + 9 dígitos)
    if (cleaned.length <= 11) {
        emit('update:modelValue', cleaned);
    }
};

// Foca no input quando necessário
const focus = () => input.value.focus();

// Expõe métodos para o componente pai
defineExpose({ focus });

// Observa mudanças no modelValue para garantir consistência
watch(() => props.modelValue, (newValue) => {
    if (newValue && cleanPhone(newValue) !== newValue) {
        emit('update:modelValue', cleanPhone(newValue));
    }
});
</script>