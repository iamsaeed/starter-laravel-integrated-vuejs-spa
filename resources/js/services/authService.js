/**
 * Authentication Service
 * Handles all API calls related to authentication
 */

export const authService = {
  /**
   * Login with email and password
   * @param {Object} credentials - { email, password, remember }
   * @returns {Promise}
   */
  async login(credentials) {
    const response = await window.axios.post('/api/login', credentials)
    return response.data
  },

  /**
   * Register a new user
   * @param {Object} userData - { name, email, password, password_confirmation }
   * @returns {Promise}
   */
  async register(userData) {
    const response = await window.axios.post('/api/register', userData)
    return response.data
  },

  /**
   * Logout the current user
   * @returns {Promise}
   */
  async logout() {
    const response = await window.axios.post('/api/logout')
    return response.data
  },

  /**
   * Send password reset link
   * @param {string} email
   * @returns {Promise}
   */
  async forgotPassword(email) {
    const response = await window.axios.post('/api/forgot-password', { email })
    return response.data
  },

  /**
   * Reset password with token
   * @param {Object} data - { token, email, password, password_confirmation }
   * @returns {Promise}
   */
  async resetPassword(data) {
    const response = await window.axios.post('/api/reset-password', data)
    return response.data
  },

  /**
   * Get current authenticated user
   * @returns {Promise}
   */
  async getUser() {
    const response = await window.axios.get('/api/me')
    return response.data
  },

  /**
   * Update user profile
   * @param {Object} profileData - { name, email }
   * @returns {Promise}
   */
  async updateProfile(profileData) {
    const response = await window.axios.put('/api/profile', profileData)
    return response.data
  },

  /**
   * Change user password
   * @param {Object} passwordData - { current_password, password, password_confirmation }
   * @returns {Promise}
   */
  async changePassword(passwordData) {
    const response = await window.axios.put('/api/password', passwordData)
    return response.data
  },

  /**
   * Logout from all sessions (revoke all tokens)
   * @returns {Promise}
   */
  async logoutAllSessions() {
    const response = await window.axios.post('/api/logout-all-sessions')
    return response.data
  },

  /**
   * Logout from all other sessions except current
   * @returns {Promise}
   */
  async logoutOtherSessions() {
    const response = await window.axios.post('/api/logout-other-sessions')
    return response.data
  },

  /**
   * Check if an email exists in the system (public endpoint)
   * @param {string} email
   * @returns {Promise}
   */
  async checkEmail(email) {
    const response = await window.axios.post('/api/check-email', { email })
    return response.data
  }
}