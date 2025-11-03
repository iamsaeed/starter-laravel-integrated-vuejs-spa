<template>
  <div class="resource-manager">
    <!-- Page Header -->
    <div v-if="title || showBreadcrumbs" class="mb-6">
      <h1 v-if="title" class="text-3xl font-bold text-gray-900 dark:text-gray-100">
        {{ title }}
      </h1>
      <div v-if="showBreadcrumbs" class="mt-2">
        <!-- Breadcrumb slot for future implementation -->
        <slot name="breadcrumbs" />
      </div>
    </div>

    <!-- Resource Table -->
    <ResourceTable
      ref="tableRef"
      :resource="resource"
      :default-per-page="defaultPerPage"
      :enable-export="enableExport"
      @create="handleCreate"
      @edit="handleEdit"
      @view="handleView"
      @deleted="handleDeleted"
      @impersonate="handleImpersonate"
    />

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div
        v-if="showForm"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="closeForm"
      >
        <div class="modal-container">
          <!-- Background overlay -->
          <div class="modal-overlay" />

          <!-- Modal panel -->
          <div class="modal-panel" @click.stop>
            <ResourceForm
              :resource="resource"
              :item-id="editingId"
              @success="handleFormSuccess"
              @cancel="closeForm"
            />
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ResourceTable from './ResourceTable.vue'
import ResourceForm from './ResourceForm.vue'
import { impersonationService } from '@/services/impersonationService'
import { useToast } from '@/composables/useToast'
import { useAuthStore } from '@/stores/auth'
import { useDialog } from '@/composables/useDialog'

const toast = useToast()
const authStore = useAuthStore()
const { confirmWarning } = useDialog()

const props = defineProps({
  resource: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: null
  },
  showBreadcrumbs: {
    type: Boolean,
    default: false
  },
  defaultPerPage: {
    type: Number,
    default: 15
  },
  enableExport: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['created', 'updated', 'deleted'])

const route = useRoute()
const router = useRouter()

const showForm = ref(false)
const editingId = ref(null)
const tableRef = ref(null)
const isInitializing = ref(true)

// Initialize modal state from URL query params
function initializeFromQuery() {
  const query = route.query

  if (query.action === 'create') {
    editingId.value = null
    showForm.value = true
  } else if (query.action === 'edit' && query.id) {
    editingId.value = query.id
    showForm.value = true
  } else {
    showForm.value = false
    editingId.value = null
  }
}

// Update URL query params when modal state changes
function updateQueryParams() {
  if (isInitializing.value) return

  const query = { ...route.query }

  if (showForm.value) {
    if (editingId.value) {
      query.action = 'edit'
      query.id = editingId.value.toString()
    } else {
      query.action = 'create'
      delete query.id
    }
  } else {
    delete query.action
    delete query.id
  }

  // Only update if query actually changed
  if (JSON.stringify(query) !== JSON.stringify(route.query)) {
    router.replace({ query })
  }
}

// Watch modal state changes and update URL
watch([showForm, editingId], () => {
  updateQueryParams()
})

// Watch route query changes (e.g., browser back/forward or direct URL access)
watch(() => route.query, (newQuery, oldQuery) => {
  if (isInitializing.value) return

  // Only respond to action/id changes
  if (newQuery.action !== oldQuery?.action || newQuery.id !== oldQuery?.id) {
    initializeFromQuery()
  }
}, { deep: true })

function handleCreate() {
  editingId.value = null
  showForm.value = true
}

function handleEdit(item) {
  editingId.value = item.id
  showForm.value = true
}

function handleView(item) {
  // Could navigate to detail page or open detail modal
  // For now, open edit form
  handleEdit(item)
}

function handleFormSuccess(data) {
  showForm.value = false

  // Refresh the table data
  if (tableRef.value && tableRef.value.fetchData) {
    tableRef.value.fetchData()
  }

  // Emit appropriate event
  if (editingId.value) {
    emit('updated', data)
  } else {
    emit('created', data)
  }

  editingId.value = null
}

function handleDeleted(ids) {
  emit('deleted', ids)
}

function closeForm() {
  showForm.value = false
  editingId.value = null
}

async function handleImpersonate(user) {
  const confirmed = await confirmWarning(
    `Are you sure you want to impersonate <strong>${user.name}</strong>?<br><br>You will be logged in as this user and can see their account.`
  )

  if (!confirmed) {
    return
  }

  try {
    await impersonationService.impersonate(user.id)

    // Refresh user data
    await authStore.fetchUser()

    toast.success(`Successfully impersonating ${user.name}.`)

    // Redirect to user dashboard
    router.push({ name: 'user.dashboard' })
  } catch (error) {
    console.error('Failed to impersonate user:', error)
    toast.error(error.response?.data?.message || 'Failed to impersonate user.')
  }
}

// Lifecycle
onMounted(() => {
  initializeFromQuery()
  isInitializing.value = false
})
</script>
