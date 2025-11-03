/**
 * Unit Tests for CountrySelect Component
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import CountrySelect from '@/components/settings/CountrySelect.vue'
import { useSettingsStore } from '@/stores/settings'
import { createCountriesList } from '../../../utils/settingsTestUtils'

describe('CountrySelect Component', () => {
  let pinia
  let settingsStore

  beforeEach(() => {
    pinia = createPinia()
    setActivePinia(pinia)
    settingsStore = useSettingsStore()
    vi.clearAllMocks()
  })

  describe('Component Rendering', () => {
    it('should render the component with default props', () => {
      const wrapper = mount(CountrySelect, {
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.exists()).toBe(true)
      expect(wrapper.find('label').text()).toBe('Country')
    })

    it('should render with custom label', () => {
      const wrapper = mount(CountrySelect, {
        props: {
          label: 'Your Country'
        },
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.find('label').text()).toBe('Your Country')
    })

    it('should render with custom placeholder', async () => {
      const wrapper = mount(CountrySelect, {
        props: {
          placeholder: 'Choose a country',
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()
      const input = wrapper.find('input[type="text"]')
      expect(input.attributes('placeholder')).toBe('Choose a country')
    })

    it('should display required indicator when required prop is true', () => {
      const wrapper = mount(CountrySelect, {
        props: {
          required: true
        },
        global: {
          plugins: [pinia]
        }
      })

      // Check if FormLabel receives required prop
      const label = wrapper.findComponent({ name: 'FormLabel' })
      expect(label.props('required')).toBe(true)
    })
  })

  describe('Data Loading', () => {
    it('should load countries on mount when autoLoad is true', async () => {
      vi.spyOn(settingsStore, 'loadCountries').mockResolvedValue({
        countries: createCountriesList()
      })

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      expect(settingsStore.loadCountries).toHaveBeenCalled()
    })

    it('should not load countries on mount when autoLoad is false', async () => {
      vi.spyOn(settingsStore, 'loadCountries').mockResolvedValue({
        countries: createCountriesList()
      })

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      expect(settingsStore.loadCountries).not.toHaveBeenCalled()
    })

    it('should not reload countries if already loaded', async () => {
      settingsStore.countries = createCountriesList()
      vi.spyOn(settingsStore, 'loadCountries')

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      expect(settingsStore.loadCountries).not.toHaveBeenCalled()
    })

    it('should handle loading errors gracefully', async () => {
      const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      vi.spyOn(settingsStore, 'loadCountries').mockRejectedValue(new Error('Network error'))

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      expect(consoleErrorSpy).toHaveBeenCalledWith(
        'Failed to load countries:',
        expect.any(Error)
      )

      consoleErrorSpy.mockRestore()
    })
  })

  describe('Country Options', () => {
    it('should transform countries into correct option format', async () => {
      const mockCountries = createCountriesList()
      settingsStore.countries = mockCountries

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const options = wrapper.vm.countryOptions

      expect(options.length).toBe(mockCountries.length)
      expect(options[0]).toHaveProperty('value')
      expect(options[0]).toHaveProperty('label')
      expect(options[0].value).toBe(mockCountries[0].code)
      expect(options[0].label).toBe(mockCountries[0].name)
    })

    it('should include region in description if available', async () => {
      const mockCountries = [
        { id: 1, code: 'US', name: 'United States', region: 'North America' }
      ]
      settingsStore.countries = mockCountries

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const options = wrapper.vm.countryOptions
      expect(options[0].description).toBe('North America')
    })
  })

  describe('User Interaction', () => {
    it('should emit select event when country is selected', async () => {
      const mockCountries = createCountriesList()
      settingsStore.countries = mockCountries

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const selectedCountry = { value: 'US', label: 'United States' }
      wrapper.vm.handleCountrySelect(selectedCountry)

      expect(wrapper.emitted('select')).toBeTruthy()
      expect(wrapper.emitted('select')[0]).toEqual([selectedCountry])
    })

    it('should emit change event with country code', async () => {
      const mockCountries = createCountriesList()
      settingsStore.countries = mockCountries

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const selectedCountry = { value: 'US', label: 'United States' }
      wrapper.vm.handleCountrySelect(selectedCountry)

      expect(wrapper.emitted('change')).toBeTruthy()
      expect(wrapper.emitted('change')[0]).toEqual(['US'])
    })
  })

  describe('Component State', () => {
    it('should show loading state while loading countries', async () => {
      let resolveLoad
      const loadPromise = new Promise((resolve) => {
        resolveLoad = resolve
      })
      vi.spyOn(settingsStore, 'loadCountries').mockReturnValue(loadPromise)

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      expect(wrapper.vm.isLoading).toBe(true)

      resolveLoad({ countries: createCountriesList() })
      await flushPromises()

      expect(wrapper.vm.isLoading).toBe(false)
    })

    it('should disable input when disabled prop is true', async () => {
      const wrapper = mount(CountrySelect, {
        props: {
          disabled: true,
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const virtualSelect = wrapper.findComponent({ name: 'VirtualSelectInput' })
      expect(virtualSelect.props('disabled')).toBe(true)
    })

    it('should disable input when loading', async () => {
      let resolveLoad
      const loadPromise = new Promise((resolve) => {
        resolveLoad = resolve
      })
      vi.spyOn(settingsStore, 'loadCountries').mockReturnValue(loadPromise)

      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: true
        },
        global: {
          plugins: [pinia]
        }
      })

      await wrapper.vm.$nextTick()

      const virtualSelect = wrapper.findComponent({ name: 'VirtualSelectInput' })
      expect(virtualSelect.props('disabled')).toBe(true)

      resolveLoad({ countries: createCountriesList() })
      await flushPromises()

      expect(wrapper.vm.isLoading).toBe(false)
    })
  })

  describe('Region Filtering', () => {
    it('should reload countries when region prop changes', async () => {
      vi.spyOn(settingsStore, 'loadCountries').mockResolvedValue({
        countries: createCountriesList()
      })

      const wrapper = mount(CountrySelect, {
        props: {
          region: null,
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      await flushPromises()

      // Change region
      await wrapper.setProps({ region: 'Europe' })
      await flushPromises()

      expect(settingsStore.loadCountries).toHaveBeenCalled()
    })
  })

  describe('Exposed Methods', () => {
    it('should expose loadCountries method', () => {
      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.vm.loadCountries).toBeDefined()
      expect(typeof wrapper.vm.loadCountries).toBe('function')
    })

    it('should expose isLoading ref', () => {
      const wrapper = mount(CountrySelect, {
        props: {
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      expect(wrapper.vm.isLoading).toBeDefined()
      expect(typeof wrapper.vm.isLoading).toBe('boolean')
    })
  })

  describe('Help Text', () => {
    it('should display help text when provided', () => {
      const helpText = 'Select your country of residence'

      const wrapper = mount(CountrySelect, {
        props: {
          helpText,
          autoLoad: false
        },
        global: {
          plugins: [pinia]
        }
      })

      const virtualSelect = wrapper.findComponent({ name: 'VirtualSelectInput' })
      expect(virtualSelect.props('helpText')).toBe(helpText)
    })
  })
})
