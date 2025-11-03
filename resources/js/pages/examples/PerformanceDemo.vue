<template>
  <div class="max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
      Performance Optimizations Demo
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Lazy Loading Demo -->
      <div class="space-y-6">
        <div class="card p-6">
          <h2 class="text-xl font-semibold mb-4">Lazy Loading Validation Rules</h2>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Validation rules are loaded on-demand to reduce initial bundle size.
          </p>
          
          <button
            @click="loadEmailValidation"
            :disabled="isLoadingRules"
            class="form-button-primary mb-4"
          >
            <span v-if="isLoadingRules">Loading Rules...</span>
            <span v-else>Load Email Validation Rules</span>
          </button>
          
          <div v-if="loadedRules.size > 0" class="text-sm text-green-600 dark:text-green-400">
            Loaded rules: {{ Array.from(loadedRules).join(', ') }}
          </div>
        </div>

        <!-- Debounced Validation Demo -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold mb-4">Debounced Validation</h2>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Validation is debounced to reduce CPU usage during typing.
          </p>
          
          <FormInput
            name="debouncedEmail"
            placeholder="Type to see debounced validation..."
            @input="handleDebouncedInput"
          />
          
          <div class="mt-2 text-sm">
            <div class="text-gray-500">Validation calls: {{ validationCallCount }}</div>
            <div v-if="isValidatingDebounced" class="text-blue-600">Validating...</div>
            <div v-if="debouncedResult" class="text-green-600">✓ Valid email</div>
          </div>
        </div>

        <!-- Memoization Demo -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold mb-4">Memoized Computations</h2>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Expensive computations are cached to improve performance.
          </p>
          
          <button
            @click="testMemoization"
            class="form-button-primary mb-4"
          >
            Test Memoized Function
          </button>
          
          <div class="text-sm space-y-1">
            <div>Function calls: {{ memoCallCount }}</div>
            <div>Cache hits: {{ memoCacheHits }}</div>
            <div>Cache size: {{ memoizedFunction.size() }}</div>
            <div>Last result: {{ lastMemoResult }}</div>
          </div>
        </div>
      </div>

      <!-- Virtual Scrolling Demo -->
      <div class="space-y-6">
        <div class="card p-6">
          <h2 class="text-xl font-semibold mb-4">Virtual Scrolling Select</h2>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Handles {{ largeOptionList.length }} options efficiently using virtual scrolling.
          </p>
          
          <VirtualSelectInput
            name="virtualSelect"
            :options="largeOptionList"
            placeholder="Search through 10,000+ options..."
            multiple
          />
        </div>

        <!-- File Preview Optimization -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold mb-4">Optimized File Previews</h2>
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            File previews are generated with caching and resizing for better performance.
          </p>
          
          <FileInput
            name="optimizedFiles"
            accept="image/*"
            multiple
            :max-size="10485760"
            help-text="Upload multiple images to see optimized preview generation"
          />
        </div>

        <!-- Performance Metrics -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold mb-4">Performance Metrics</h2>
          
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                <div class="font-medium">Validation Count</div>
                <div class="text-lg">{{ performanceMetrics.validationCount }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                <div class="font-medium">Avg Time (ms)</div>
                <div class="text-lg">{{ performanceMetrics.averageValidationTime.toFixed(2) }}</div>
              </div>
            </div>
            
            <div v-if="performanceMetrics.slowValidations.length > 0">
              <div class="font-medium text-sm mb-2">Slow Validations (>100ms):</div>
              <div class="space-y-1">
                <div
                  v-for="slow in performanceMetrics.slowValidations.slice(-3)"
                  :key="slow.timestamp"
                  class="text-xs text-red-600 dark:text-red-400"
                >
                  {{ slow.fieldName }}: {{ slow.duration.toFixed(2) }}ms
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Component Rendering Optimizations Info -->
    <div class="card p-6 mt-8">
      <h2 class="text-xl font-semibold mb-4">Component Rendering Optimizations</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
        <div>
          <h3 class="font-medium mb-2">Key Optimizations</h3>
          <ul class="space-y-1 text-gray-600 dark:text-gray-300">
            <li>• Using shallowRef for arrays</li>
            <li>• Proper key usage in v-for</li>
            <li>• v-show instead of v-if for toggles</li>
            <li>• Memoized computed properties</li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-medium mb-2">Lazy Loading</h3>
          <ul class="space-y-1 text-gray-600 dark:text-gray-300">
            <li>• Validation rules loaded on demand</li>
            <li>• Component code splitting</li>
            <li>• Dynamic imports for features</li>
            <li>• Reduced initial bundle size</li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-medium mb-2">Debouncing & Throttling</h3>
          <ul class="space-y-1 text-gray-600 dark:text-gray-300">
            <li>• Input validation debounced</li>
            <li>• Search queries throttled</li>
            <li>• Async operations optimized</li>
            <li>• CPU usage reduced</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Performance Tips -->
    <div class="card p-6 mt-8">
      <h2 class="text-xl font-semibold mb-4">Performance Tips</h2>
      
      <div class="prose dark:prose-invert max-w-none">
        <h3>Form Performance Best Practices:</h3>
        <ul>
          <li><strong>Use Virtual Scrolling:</strong> For select components with >100 options</li>
          <li><strong>Debounce Validation:</strong> Avoid validating on every keystroke</li>
          <li><strong>Memoize Expensive Operations:</strong> Cache computed values and API results</li>
          <li><strong>Lazy Load Rules:</strong> Only load validation rules when needed</li>
          <li><strong>Optimize File Handling:</strong> Resize images and cache previews</li>
          <li><strong>Use Proper Keys:</strong> Ensure efficient list updates with unique keys</li>
          <li><strong>Prefer v-show:</strong> For frequently toggled elements</li>
          <li><strong>ShallowRef for Arrays:</strong> Better performance for large lists</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FormInput from '../components/form/FormInput.vue'
import VirtualSelectInput from '../components/form/VirtualSelectInput.vue'
import FileInput from '../components/form/FileInput.vue'

// Lazy loading utilities
import { useValidationRules, preloadCommonRules } from '../utils/lazyValidation'
import { useDebouncedValidation, usePerformanceMonitor } from '../utils/debouncedValidation'
import { memoize } from '../utils/memoization'

// State
const isLoadingRules = ref(false)
const validationCallCount = ref(0)
const isValidatingDebounced = ref(false)
const debouncedResult = ref(false)
const memoCallCount = ref(0)
const memoCacheHits = ref(0)
const lastMemoResult = ref(null)

// Composables
const { loadedRules, ensureRulesLoaded } = useValidationRules()
const debouncedValidation = useDebouncedValidation()
const { performanceMetrics, measureValidation } = usePerformanceMonitor()

// Memoized function demo
const expensiveFunction = memoize((input) => {
  memoCallCount.value++
  // Simulate expensive computation
  const result = input.split('').reverse().join('').toUpperCase()
  return result
}, { maxCacheSize: 10 })

// Override the memoized function to track cache hits
const originalMemoized = expensiveFunction
const memoizedFunction = (...args) => {
  const hadResult = originalMemoized.has(JSON.stringify(args))
  const result = originalMemoized(...args)
  if (hadResult) memoCacheHits.value++
  return result
}
memoizedFunction.size = originalMemoized.size
memoizedFunction.clear = originalMemoized.clear

// Large option list for virtual scrolling demo
const largeOptionList = computed(() => {
  const options = []
  const categories = ['Technology', 'Science', 'Arts', 'Sports', 'Music', 'Food', 'Travel', 'Books']
  const adjectives = ['Amazing', 'Incredible', 'Fantastic', 'Brilliant', 'Outstanding', 'Excellent', 'Wonderful', 'Awesome']
  
  for (let i = 0; i < 10000; i++) {
    const category = categories[i % categories.length]
    const adjective = adjectives[i % adjectives.length]
    options.push({
      value: `option-${i}`,
      label: `${adjective} ${category} Option ${i + 1}`,
      description: `This is option number ${i + 1} in the ${category} category`
    })
  }
  
  return options
})

// Methods
const loadEmailValidation = async () => {
  isLoadingRules.value = true
  try {
    await ensureRulesLoaded(['email', 'required'])
    console.log('Email validation rules loaded')
  } catch (error) {
    console.error('Failed to load validation rules:', error)
  } finally {
    isLoadingRules.value = false
  }
}

const handleDebouncedInput = async (event) => {
  const value = event.target.value
  validationCallCount.value++
  
  if (!value) {
    isValidatingDebounced.value = false
    debouncedResult.value = false
    return
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  
  const validator = measureValidation('email', async (val) => {
    // Simulate async validation delay
    await new Promise(resolve => setTimeout(resolve, 100))
    return emailRegex.test(val)
  })
  
  isValidatingDebounced.value = true
  
  try {
    const result = await debouncedValidation.validateField('email', value, validator, 300)
    debouncedResult.value = result
  } catch (error) {
    debouncedResult.value = false
  } finally {
    isValidatingDebounced.value = false
  }
}

const testMemoization = () => {
  const inputs = ['hello', 'world', 'test', 'hello', 'performance', 'world']
  const randomInput = inputs[Math.floor(Math.random() * inputs.length)]
  lastMemoResult.value = memoizedFunction(randomInput)
}

// Initialize
onMounted(async () => {
  // Preload common validation rules
  await preloadCommonRules()
  console.log('Common validation rules preloaded')
})
</script>