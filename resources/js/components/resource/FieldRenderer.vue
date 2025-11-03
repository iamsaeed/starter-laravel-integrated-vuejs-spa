<template>
  <transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0 -translate-y-1"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-1"
  >
    <div v-show="isFieldVisible" class="form-group">
    <label
      :for="field.attribute"
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
    >
      {{ field.label }}
      <span v-if="isFieldRequired" class="text-red-500">*</span>
      <span v-if="field.type === 'password' && itemId && !field.requiredOnUpdate" class="text-sm text-gray-500 dark:text-gray-400 font-normal ml-1">(optional)</span>
    </label>

    <!-- Text Input -->
    <input
      v-if="field.type === 'text' || field.type === 'email'"
      :id="field.attribute"
      v-model="modelValue[field.attribute]"
      :type="field.type"
      :placeholder="field.meta?.placeholder"
      :required="isFieldRequired"
      :disabled="isFieldDisabled"
      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
      :class="{ 'border-red-500': errors[field.attribute] }"
    />

    <!-- Password Input -->
    <input
      v-else-if="field.type === 'password'"
      :id="field.attribute"
      v-model="modelValue[field.attribute]"
      type="password"
      :placeholder="fieldPlaceholder"
      :required="isFieldRequired"
      :disabled="isFieldDisabled"
      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
      :class="{ 'border-red-500': errors[field.attribute] }"
    />

    <!-- Number Input -->
    <input
      v-else-if="field.type === 'number'"
      :id="field.attribute"
      v-model.number="modelValue[field.attribute]"
      type="number"
      :placeholder="field.meta?.placeholder"
      :required="isFieldRequired"
      :disabled="isFieldDisabled"
      :min="field.meta?.min"
      :max="field.meta?.max"
      :step="field.meta?.step"
      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
      :class="{ 'border-red-500': errors[field.attribute] }"
    />

    <!-- Textarea -->
    <textarea
      v-else-if="field.type === 'textarea'"
      :id="field.attribute"
      v-model="modelValue[field.attribute]"
      :placeholder="field.meta?.placeholder"
      :required="isFieldRequired"
      :disabled="isFieldDisabled"
      :rows="field.meta?.rows || 3"
      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
      :class="{ 'border-red-500': errors[field.attribute] }"
    />

    <!-- JSON Editor -->
    <div v-else-if="field.type === 'json'">
      <JsonEditor
        v-model="modelValue[field.attribute]"
        :placeholder="field.meta?.placeholder"
        :help-text="field.meta?.helpText"
        :error="errors[field.attribute]?.[0]"
        :rows="field.meta?.rows || 6"
        :required="isFieldRequired"
        :disabled="isFieldDisabled"
        :expected-type="field.meta?.expectedType"
        :show-preview="field.meta?.showPreview || false"
        :auto-format="field.meta?.autoFormat !== false"
        :show-format-button="field.meta?.showFormatButton !== false"
        :show-validate-button="field.meta?.showValidateButton !== false"
        :show-validation-icon="field.meta?.showValidationIcon !== false"
      />
    </div>

    <!-- Select (Server-side) -->
    <div v-else-if="field.type === 'select' && field.meta?.serverSide && field.meta?.resource">
      <ServerSelectInput
        v-model="modelValue[field.attribute]"
        :resource-key="field.meta.resource"
        :title-attribute="field.meta.titleAttribute || 'name'"
        :placeholder="field.meta?.placeholder || 'Search ' + field.label"
        :multiple="field.meta?.multiple || false"
        :max-selections="field.meta?.maxSelections || null"
        :disabled="isFieldDisabled"
      />
      <p v-if="errors[field.attribute]" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ errors[field.attribute][0] }}
      </p>
      <p v-if="field.meta?.helpText" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ field.meta.helpText }}
      </p>
    </div>

    <!-- Select (Client-side) -->
    <div v-else-if="field.type === 'select'">
      <ResourceSelectInput
        v-model="modelValue[field.attribute]"
        :options="selectOptions"
        :placeholder="field.meta?.placeholder || 'Select ' + field.label"
        :searchable="field.meta?.searchable || false"
        :multiple="field.meta?.multiple || false"
        :max-selections="field.meta?.maxSelections || null"
        :disabled="isFieldDisabled"
      />
      <p v-if="errors[field.attribute]" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ errors[field.attribute][0] }}
      </p>
      <p v-if="field.meta?.helpText" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ field.meta.helpText }}
      </p>
    </div>

    <!-- Boolean (Checkbox) -->
    <div v-else-if="field.type === 'boolean'" class="flex items-center">
      <input
        :id="field.attribute"
        v-model="modelValue[field.attribute]"
        type="checkbox"
        :disabled="isFieldDisabled"
        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
      />
      <label :for="field.attribute" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
        {{ field.meta?.trueLabel || 'Yes' }}
      </label>
    </div>

    <!-- Date -->
    <input
      v-else-if="field.type === 'date'"
      :id="field.attribute"
      v-model="modelValue[field.attribute]"
      type="date"
      :required="isFieldRequired"
      :disabled="isFieldDisabled"
      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
      :class="{ 'border-red-500': errors[field.attribute] }"
    />

    <!-- BelongsTo (Single select with Quick Create) -->
    <div v-else-if="field.type === 'belongsTo' || field.type === 'belongs-to'" class="flex gap-2">
      <select
        :id="field.attribute"
        v-model="modelValue[field.attribute]"
        :required="isFieldRequired"
        :disabled="isFieldDisabled"
        class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
        :class="{ 'border-red-500': errors[field.attribute] }"
      >
        <option value="">Select {{ field.label }}</option>
        <option
          v-for="option in relationOptions[field.attribute]"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>

      <!-- Quick Create Button -->
      <button
        v-if="field.creatable"
        type="button"
        @click="$emit('quick-create', field)"
        :disabled="isFieldDisabled"
        class="px-3 py-2 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 text-primary-700 dark:text-primary-300 border border-primary-300 dark:border-primary-700 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        v-tooltip="'Create new ' + field.label"
      >
        <Icon name="add" :size="20" />
      </button>
    </div>

    <!-- BelongsToMany (Multi-select with Quick Create) -->
    <div v-else-if="field.type === 'belongsToMany' || field.type === 'belongs-to-many'" class="flex gap-2">
      <select
        :id="field.attribute"
        v-model="modelValue[field.attribute]"
        multiple
        :disabled="isFieldDisabled"
        class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
        :class="{ 'border-red-500': errors[field.attribute] }"
      >
        <option
          v-for="option in relationOptions[field.attribute]"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>

      <!-- Quick Create Button -->
      <button
        v-if="field.creatable"
        type="button"
        @click="$emit('quick-create', field)"
        :disabled="isFieldDisabled"
        class="px-3 py-2 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 text-primary-700 dark:text-primary-300 border border-primary-300 dark:border-primary-700 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        v-tooltip="'Create new ' + field.label"
      >
        <Icon name="add" :size="20" />
      </button>
    </div>

    <!-- Media Upload (only show when editing) -->
    <div v-else-if="field.type === 'media'">
      <MediaUpload
        v-if="itemId"
        v-model="modelValue[field.attribute]"
        :label="field.label"
        :help-text="field.meta?.helpText"
        :required="field.required"
        :multiple="field.meta?.multiple"
        :collection="field.meta?.collection || 'default'"
        :accepted-types="field.meta?.acceptedTypes || ['image/*']"
        :max-file-size="field.meta?.maxFileSize"
        :rounded="field.meta?.rounded"
        :preview-width="field.meta?.previewWidth || 128"
        :preview-height="field.meta?.previewHeight || 128"
        :model-type="meta.model"
        :model-id="itemId"
        :editable="field.meta?.editable || false"
        :editor-options="field.meta?.editorOptions || {}"
      />
      <div v-else class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <p class="text-sm text-blue-700 dark:text-blue-300">
          {{ field.label }} can be uploaded after creating the record. Save this form first, then edit to add {{ field.label.toLowerCase() }}.
        </p>
      </div>
    </div>

    <!-- Error Message -->
    <p v-if="errors[field.attribute]" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ errors[field.attribute][0] }}
    </p>

    <!-- Help Text -->
    <p v-if="field.meta?.helpText" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ field.meta.helpText }}
    </p>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue'
