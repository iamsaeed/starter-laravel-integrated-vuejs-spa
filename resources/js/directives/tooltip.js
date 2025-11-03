/**
 * Tooltip Directive for Vue 3
 *
 * Usage:
 * - Simple: v-tooltip="'Tooltip text'"
 * - Advanced: v-tooltip="{ text: 'Text', position: 'top', delay: 300 }"
 */

let activeTooltip = null

function createTooltipElement(text) {
  const tooltip = document.createElement('div')
  tooltip.className = 'tooltip-container fixed z-[9999] pointer-events-none transition-opacity duration-150 opacity-0'

  const content = document.createElement('div')
  content.className = 'tooltip-content relative px-3 py-1.5 text-xs font-medium rounded-lg shadow-xl bg-gray-900 dark:bg-gray-800 text-white whitespace-nowrap max-w-xs overflow-visible'
  content.textContent = text

  const arrow = document.createElement('div')
  arrow.className = 'tooltip-arrow absolute w-2.5 h-2.5 bg-gray-900 dark:bg-gray-800 transform rotate-45'

  content.appendChild(arrow)
  tooltip.appendChild(content)
  document.body.appendChild(tooltip)

  return { tooltip, content, arrow }
}

function getPosition(el, tooltip, content, preferredPosition = 'top') {
  const rect = el.getBoundingClientRect()
  const tooltipRect = content.getBoundingClientRect()
  const gap = 6 // Gap between element and tooltip
  const arrowSize = 5 // Half of arrow size (2.5 * 2)

  const positions = {
    top: {
      x: rect.left + rect.width / 2 - tooltipRect.width / 2,
      y: rect.top - tooltipRect.height - gap,
      arrowX: tooltipRect.width / 2 - arrowSize,
      arrowY: tooltipRect.height - arrowSize,
      arrowClass: ''
    },
    bottom: {
      x: rect.left + rect.width / 2 - tooltipRect.width / 2,
      y: rect.bottom + gap,
      arrowX: tooltipRect.width / 2 - arrowSize,
      arrowY: -arrowSize,
      arrowClass: ''
    },
    left: {
      x: rect.left - tooltipRect.width - gap,
      y: rect.top + rect.height / 2 - tooltipRect.height / 2,
      arrowX: tooltipRect.width - arrowSize,
      arrowY: tooltipRect.height / 2 - arrowSize,
      arrowClass: ''
    },
    right: {
      x: rect.right + gap,
      y: rect.top + rect.height / 2 - tooltipRect.height / 2,
      arrowX: -arrowSize,
      arrowY: tooltipRect.height / 2 - arrowSize,
      arrowClass: ''
    }
  }

  // Check if preferred position fits in viewport
  function fitsInViewport(pos) {
    const x = positions[pos].x
    const y = positions[pos].y
    return (
      x >= 0 &&
      y >= 0 &&
      x + tooltipRect.width <= window.innerWidth &&
      y + tooltipRect.height <= window.innerHeight
    )
  }

  // Try positions in order: preferred → top → bottom → left → right
  const positionOrder = [preferredPosition, 'top', 'bottom', 'left', 'right']
  const finalPosition = positionOrder.find(pos => fitsInViewport(pos)) || 'top'

  return { ...positions[finalPosition], position: finalPosition }
}

function showTooltip(el, binding) {
  // Hide any existing tooltip
  if (activeTooltip) {
    hideTooltip(activeTooltip.el)
  }

  const options = typeof binding.value === 'string'
    ? { text: binding.value, position: 'top', delay: 300 }
    : { position: 'top', delay: 300, ...binding.value }

  if (!options.text) return

  const showTimeout = setTimeout(() => {
    const { tooltip, content, arrow } = createTooltipElement(options.text)

    // Store reference
    el._tooltip = { tooltip, content, arrow, showTimeout: null }
    activeTooltip = { el, tooltip }

    // Calculate position immediately before showing
    const pos = getPosition(el, tooltip, content, options.position)

    // Set position before showing (prevents animation from top-left)
    tooltip.style.left = `${pos.x}px`
    tooltip.style.top = `${pos.y}px`

    // Position arrow
    arrow.style.left = `${pos.arrowX}px`
    arrow.style.top = `${pos.arrowY}px`

    // Show tooltip with animation on next frame
    requestAnimationFrame(() => {
      tooltip.classList.remove('opacity-0')
      tooltip.classList.add('opacity-100')
    })
  }, options.delay)

  el._tooltip = { showTimeout }
}

function hideTooltip(el) {
  if (!el._tooltip) return

  const { tooltip, showTimeout } = el._tooltip

  // Clear show timeout if tooltip hasn't appeared yet
  if (showTimeout) {
    clearTimeout(showTimeout)
  }

  // Remove tooltip with animation
  if (tooltip && tooltip.parentNode) {
    tooltip.classList.remove('opacity-100')
    tooltip.classList.add('opacity-0')

    setTimeout(() => {
      if (tooltip.parentNode) {
        tooltip.parentNode.removeChild(tooltip)
      }
    }, 200)
  }

  // Clear reference
  if (activeTooltip && activeTooltip.el === el) {
    activeTooltip = null
  }
  el._tooltip = null
}

export default {
  mounted(el, binding) {
    if (!binding.value) return

    el._tooltipHandlers = {
      mouseenter: () => showTooltip(el, binding),
      mouseleave: () => hideTooltip(el)
    }

    el.addEventListener('mouseenter', el._tooltipHandlers.mouseenter)
    el.addEventListener('mouseleave', el._tooltipHandlers.mouseleave)
  },

  updated(el, binding) {
    // If tooltip is currently visible, update it
    if (el._tooltip && el._tooltip.tooltip) {
      hideTooltip(el)
    }
  },

  unmounted(el) {
    hideTooltip(el)

    if (el._tooltipHandlers) {
      el.removeEventListener('mouseenter', el._tooltipHandlers.mouseenter)
      el.removeEventListener('mouseleave', el._tooltipHandlers.mouseleave)
      delete el._tooltipHandlers
    }
  }
}
