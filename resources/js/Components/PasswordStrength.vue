<script setup>
import { computed } from 'vue';

const props = defineProps({
    password: {
        type: String,
        default: ''
    }
});

const strength = computed(() => {
    const p = props.password;
    if (!p) return 0;

    let score = 0;

    if (p.length >= 8) score += 1;
    if (p.length >= 12) score += 1;
    if (/[a-z]/.test(p)) score += 1;
    if (/[A-Z]/.test(p)) score += 1;
    if (/[0-9]/.test(p)) score += 1;
    if (/[^a-zA-Z0-9]/.test(p)) score += 1;

    return score;
});

const strengthLevel = computed(() => {
    if (strength.value <= 2) return 'fraca';
    if (strength.value <= 4) return 'média';
    return 'forte';
});

const strengthColor = computed(() => {
    if (strength.value <= 2) return 'bg-red-500';
    if (strength.value <= 4) return 'bg-yellow-500';
    return 'bg-green-500';
});

const strengthPercentage = computed(() => {
    return Math.min((strength.value / 6) * 100, 100);
});
</script>

<template>
    <div v-if="password" class="mt-2">
        <div class="flex items-center gap-2 mb-1">
            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div 
                    class="h-full transition-all duration-300"
                    :class="strengthColor"
                    :style="{ width: strengthPercentage + '%' }"
                ></div>
            </div>
            <span class="text-sm font-medium" :class="{
                'text-red-600': strengthLevel === 'fraca',
                'text-yellow-600': strengthLevel === 'média',
                'text-green-600': strengthLevel === 'forte'
            }">
                {{ strengthLevel === 'fraca' ? 'Fraca' : strengthLevel === 'média' ? 'Média' : 'Forte' }}
            </span>
        </div>
        <p class="text-xs text-gray-500">
            Mínimo: 8 caracteres com letras maiúsculas, minúsculas, números e símbolos
        </p>
    </div>
</template>