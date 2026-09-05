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

onMounted(() => {
  if (props.modelValue && dialogRef.value) {
    dialogRef.value.showModal();
  }
});
</script>

<template>
  <dialog
    ref="dialogRef"
    class="rounded-2xl p-0 shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-sm border-0 m-auto fixed inset-0 w-full overflow-hidden transition-all text-gray-800"
    :class="maxWidth"
    @close="handleDialogClose"
  >
    <div class="bg-white flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
        <button
          type="button"
          class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
          @click="handleClose"
        >
          <X :size="20" />
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
