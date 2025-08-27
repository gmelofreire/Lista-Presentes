<template>
  <div v-if="!isDismissed" :class="alertClasses">
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
</template>

<script setup>
import { computed, ref } from 'vue'
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
  }
})

const emit = defineEmits(['dismiss'])

const isDismissed = ref(false)

const dismiss = () => {
  isDismissed.value = true
  emit('dismiss')
}

const alertClasses = computed(() => {
  const baseClasses = 'rounded-md p-4'
  const typeClasses = {
    success: 'bg-green-50',
    danger: 'bg-red-50',
    alert: 'bg-yellow-50',
    info: 'bg-blue-50'
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
    success: 'text-green-400',
    danger: 'text-red-400',
    alert: 'text-yellow-400',
    info: 'text-blue-400'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})

const titleClasses = computed(() => {
  const baseClasses = 'text-sm font-medium'
  const typeClasses = {
    success: 'text-green-800',
    danger: 'text-red-800',
    alert: 'text-yellow-800',
    info: 'text-blue-800'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})

const messageClasses = computed(() => {
  const baseClasses = 'mt-2 text-sm'
  const typeClasses = {
    success: 'text-green-700',
    danger: 'text-red-700',
    alert: 'text-yellow-700',
    info: 'text-blue-700'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})

const closeButtonClasses = computed(() => {
  const baseClasses = 'inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors'
  const typeClasses = {
    success: 'bg-green-50 text-green-500 hover:bg-green-100 focus:ring-green-600 focus:ring-offset-green-50',
    danger: 'bg-red-50 text-red-500 hover:bg-red-100 focus:ring-red-600 focus:ring-offset-red-50',
    alert: 'bg-yellow-50 text-yellow-500 hover:bg-yellow-100 focus:ring-yellow-600 focus:ring-offset-yellow-50',
    info: 'bg-blue-50 text-blue-500 hover:bg-blue-100 focus:ring-blue-600 focus:ring-offset-blue-50'
  }
  return `${baseClasses} ${typeClasses[props.type]}`
})
</script>