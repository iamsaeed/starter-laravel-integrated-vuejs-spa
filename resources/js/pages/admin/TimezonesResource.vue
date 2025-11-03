<template>
  <div class="timezones-resource-page">
    <ResourceManager
      resource="timezones"
      :default-per-page="20"
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
  console.log('Timezone created:', data)
  // Show success notification
}

function handleUpdated(data) {
  console.log('Timezone updated:', data)
  // Show success notification
}

function handleDeleted(ids) {
  console.log('Timezones deleted:', ids)
  // Show success notification
}
</script>
