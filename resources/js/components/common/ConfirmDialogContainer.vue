<template>
  <Teleport to="body">
    <div class="confirm-dialog-container">
      <ConfirmDialog
        v-for="dialog in dialogs"
        :key="dialog.id"
        v-bind="dialog"
      />
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useDialogStore } from '@/stores/dialog'
import ConfirmDialog from './ConfirmDialog.vue'

const store = useDialogStore()

const dialogs = computed(() => store.dialogs)
</script>
x