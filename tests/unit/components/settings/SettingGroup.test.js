/**
 * Unit Tests for SettingGroup Component
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import SettingGroup from '@/components/settings/SettingGroup.vue'

describe('SettingGroup Component', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('Component Rendering', () => {
    it('should render the component with required title', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'General Settings'
        }
      })

      expect(wrapper.exists()).toBe(true)
      expect(wrapper.text()).toContain('General Settings')
    })

    it('should render with description when provided', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'General Settings',
          description: 'Configure general application settings'
        }
      })

      expect(wrapper.text()).toContain('Configure general application settings')
    })

    it('should render slot content', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'General Settings'
        },
        slots: {
          default: '<div class="test-content">Setting Content</div>'
        }
      })

      expect(wrapper.find('.test-content').exists()).toBe(true)
      expect(wrapper.text()).toContain('Setting Content')
    })

    it('should render multiple items in slot with proper spacing', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'General Settings'
        },
        slots: {
          default: `
            <div class="item-1">Item 1</div>
            <div class="item-2">Item 2</div>
            <div class="item-3">Item 3</div>
          `
        }
      })

      expect(wrapper.find('.item-1').exists()).toBe(true)
      expect(wrapper.find('.item-2').exists()).toBe(true)
      expect(wrapper.find('.item-3').exists()).toBe(true)
    })
  })

  describe('FormSection Integration', () => {
    it('should pass title prop to FormSection', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Appearance Settings'
        }
      })

      const formSection = wrapper.findComponent({ name: 'FormSection' })
      expect(formSection.exists()).toBe(true)
      expect(formSection.props('title')).toBe('Appearance Settings')
    })

    it('should pass description prop to FormSection', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Appearance Settings',
          description: 'Customize the look and feel'
        }
      })

      const formSection = wrapper.findComponent({ name: 'FormSection' })
      expect(formSection.props('description')).toBe('Customize the look and feel')
    })

    it('should pass collapsible prop to FormSection', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Settings',
          collapsible: true
        }
      })

      const formSection = wrapper.findComponent({ name: 'FormSection' })
      expect(formSection.props('collapsible')).toBe(true)
    })

    it('should pass collapsed prop to FormSection', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Settings',
          collapsed: true
        }
      })

      const formSection = wrapper.findComponent({ name: 'FormSection' })
      expect(formSection.props('collapsed')).toBe(true)
    })

    it('should pass showDivider prop to FormSection', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Settings',
          showDivider: false
        }
      })

      const formSection = wrapper.findComponent({ name: 'FormSection' })
      expect(formSection.props('showDivider')).toBe(false)
    })
  })

  describe('Collapsible Behavior', () => {
    it('should emit toggle event when FormSection emits toggle', async () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Settings',
          collapsible: true
        }
      })

      const formSection = wrapper.findComponent({ name: 'FormSection' })
      await formSection.vm.$emit('toggle', true)

      expect(wrapper.emitted('toggle')).toBeTruthy()
      expect(wrapper.emitted('toggle')[0]).toEqual([true])
    })

    it('should handle collapse state changes', async () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Settings',
          collapsible: true,
          collapsed: false
        }
      })

      const formSection = wrapper.findComponent({ name: 'FormSection' })

      // Emit collapsed
      await formSection.vm.$emit('toggle', true)
      expect(wrapper.emitted('toggle')[0]).toEqual([true])

      // Emit expanded
      await formSection.vm.$emit('toggle', false)
      expect(wrapper.emitted('toggle')[1]).toEqual([false])
    })
  })

  describe('Props Validation', () => {
    it('should accept all valid props', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Test Settings',
          description: 'Test description',
          collapsible: true,
          collapsed: false,
          showDivider: true
        }
      })

      expect(wrapper.exists()).toBe(true)
    })

    it('should use default values for optional props', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Test Settings'
        }
      })

      const formSection = wrapper.findComponent({ name: 'FormSection' })
      expect(formSection.props('collapsible')).toBe(false)
      expect(formSection.props('collapsed')).toBe(false)
      expect(formSection.props('showDivider')).toBe(true)
    })
  })

  describe('Content Spacing', () => {
    it('should apply space-y-6 class to content wrapper', () => {
      const wrapper = mount(SettingGroup, {
        props: {
          title: 'Settings'
        },
        slots: {
          default: '<div>Content</div>'
        }
      })

      const contentWrapper = wrapper.find('.space-y-6')
      expect(contentWrapper.exists()).toBe(true)
    })
  })
})
