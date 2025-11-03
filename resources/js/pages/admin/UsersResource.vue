<template>
  <div class="users-resource-page">
    <ResourceManager
      resource="users"
      :default-per-page="15"
      :enable-export="true"
      @created="handleCreated"
      @updated="handleUpdated"
      @deleted="handleDeleted"
    />
  </div>
</template>

<script setup>
import ResourceManager from '@/core/components/resource/ResourceManager.vue'

function handleCreated(data) {
  console.log('User created:', data)
  // Show success notification
}

function handleUpdated(data) {
  console.log('User updated:', data)
  // Show success notification
}

function handleDeleted(ids) {
  console.log('Users deleted:', ids)
  // Show success notification
}
</script>
