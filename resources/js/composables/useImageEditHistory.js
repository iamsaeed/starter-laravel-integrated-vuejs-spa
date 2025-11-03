import { ref, computed } from 'vue'

/**
 * Image Edit History Composable
 * Manages undo/redo stack for image editing operations
 *
 * @param {number} maxHistorySize - Maximum number of history states to keep
 * @returns {Object} History management functions and state
 */
export function useImageEditHistory(maxHistorySize = 10) {
  // History stack
  const past = ref([]) // Past states (for undo)
  const present = ref(null) // Current state
  const future = ref([]) // Future states (for redo)

  // Computed properties
  const canUndo = computed(() => past.value.length > 0)
  const canRedo = computed(() => future.value.length > 0)
  const historyPosition = computed(() => past.value.length)
  const totalStates = computed(() => past.value.length + (present.value ? 1 : 0) + future.value.length)

  /**
   * Push a new state to history
   * Clears future states and adds current state to past
   *
   * @param {Blob|File} state - New state to push
   */
  function pushState(state) {
    // If there's a current state, move it to past
    if (present.value) {
      past.value.push(present.value)

      // Limit history size
      if (past.value.length > maxHistorySize) {
        past.value.shift() // Remove oldest state
      }
    }

    // Set new present state
    present.value = state

    // Clear future (can't redo after new change)
    future.value = []
  }

  /**
   * Undo to previous state
   *
   * @returns {Blob|File|null} Previous state or null if can't undo
   */
  function undo() {
    if (!canUndo.value) {
      return null
    }

    // Move current state to future
    if (present.value) {
      future.value.unshift(present.value)
    }

    // Pop from past and set as present
    present.value = past.value.pop()

    return present.value
  }

  /**
   * Redo to next state
   *
   * @returns {Blob|File|null} Next state or null if can't redo
   */
  function redo() {
    if (!canRedo.value) {
      return null
    }

    // Move current state to past
    if (present.value) {
      past.value.push(present.value)

      // Limit history size
      if (past.value.length > maxHistorySize) {
        past.value.shift()
      }
    }

    // Pop from future and set as present
    present.value = future.value.shift()

    return present.value
  }

  /**
   * Reset to initial state (first state in history)
   *
   * @returns {Blob|File|null} Initial state or null if no history
   */
  function reset() {
    if (past.value.length === 0) {
      return present.value
    }

    // Move all states back to get the first one
    const allStates = [...past.value]

    if (present.value) {
      allStates.push(present.value)
    }

    allStates.push(...future.value)

    // First state is the original
    const originalState = allStates[0]

    // Reset history
    past.value = []
    present.value = originalState
    future.value = []

    return originalState
  }

  /**
   * Get current state
   *
   * @returns {Blob|File|null} Current state
   */
  function getCurrentState() {
    return present.value
  }

  /**
   * Clear all history
   */
  function clearHistory() {
    past.value = []
    present.value = null
    future.value = []
  }

  /**
   * Get history summary
   *
   * @returns {Object} History information
   */
  function getHistorySummary() {
    return {
      canUndo: canUndo.value,
      canRedo: canRedo.value,
      position: historyPosition.value,
      total: totalStates.value,
      pastStates: past.value.length,
      futureStates: future.value.length
    }
  }

  return {
    // State
    canUndo,
    canRedo,
    historyPosition,
    totalStates,

    // Actions
    pushState,
    undo,
    redo,
    reset,
    getCurrentState,
    clearHistory,
    getHistorySummary
  }
}