import MediaUpload from '@/components/form/MediaUpload.vue'
import Icon from '@/components/common/Icon.vue'
import ResourceSelectInput from '@/components/form/ResourceSelectInput.vue'
import ServerSelectInput from '@/components/form/ServerSelectInput.vue'
import JsonEditor from '@/components/form/JsonEditor.vue'

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  modelValue: {
    type: Object,
    required: true
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  relationOptions: {
    type: Object,
    default: () => ({})
  },
  itemId: {
    type: [Number, String],
    default: null
  },
  meta: {
    type: Object,
    default: () => ({})
  }
})

defineEmits(['quick-create'])

const isFieldVisible = computed(() => {
  const field = props.field

  // Check dependsOn condition
  if (field.meta?.dependsOn) {
    return evaluateDependsOn(field.meta.dependsOn)
  }

  // Check showWhen structured condition
  if (field.meta?.showWhen) {
    // Skip if it's a callback (frontend: false)
    if (field.meta.showWhen.frontend === false) {
      return true // Backend-only callbacks always visible on frontend
    }

    return evaluateStructuredCondition(field.meta.showWhen)
  }

  // Default: visible
  return true
})

const evaluateDependsOn = (dependsOn) => {
  const { attribute, value, operator = '=' } = dependsOn
  const actualValue = props.modelValue[attribute]

  return evaluateCondition(actualValue, value, operator)
}

