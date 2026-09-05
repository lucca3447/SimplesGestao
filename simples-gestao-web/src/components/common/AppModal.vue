<script setup>
import { ref, watch, onMounted } from 'vue';
import { X } from '@lucide/vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  maxWidth: {
    type: String,
    default: 'max-w-lg',
  },
});

const emit = defineEmits(['update:modelValue', 'close']);
const dialogRef = ref(null);

watch(() => props.modelValue, (isOpen) => {
  if (!dialogRef.value) return;
  if (isOpen) {
    if (!dialogRef.value.open) {
      dialogRef.value.showModal();
    }
  } else {
    if (dialogRef.value.open) {
      dialogRef.value.close();
    }
  }
});

function handleClose() {
  emit('update:modelValue', false);
  emit('close');
}

function handleDialogClose() {
  emit('update:modelValue', false);
  emit('close');
}

// Fallback de clique fora (light-dismiss) para navegadores que ainda não suportam closedby="any" nativo
function handleBackdropClick(event) {
  if (!dialogRef.value) return;
  if (event.target !== dialogRef.value) return;

  const rect = dialogRef.value.getBoundingClientRect();
  const isInside = (
    rect.top <= event.clientY &&
    event.clientY <= rect.top + rect.height &&
    rect.left <= event.clientX &&
    event.clientX <= rect.left + rect.width
  );

  if (!isInside) {
    handleClose();
  }
}

onMounted(() => {
  if (props.modelValue && dialogRef.value) {
    dialogRef.value.showModal();
  }
});
</script>

<template>
  <dialog
    ref="dialogRef"
    closedby="any"
    aria-labelledby="app-modal-title"
    class="rounded-2xl p-0 shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-sm border-0 m-auto fixed inset-0 w-full overflow-hidden transition-all text-gray-800"
    :class="maxWidth"
    @close="handleDialogClose"
    @click="handleBackdropClick"
  >
    <div class="bg-white flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h3 id="app-modal-title" class="text-base font-bold text-gray-900">{{ title }}</h3>
        <button
          type="button"
          aria-label="Fechar janela"
          class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
          @click="handleClose"
        >
          <X :size="18" />
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 py-4 overflow-y-auto flex-1">
        <slot />
      </div>

      <!-- Footer (se fornecido) -->
      <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
        <slot name="footer" />
      </div>
    </div>
  </dialog>
</template>
