/**
 * Settings Service
 * Handles all API calls related to settings
 */


export const settingsService = {
  // ============================================
  // User Settings
  // ============================================

  /**
   * Get all current user's settings
   * @param {string} group - Optional group filter (appearance, notifications, etc.)
   * @returns {Promise}
   */
  async getUserSettings(group = null) {
    const params = group ? { group } : {}
    const response = await window.axios.get('/api/user/settings', { params })
    return response.data
  },

  /**
   * Get a specific user setting by key
   * @param {string} key - Setting key
   * @returns {Promise}
   */
  async getUserSetting(key) {
    const response = await window.axios.get(`/api/user/settings/${key}`)
    return response.data
  },

  /**
   * Update multiple user settings at once
   * @param {Object} settings - Key-value pairs of settings
   * @returns {Promise}
   */
  async updateUserSettings(settings) {
    const response = await window.axios.put('/api/user/settings', { settings })
    return response.data
  },

  /**
   * Update a single user setting
   * @param {string} key - Setting key
   * @param {*} value - Setting value
   * @returns {Promise}
   */
  async updateUserSetting(key, value) {
    const response = await window.axios.put(`/api/user/settings/${key}`, { value })
    return response.data
  },

  // ============================================
  // Global Settings (Admin Only)
  // ============================================

  /**
   * Get all global settings
   * @param {string} group - Optional group filter
   * @param {string} scope - Optional scope filter (global, admin)
   * @returns {Promise}
   */
  async getGlobalSettings(group = null, scope = 'global') {
    const params = {}
    if (group) params.group = group
    if (scope) params.scope = scope
    const response = await window.axios.get('/api/settings', { params })
    return response.data
  },

  /**
   * Get a specific global setting by key
   * @param {string} key - Setting key
   * @returns {Promise}
   */
  async getGlobalSetting(key) {
    const response = await window.axios.get(`/api/settings/${key}`)
    return response.data
  },

  /**
   * Create or update a global setting
   * @param {string} key - Setting key
   * @param {*} value - Setting value
   * @returns {Promise}
   */
  async createGlobalSetting(key, value) {
    const response = await window.axios.post('/api/settings', { key, value })
    return response.data
  },

  /**
   * Update a global setting
   * @param {string} key - Setting key
   * @param {*} value - Setting value
   * @returns {Promise}
   */
  async updateGlobalSetting(key, value) {
    const response = await window.axios.put(`/api/settings/${key}`, { value })
    return response.data
  },

  /**
   * Delete a global setting
   * @param {string} key - Setting key
   * @returns {Promise}
   */
  async deleteGlobalSetting(key) {
    const response = await window.axios.delete(`/api/settings/${key}`)
    return response.data
  },

  // ============================================
  // Setting Groups
  // ============================================

  /**
   * Get all setting groups with counts
   * @returns {Promise}
   */
  async getSettingGroups() {
    const response = await window.axios.get('/api/settings/groups')
    return response.data
  },

  // ============================================
  // Setting Lists (Options)
  // ============================================

  /**
   * Get predefined options for a setting
   * @param {string} key - Setting list key (themes, date_formats, etc.)
   * @returns {Promise}
   */
  async getSettingLists(key) {
    const response = await window.axios.get(`/api/settings/lists/${key}`)
    return response.data
  },

  // ============================================
  // Reference Data
  // ============================================

  /**
   * Get all countries
   * @param {string} region - Optional region filter
   * @returns {Promise}
   */
  async getCountries(region = null) {
    const params = region ? { region } : {}
    const response = await window.axios.get('/api/countries', { params })
    return response.data
  },

  /**
   * Get a specific country by code
   * @param {string} code - Country code (US, GB, etc.)
   * @returns {Promise}
   */
  async getCountry(code) {
    const response = await window.axios.get(`/api/countries/${code}`)
    return response.data
  },

  /**
   * Get all timezones
   * @param {string} region - Optional region filter
   * @param {number} countryId - Optional country ID filter
   * @returns {Promise}
   */
  async getTimezones(region = null, countryId = null) {
    const params = {}
    if (region) params.region = region
    if (countryId) params.country_id = countryId
    const response = await window.axios.get('/api/timezones', { params })
    return response.data
  },

  /**
   * Get a specific timezone by ID
   * @param {number} id - Timezone ID
   * @returns {Promise}
   */
  async getTimezone(id) {
    const response = await window.axios.get(`/api/timezones/${id}`)
    return response.data
  }
}
