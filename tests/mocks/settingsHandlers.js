/**
 * MSW (Mock Service Worker) Handlers for Settings API
 * API mock handlers for testing settings endpoints
 */

import { http, HttpResponse } from 'msw'
import {
  createSettingsResponse,
  createUserSettingsResponse,
  createGlobalSettingsResponse,
  createMockSetting,
  createThemesList,
  createCountriesList,
  createTimezonesList,
  createMockSettingList,
  createSuccessfulUpdateResponse,
  createBulkUpdateResponse,
  createValidationErrorResponse,
  createNotFoundErrorResponse,
} from '../utils/settingsTestUtils'

const BASE_URL = 'http://localhost:8001'

/**
 * Settings API Handlers
 */
export const settingsHandlers = [
  // GET /api/settings - Get all accessible settings
  http.get(`${BASE_URL}/api/settings`, ({ request }) => {
    const url = new URL(request.url)
    const group = url.searchParams.get('group')
    const scope = url.searchParams.get('scope')

    return HttpResponse.json(createSettingsResponse(group, scope))
  }),

  // GET /api/settings/{group} - Get settings by group
  http.get(`${BASE_URL}/api/settings/:group`, ({ params }) => {
    const { group } = params
    return HttpResponse.json(createSettingsResponse(group))
  }),

  // GET /api/settings/{key} - Get specific setting
  http.get(`${BASE_URL}/api/settings/:key`, ({ params }) => {
    const { key } = params
    
    if (key === 'non_existent_setting') {
      return HttpResponse.json(createNotFoundErrorResponse().response.data, { 
        status: 404 
      })
    }

    return HttpResponse.json({
      setting: createMockSetting({ key }),
    })
  }),

  // POST /api/settings - Create/update setting
  http.post(`${BASE_URL}/api/settings`, async ({ request }) => {
    const body = await request.json()
    
    if (!body.key || body.value === undefined) {
      return HttpResponse.json(
        createValidationErrorResponse({
          key: ['The key field is required.'],
          value: ['The value field is required.'],
        }).response.data,
        { status: 422 }
      )
    }

    return HttpResponse.json({
      message: 'Setting created successfully',
      setting: createMockSetting({ key: body.key, value: body.value }),
    })
  }),

  // PUT /api/settings/{key} - Update specific setting
  http.put(`${BASE_URL}/api/settings/:key`, async ({ params, request }) => {
    const { key } = params
    const body = await request.json()

    if (key === 'non_existent_setting') {
      return HttpResponse.json(createNotFoundErrorResponse().response.data, { 
        status: 404 
      })
    }

    return HttpResponse.json(createSuccessfulUpdateResponse(key, body.value))
  }),

  // DELETE /api/settings/{key} - Delete setting
  http.delete(`${BASE_URL}/api/settings/:key`, ({ params }) => {
    const { key } = params

    if (key === 'non_existent_setting') {
      return HttpResponse.json(createNotFoundErrorResponse().response.data, { 
        status: 404 
      })
    }

    return HttpResponse.json({
      message: 'Setting deleted successfully',
    })
  }),

  // GET /api/user/settings - Get current user's settings
  http.get(`${BASE_URL}/api/user/settings`, ({ request }) => {
    const url = new URL(request.url)
    const group = url.searchParams.get('group')

    const allSettings = createUserSettingsResponse()

    if (group) {
      // Filter by group (simplified for testing)
      return HttpResponse.json({
        settings: allSettings.settings,
      })
    }

    return HttpResponse.json(allSettings)
  }),

  // GET /api/user/settings/{key} - Get specific user setting
  http.get(`${BASE_URL}/api/user/settings/:key`, ({ params }) => {
    const { key } = params
    const userSettings = createUserSettingsResponse().settings

    if (!userSettings[key]) {
      return HttpResponse.json(createNotFoundErrorResponse().response.data, { 
        status: 404 
      })
    }

    return HttpResponse.json({
      setting: {
        key,
        value: userSettings[key],
      },
    })
  }),

  // PUT /api/user/settings - Update user settings (bulk)
  http.put(`${BASE_URL}/api/user/settings`, async ({ request }) => {
    const body = await request.json()

    if (!body.settings || typeof body.settings !== 'object') {
      return HttpResponse.json(
        createValidationErrorResponse({
          settings: ['The settings field is required and must be an object.'],
        }).response.data,
        { status: 422 }
      )
    }

    return HttpResponse.json(createBulkUpdateResponse(body.settings))
  }),

  // PUT /api/user/settings/{key} - Update single user setting
  http.put(`${BASE_URL}/api/user/settings/:key`, async ({ params, request }) => {
    const { key } = params
    const body = await request.json()

    return HttpResponse.json(createSuccessfulUpdateResponse(key, body.value))
  }),

  // GET /api/settings/lists/{key} - Get predefined options from setting_lists
  http.get(`${BASE_URL}/api/settings/lists/:key`, ({ params }) => {
    const { key } = params

    if (key === 'themes') {
      return HttpResponse.json({
        list: createMockSettingList({ key: 'themes' }),
      })
    }

    if (key === 'date_formats') {
      return HttpResponse.json({
        list: createMockSettingList({
          key: 'date_formats',
          label: 'Date Formats',
          options: [
            { value: 'MM/DD/YYYY', label: 'MM/DD/YYYY' },
            { value: 'DD/MM/YYYY', label: 'DD/MM/YYYY' },
            { value: 'YYYY-MM-DD', label: 'YYYY-MM-DD' },
          ],
        }),
      })
    }

    return HttpResponse.json(createNotFoundErrorResponse().response.data, { 
      status: 404 
    })
  }),

  // GET /api/countries - Get all countries
  http.get(`${BASE_URL}/api/countries`, ({ request }) => {
    const url = new URL(request.url)
    const region = url.searchParams.get('region')
    const search = url.searchParams.get('search')

    let countries = createCountriesList()

    if (region) {
      countries = countries.filter((c) => c.region === region)
    }

    if (search) {
      const searchLower = search.toLowerCase()
      countries = countries.filter((c) =>
        c.name.toLowerCase().includes(searchLower) ||
        c.code.toLowerCase().includes(searchLower)
      )
    }

    return HttpResponse.json({
      countries,
    })
  }),

  // GET /api/countries/{code} - Get country with timezones
  http.get(`${BASE_URL}/api/countries/:code`, ({ params }) => {
    const { code } = params
    const countries = createCountriesList()
    const country = countries.find((c) => c.code === code)

    if (!country) {
      return HttpResponse.json(
        { message: 'Country not found.' },
        { status: 404 }
      )
    }

    // Add timezones for the country
    const timezones = createTimezonesList().filter((tz) => {
      if (code === 'US') return tz.country_id === 1
      if (code === 'GB') return tz.country_id === 3
      return false
    })

    return HttpResponse.json({
      country: {
        ...country,
        timezones,
      },
    })
  }),

  // GET /api/timezones - Get all timezones
  http.get(`${BASE_URL}/api/timezones`, ({ request }) => {
    const url = new URL(request.url)
    const countryId = url.searchParams.get('country_id')
    const region = url.searchParams.get('region')
    const search = url.searchParams.get('search')

    let timezones = createTimezonesList()

    if (countryId) {
      timezones = timezones.filter((tz) => tz.country_id === parseInt(countryId))
    }

    if (search) {
      const searchLower = search.toLowerCase()
      timezones = timezones.filter((tz) =>
        tz.timezone.toLowerCase().includes(searchLower) ||
        tz.display_name.toLowerCase().includes(searchLower)
      )
    }

    return HttpResponse.json({
      timezones,
    })
  }),

  // GET /api/timezones/{id} - Get specific timezone
  http.get(`${BASE_URL}/api/timezones/:id`, ({ params }) => {
    const { id } = params
    const timezones = createTimezonesList()
    const timezone = timezones.find((tz) => tz.id === parseInt(id))

    if (!timezone) {
      return HttpResponse.json(
        { message: 'Timezone not found.' },
        { status: 404 }
      )
    }

    return HttpResponse.json({
      timezone,
    })
  }),

  // GET /api/admin/settings - Get admin settings (admin only)
  http.get(`${BASE_URL}/api/admin/settings`, () => {
    return HttpResponse.json(createGlobalSettingsResponse())
  }),

  // PUT /api/admin/settings - Update admin settings (admin only)
  http.put(`${BASE_URL}/api/admin/settings`, async ({ request }) => {
    const body = await request.json()

    if (!body.settings) {
      return HttpResponse.json(
        createValidationErrorResponse({
          settings: ['The settings field is required.'],
        }).response.data,
        { status: 422 }
      )
    }

    return HttpResponse.json(createBulkUpdateResponse(body.settings))
  }),

  // GET /api/settings/groups - Get all setting groups with counts
  http.get(`${BASE_URL}/api/settings/groups`, () => {
    return HttpResponse.json({
      groups: [
        {
          name: 'general',
          label: 'General',
          description: 'General application settings',
          icon: 'settings',
          count: 5,
        },
        {
          name: 'appearance',
          label: 'Appearance',
          description: 'Theme and display settings',
          icon: 'palette',
          count: 3,
        },
        {
          name: 'localization',
          label: 'Localization',
          description: 'Language, timezone, and date format settings',
          icon: 'globe',
          count: 4,
        },
        {
          name: 'notifications',
          label: 'Notifications',
          description: 'Email and push notification settings',
          icon: 'bell',
          count: 4,
        },
      ],
    })
  }),
]

export default settingsHandlers
