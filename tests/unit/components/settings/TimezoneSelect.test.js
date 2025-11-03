/**
 * Unit Tests for TimezoneSelect Component
 */

import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import TimezoneSelect from '@/components/settings/TimezoneSelect.vue'
import { useSettingsStore } from '@/stores/settings'
import {
  createTimezonesList,
  createCountriesList
} from '../../../utils/settingsTestUtils'

describe('TimezoneSelect Component', () => {
  let pinia
  let settingsStore

  beforeEach(() => {
    pinia = createPinia()
    setActivePinia(pinia)
    settingsStore = useSettingsStore()
    vi.clearAllMocks()

    // Mock setInterval and clearInterval
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.restoreAllMocks()
    vi.useRealTimers()
  })

  describe('Component Rendering', () => {
    it('should render the component with default props', () => {
      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.exists()).toBe(true)
      expect(wrapper.find('label').text()).toBe('Timezone')
    })

    it('should render with custom label', () => {
      const wrapper = mount(TimezoneSelect, {
        props: {
          label: 'Your Timezone',
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.find('label').text()).toBe('Your Timezone')
    })

    it('should render with custom placeholder', async () => {
      const wrapper = mount(TimezoneSelect, {
        props: {
          placeholder: 'Choose a timezone',
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()
      const input = wrapper.find('input[type="text"]')
      expect(input.attributes('placeholder')).toBe('Choose a timezone')
    })
  })

  describe('Data Loading', () => {
    it('should load timezones on mount when autoLoad is true', async () => {
      vi.spyOn(settingsStore, 'loadTimezones').mockResolvedValue({
        timezones: createTimezonesList()
      })

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      expect(settingsStore.loadTimezones).toHaveBeenCalled()
    })

    it('should not load timezones on mount when autoLoad is false', async () => {
      vi.spyOn(settingsStore, 'loadTimezones').mockResolvedValue({
        timezones: createTimezonesList()
      })

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      expect(settingsStore.loadTimezones).not.toHaveBeenCalled()
    })

    it('should not reload timezones if already loaded', async () => {
      settingsStore.timezones = createTimezonesList()
      vi.spyOn(settingsStore, 'loadTimezones')

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      expect(settingsStore.loadTimezones).not.toHaveBeenCalled()
    })

    it('should handle loading errors gracefully', async () => {
      const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      vi.spyOn(settingsStore, 'loadTimezones').mockRejectedValue(new Error('Network error'))

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      expect(consoleErrorSpy).toHaveBeenCalledWith(
        'Failed to load timezones:',
        expect.any(Error)
      )

      consoleErrorSpy.mockRestore()
    })
  })

  describe('Timezone Options', () => {
    it('should transform timezones into correct option format', async () => {
      const mockTimezones = createTimezonesList()
      settingsStore.timezones = mockTimezones

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const options = wrapper.vm.timezoneOptions

      expect(options.length).toBeGreaterThan(0)
      expect(options[0]).toHaveProperty('value')
      expect(options[0]).toHaveProperty('label')
      expect(options[0]).toHaveProperty('description')
      expect(options[0].value).toBe(mockTimezones[0].id)
    })

    it('should sort primary timezone first', async () => {
      const mockTimezones = [
        { id: 1, timezone: 'America/Chicago', display_name: 'Central Time', offset: '-06:00', is_primary: false, country_id: 1 },
        { id: 2, timezone: 'America/New_York', display_name: 'Eastern Time', offset: '-05:00', is_primary: true, country_id: 1 }
      ]
      settingsStore.timezones = mockTimezones

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const options = wrapper.vm.timezoneOptions

      // Primary should be first
      expect(options[0].label).toBe('Eastern Time')
    })

    it('should filter timezones by country when countryCode is provided', async () => {
      const mockCountries = createCountriesList()
      const mockTimezones = createTimezonesList()

      settingsStore.countries = mockCountries
      settingsStore.timezones = mockTimezones

      const wrapper = mount(TimezoneSelect, {
        props: {
          countryCode: 'US',
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const options = wrapper.vm.timezoneOptions

      // Should only include timezones for US (country_id: 1)
      expect(options.every(opt => opt.data.country_id === 1)).toBe(true)
    })
  })

  describe('Country Dependency', () => {
    it('should disable input when requireCountry is true and no countryCode', () => {
      const wrapper = mount(TimezoneSelect, {
        props: {
          requireCountry: true,
          countryCode: null,
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      const virtualSelect = wrapper.findComponent({ name: 'VirtualSelectInput' })
      expect(virtualSelect.props('disabled')).toBe(true)
    })

    it('should enable input when requireCountry is true and countryCode is provided', () => {
      const wrapper = mount(TimezoneSelect, {
        props: {
          requireCountry: true,
          countryCode: 'US',
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      const virtualSelect = wrapper.findComponent({ name: 'VirtualSelectInput' })
      expect(virtualSelect.props('disabled')).toBe(false)
    })

    it('should show appropriate help text when country is required but not selected', () => {
      const wrapper = mount(TimezoneSelect, {
        props: {
          requireCountry: true,
          countryCode: null,
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.vm.computedHelpText).toBe('Please select a country first')
    })

    it('should reload timezones when countryCode changes', async () => {
      vi.spyOn(settingsStore, 'loadTimezones').mockResolvedValue({
        timezones: createTimezonesList()
      })

      const wrapper = mount(TimezoneSelect, {
        props: {
          requireCountry: true,
          countryCode: null,
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      // Change country
      await wrapper.setProps({ countryCode: 'US' })
      await flushPromises()

      expect(settingsStore.loadTimezones).toHaveBeenCalled()
    })
  })

  describe('Time Preview', () => {
    it('should show time preview when showPreview is true and timezone is selected', async () => {
      const mockTimezones = createTimezonesList()
      settingsStore.timezones = mockTimezones

      const wrapper = mount(TimezoneSelect, {
        props: {
          showPreview: true,
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      // Select a timezone
      const selectedTz = {
        value: 1,
        label: 'Eastern Time',
        data: mockTimezones[0]
      }
      wrapper.vm.selectedTimezone = selectedTz

      await wrapper.vm.$nextTick()

      expect(wrapper.text()).toContain('Current time:')
    })

    it('should not show time preview when showPreview is false', async () => {
      const mockTimezones = createTimezonesList()
      settingsStore.timezones = mockTimezones

      const wrapper = mount(TimezoneSelect, {
        props: {
          showPreview: false,
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      // Select a timezone
      const selectedTz = {
        value: 1,
        label: 'Eastern Time',
        data: mockTimezones[0]
      }
      wrapper.vm.selectedTimezone = selectedTz

      await wrapper.vm.$nextTick()

      expect(wrapper.text()).not.toContain('Current time:')
    })
  })

  describe('User Interaction', () => {
    it('should emit select event when timezone is selected', async () => {
      const mockTimezones = createTimezonesList()
      settingsStore.timezones = mockTimezones

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const selectedTimezone = {
        value: 1,
        label: 'Eastern Time',
        data: mockTimezones[0]
      }
      wrapper.vm.handleTimezoneSelect(selectedTimezone)

      expect(wrapper.emitted('select')).toBeTruthy()
      expect(wrapper.emitted('select')[0]).toEqual([selectedTimezone])
    })

    it('should emit change event with timezone id', async () => {
      const mockTimezones = createTimezonesList()
      settingsStore.timezones = mockTimezones

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const selectedTimezone = {
        value: 1,
        label: 'Eastern Time',
        data: mockTimezones[0]
      }
      wrapper.vm.handleTimezoneSelect(selectedTimezone)

      expect(wrapper.emitted('change')).toBeTruthy()
      expect(wrapper.emitted('change')[0]).toEqual([1])
    })
  })

  describe('Loading State', () => {
    it('should show loading state while loading timezones', async () => {
      let resolveLoad
      const loadPromise = new Promise((resolve) => {
        resolveLoad = resolve
      })
      vi.spyOn(settingsStore, 'loadTimezones').mockReturnValue(loadPromise)

      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      expect(wrapper.vm.isLoading).toBe(true)

      resolveLoad({ timezones: createTimezonesList() })
      await flushPromises()

      expect(wrapper.vm.isLoading).toBe(false)
    })
  })

  describe('Exposed Methods', () => {
    it('should expose loadTimezones method', () => {
      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.vm.loadTimezones).toBeDefined()
      expect(typeof wrapper.vm.loadTimezones).toBe('function')
    })

    it('should expose selectedTimezone ref', () => {
      const wrapper = mount(TimezoneSelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.vm.selectedTimezone).toBeDefined()
    })
  })
})
