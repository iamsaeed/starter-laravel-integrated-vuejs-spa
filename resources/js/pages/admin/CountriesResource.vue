<template>
  <div class="countries-resource-page">
    <ResourceManager
      resource="countries"
      :default-per-page="20"
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
  console.log('Country created:', data)
  // Show success notification
}

function handleUpdated(data) {
  console.log('Country updated:', data)
  // Show success notification
}

function handleDeleted(ids) {
  console.log('Countries deleted:', ids)
  // Show success notification
}
</script>
