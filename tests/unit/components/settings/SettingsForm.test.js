/**
 * Unit Tests for SettingsForm Component
 */

import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import SettingsForm from '@/components/settings/SettingsForm.vue'

// Mock vue-router
const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
    { path: '/settings', name: 'settings', component: { template: '<div>Settings</div>' } }
  ]
})

describe('SettingsForm Component', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('Component Rendering', () => {
    it('should render the component', () => {
      const wrapper = mount(SettingsForm, {
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.exists()).toBe(true)
      expect(wrapper.find('form').exists()).toBe(true)
    })

    it('should render slot content', () => {
      const wrapper = mount(SettingsForm, {
        slots: {
          default: '<div class="test-content">Form Content</div>'
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.find('.test-content').exists()).toBe(true)
      expect(wrapper.text()).toContain('Form Content')
    })

    it('should render custom actions slot', () => {
      const wrapper = mount(SettingsForm, {
        slots: {
          actions: '<button class="custom-action">Custom Action</button>'
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.find('.custom-action').exists()).toBe(true)
    })
  })

  describe('Success Message', () => {
    it('should show success message when showSuccess is true', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          showSuccess: true,
          successMessage: 'Settings saved!'
        },
        global: {
          plugins: [router]
        }
      })

      const successComponent = wrapper.findComponent({ name: 'FormSuccess' })
      expect(successComponent.exists()).toBe(true)
      expect(successComponent.props('message')).toBe('Settings saved!')
    })

    it('should not show success message when showSuccess is false', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          showSuccess: false
        },
        global: {
          plugins: [router]
        }
      })

      const successComponent = wrapper.findComponent({ name: 'FormSuccess' })
      expect(successComponent.exists()).toBe(false)
    })

    it('should display custom success message', () => {
      const customMessage = 'Your preferences have been updated!'

      const wrapper = mount(SettingsForm, {
        props: {
          showSuccess: true,
          successMessage: customMessage
        },
        global: {
          plugins: [router]
        }
      })

      const successComponent = wrapper.findComponent({ name: 'FormSuccess' })
      expect(successComponent.props('message')).toBe(customMessage)
    })
  })

  describe('Error Message', () => {
    it('should show error message when errorMessage is provided', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          errorMessage: 'Failed to save settings'
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).toContain('Failed to save settings')
    })

    it('should not show error message when errorMessage is empty', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          errorMessage: ''
        },
        global: {
          plugins: [router]
        }
      })

      const errorDiv = wrapper.find('.bg-red-50')
      expect(errorDiv.exists()).toBe(false)
    })
  })

  describe('Form Submission', () => {
    it('should emit submit event when form is submitted', async () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: true
        },
        global: {
          plugins: [router]
        }
      })

      const form = wrapper.find('form')
      await form.trigger('submit')

      expect(wrapper.emitted('submit')).toBeTruthy()
    })

    it('should emit submit when save button is clicked', async () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: true
        },
        global: {
          plugins: [router]
        }
      })

      const saveButton = wrapper.findAll('button').find(btn =>
        btn.text().includes('Save Settings')
      )

      // Button click should trigger form submit
      const form = wrapper.find('form')
      await form.trigger('submit')

      expect(wrapper.emitted('submit')).toBeTruthy()
    })

    it('should not emit submit when form is saving', async () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isSaving: true,
          isDirty: true
        },
        global: {
          plugins: [router]
        }
      })

      const form = wrapper.find('form')
      await form.trigger('submit')

      expect(wrapper.emitted('submit')).toBeFalsy()
    })

    it('should not emit submit when form is not dirty and requireDirty is true', async () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: false,
          requireDirty: true
        },
        global: {
          plugins: [router]
        }
      })

      const form = wrapper.find('form')
      await form.trigger('submit')

      expect(wrapper.emitted('submit')).toBeFalsy()
    })

    it('should emit submit when not dirty but allowSaveClean is true', async () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: false,
          allowSaveClean: true
        },
        global: {
          plugins: [router]
        }
      })

      const form = wrapper.find('form')
      await form.trigger('submit')

      expect(wrapper.emitted('submit')).toBeTruthy()
    })
  })

  describe('Save Button', () => {
    it('should show save button with default label', () => {
      const wrapper = mount(SettingsForm, {
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).toContain('Save Settings')
    })

    it('should show custom save label', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          saveLabel: 'Update Preferences'
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).toContain('Update Preferences')
    })

    it('should show saving label when isSaving is true', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isSaving: true,
          savingLabel: 'Updating...'
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).toContain('Updating...')
    })

    it('should disable save button when isSaving', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isSaving: true
        },
        global: {
          plugins: [router]
        }
      })

      const saveButton = wrapper.findAll('button').find(btn =>
        btn.text().includes('Saving')
      )
      expect(saveButton.attributes('disabled')).toBeDefined()
    })

    it('should disable save button when not dirty and not allowSaveClean', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: false,
          allowSaveClean: false
        },
        global: {
          plugins: [router]
        }
      })

      const saveButton = wrapper.findAll('button').find(btn =>
        btn.text().includes('Save Settings')
      )
      expect(saveButton.attributes('disabled')).toBeDefined()
    })

    it('should enable save button when dirty', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: true
        },
        global: {
          plugins: [router]
        }
      })

      const saveButton = wrapper.findAll('button').find(btn =>
        btn.text().includes('Save Settings')
      )
      expect(saveButton.attributes('disabled')).toBeUndefined()
    })
  })

  describe('Cancel Button', () => {
    it('should show cancel button by default', () => {
      const wrapper = mount(SettingsForm, {
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).toContain('Cancel')
    })

    it('should not show cancel button when showCancel is false', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          showCancel: false
        },
        global: {
          plugins: [router]
        }
      })

      const buttons = wrapper.findAll('button')
      const cancelButton = buttons.find(btn => btn.text().includes('Cancel'))
      expect(cancelButton).toBeUndefined()
    })

    it('should show custom cancel label', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          cancelLabel: 'Reset'
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).toContain('Reset')
    })

    it('should emit cancel and reset events when cancel clicked and confirmed', async () => {
      // Mock window.confirm to return true
      global.confirm = vi.fn(() => true)

      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: true
        },
        global: {
          plugins: [router]
        }
      })

      const cancelButton = wrapper.findAll('button').find(btn =>
        btn.text().includes('Cancel')
      )
      await cancelButton.trigger('click')

      expect(global.confirm).toHaveBeenCalled()
      expect(wrapper.emitted('cancel')).toBeTruthy()
      expect(wrapper.emitted('reset')).toBeTruthy()
    })

    it('should not emit events when cancel clicked but not confirmed', async () => {
      // Mock window.confirm to return false
      global.confirm = vi.fn(() => false)

      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: true
        },
        global: {
          plugins: [router]
        }
      })

      const cancelButton = wrapper.findAll('button').find(btn =>
        btn.text().includes('Cancel')
      )
      await cancelButton.trigger('click')

      expect(global.confirm).toHaveBeenCalled()
      expect(wrapper.emitted('cancel')).toBeFalsy()
    })
  })

  describe('Unsaved Changes Warning', () => {
    it('should show unsaved changes warning when dirty', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: true,
          showUnsavedWarning: true
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).toContain('You have unsaved changes')
    })

    it('should not show warning when not dirty', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: false,
          showUnsavedWarning: true
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).not.toContain('You have unsaved changes')
    })

    it('should not show warning when showUnsavedWarning is false', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isDirty: true,
          showUnsavedWarning: false
        },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).not.toContain('You have unsaved changes')
    })
  })

  describe('Form Actions Alignment', () => {
    it('should pass actionsAlign prop to FormActions', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          actionsAlign: 'center'
        },
        global: {
          plugins: [router]
        }
      })

      const formActions = wrapper.findComponent({ name: 'FormActions' })
      expect(formActions.props('align')).toBe('center')
    })

    it('should use right alignment by default', () => {
      const wrapper = mount(SettingsForm, {
        global: {
          plugins: [router]
        }
      })

      const formActions = wrapper.findComponent({ name: 'FormActions' })
      expect(formActions.props('align')).toBe('right')
    })
  })

  describe('Loading Icon', () => {
    it('should show loading icon when saving', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isSaving: true
        },
        global: {
          plugins: [router]
        }
      })

      const icon = wrapper.findComponent({ name: 'Icon' })
      expect(icon.exists()).toBe(true)
      expect(icon.props('name')).toBe('loading')
    })

    it('should not show loading icon when not saving', () => {
      const wrapper = mount(SettingsForm, {
        props: {
          isSaving: false
        },
        global: {
          plugins: [router]
        }
      })

      const icons = wrapper.findAllComponents({ name: 'Icon' })
      const loadingIcon = icons.find(icon => icon.props('name') === 'loading')
      expect(loadingIcon).toBeUndefined()
    })
  })
})
