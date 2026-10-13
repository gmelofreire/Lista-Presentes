<template>
  <Transition leave-active-class="transition duration-150 ease-out" leave-from-class="opacity-100"
    leave-to-class="opacity-0 -translate-y-1">
  <div v-if="!isDismissed" :class="alertClasses" :role="type === 'danger' ? 'alert' : 'status'">
    <div class="flex">
      <div class="shrink-0">
        <component :is="iconComponent" :class="iconClasses" aria-hidden="true" />
      </div>
      <div class="ml-3 flex-1">
        <h3 :class="titleClasses">{{ title }}</h3>
        <div :class="messageClasses" v-if="message">
          <p>{{ message }}</p>
        </div>
        <div :class="messageClasses" v-if="$slots.default">
          <slot></slot>
        </div>
      </div>
      <div class="ml-auto pl-3" v-if="dismissible">
        <div class="-mx-1.5 -my-1.5">
          <button
            type="button"
            :class="closeButtonClasses"
            @click="dismiss"
            aria-label="Fechar alert"
          >
            <span class="sr-only">Fechar</span>
            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
          </button>
        </div>
      </div>
    </div>
  </div>
  </Transition>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { 
  ExclamationTriangleIcon, 
  CheckCircleIcon, 
  XCircleIcon, 
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/20/solid'

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'danger', 'alert', 'info'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    default: ''
  },
  dismissible: {
    type: Boolean,
    default: true
  },
  // Avisos de sucesso somem sozinhos; use 0 para manter na tela
  autoDismiss: {
    type: Number,
    default: 6000
  }
})

const emit = defineEmits(['dismiss'])

const isDismissed = ref(false)

const dismiss = () => {
  isDismissed.value = true
  emit('dismiss')
}

let timer = null
onMounted(() => {
  if (props.type === 'success' && props.autoDismiss > 0) {
    timer = setTimeout(dismiss, props.autoDismiss)
  }
})
onUnmounted(() => clearTimeout(timer))

const alertClasses = computed(() => {
  const baseClasses = 'rounded-xl border p-4'
  const typeClasses = {
    success: 'border-brand-200 bg-brand-50',
    danger: 'border-red-200 bg-red-50',
    alert: 'border-amber-200 bg-amber-50',
    info: 'border-gray-200 bg-surface'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})

const iconComponent = computed(() => {
  const icons = {
    success: CheckCircleIcon,
    danger: XCircleIcon,
    alert: ExclamationTriangleIcon,
    info: InformationCircleIcon
  }
  return icons[props.type]
})

const iconClasses = computed(() => {
  const baseClasses = 'size-5'
  const typeClasses = {
    success: 'text-brand-600',
    danger: 'text-red-400',
    alert: 'text-amber-500',
    info: 'text-gray-400'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})

const titleClasses = computed(() => {
  const baseClasses = 'text-sm font-medium'
  const typeClasses = {
    success: 'text-brand-900',
    danger: 'text-red-800',
    alert: 'text-amber-900',
    info: 'text-gray-900'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})

const messageClasses = computed(() => {
  const baseClasses = 'mt-2 text-sm'
  const typeClasses = {
    success: 'text-brand-800',
    danger: 'text-red-700',
    alert: 'text-amber-800',
    info: 'text-gray-600'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})

const closeButtonClasses = computed(() => {
  const baseClasses = 'inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors'
  const typeClasses = {
    success: 'bg-brand-50 text-brand-600 hover:bg-brand-100 focus:ring-brand-600 focus:ring-offset-brand-50',
    danger: 'bg-red-50 text-red-500 hover:bg-red-100 focus:ring-red-600 focus:ring-offset-red-50',
    alert: 'bg-amber-50 text-amber-600 hover:bg-amber-100 focus:ring-amber-600 focus:ring-offset-amber-50',
    info: 'bg-surface text-gray-500 hover:bg-gray-100 focus:ring-gray-500 focus:ring-offset-surface'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})
</script>