<template>
  <div class="roles-resource-page">
    <ResourceManager
      resource="roles"
      :default-per-page="15"
      :enable-export="true"
      @created="handleCreated"
      @updated="handleUpdated"
      @deleted="handleDeleted"
    />
  </div>
</template>

<script setup>
import ResourceManager from '@/components/resource/ResourceManager.vue'

function handleCreated(data) {
  console.log('Role created:', data)
  // Show success notification
}

function handleUpdated(data) {
  console.log('Role updated:', data)
  // Show success notification
}

function handleDeleted(ids) {
  console.log('Roles deleted:', ids)
  // Show success notification
}
</script>