const evaluateStructuredCondition = (condition) => {
  if (condition.type === 'all') {
    // AND logic
    return condition.conditions.every(cond => {
      const actualValue = props.modelValue[cond.attribute]
      return evaluateCondition(actualValue, cond.value, cond.operator || '=')
    })
  } else if (condition.type === 'any') {
    // OR logic
    return condition.conditions.some(cond => {
      const actualValue = props.modelValue[cond.attribute]
      return evaluateCondition(actualValue, cond.value, cond.operator || '=')
    })
  }

  return true
}

const evaluateCondition = (actualValue, expectedValue, operator) => {
  switch (operator) {
    case '=':
      return actualValue == expectedValue
    case '!=':
      return actualValue != expectedValue
    case '>':
      return actualValue > expectedValue
    case '>=':
      return actualValue >= expectedValue
    case '<':
      return actualValue < expectedValue
    case '<=':
      return actualValue <= expectedValue
    case 'in':
      return Array.isArray(expectedValue) && expectedValue.includes(actualValue)
    case 'not_in':
      return Array.isArray(expectedValue) && !expectedValue.includes(actualValue)
    case 'contains':
      return Array.isArray(actualValue) && actualValue.includes(expectedValue)
    case 'not_contains':
      return Array.isArray(actualValue) && !actualValue.includes(expectedValue)
    case 'empty':
      return !actualValue || actualValue === '' || (Array.isArray(actualValue) && actualValue.length === 0)
    case 'not_empty':
      return actualValue && actualValue !== '' && (!Array.isArray(actualValue) || actualValue.length > 0)
    default:
      return false
  }
}

const isFieldRequired = computed(() => {
  const field = props.field

  // Check conditional required (requiredWhen)
  if (field.meta?.requiredWhen) {
    const { attribute, value, operator = '=' } = field.meta.requiredWhen
    const actualValue = props.modelValue[attribute]
    const conditionallyRequired = evaluateCondition(actualValue, value, operator)
    if (conditionallyRequired) {
      return true
    }
  }

  // For password fields, check context-specific required flags
  if (field.type === 'password') {
    if (props.itemId) {
      return field.requiredOnUpdate ?? false
    } else {
      return field.requiredOnCreate ?? field.required ?? false
    }
  }

  // For other fields, use the standard required flag
  return field.required ?? false
})

const isFieldDisabled = computed(() => {
  const field = props.field

  // Check if readonly on update
  if (props.itemId && field.meta?.readonlyOnUpdate) {
    return true
  }

  // Check if always readonly
  if (field.meta?.readonly) {
    return true
  }

  // Check conditional disabled (disabledWhen)
  if (field.meta?.disabledWhen) {
    const { attribute, value, operator = '=' } = field.meta.disabledWhen
    const actualValue = props.modelValue[attribute]
    return evaluateCondition(actualValue, value, operator)
  }

  // Default: not disabled
  return false
})

const fieldPlaceholder = computed(() => {
  const field = props.field

  // For password fields, check context-specific placeholders
  if (field.type === 'password') {
    if (props.itemId && field.meta?.updatePlaceholder) {
      return field.meta.updatePlaceholder
    } else if (!props.itemId && field.meta?.creationPlaceholder) {
      return field.meta.creationPlaceholder
    }
  }

  // Fall back to standard placeholder
  return field.meta?.placeholder || ''
})

const selectOptions = computed(() => {
  const field = props.field

  // If this is a select field with a resource (relationship)
  if (field.meta?.resource && props.relationOptions[field.attribute]) {
    return props.relationOptions[field.attribute]
  }

  // If this is a regular select with static options
  if (field.meta?.options) {
    return Object.entries(field.meta.options).map(([value, label]) => ({
      value,
      label
    }))
  }

  return []
})
</script>
