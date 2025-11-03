/**
 * Impersonation Service
 * Handles all API calls related to user impersonation
 */

export const impersonationService = {
  /**
   * Start impersonating a user
   * @param {number} userId - ID of the user to impersonate
   * @returns {Promise}
   */
  async impersonate(userId) {
    const response = await window.axios.post(`/api/impersonation/${userId}`)
    return response.data
  },

  /**
   * Stop impersonating and return to admin account
   * @returns {Promise}
   */
  async stopImpersonating() {
    const response = await window.axios.post('/api/impersonation/stop')
    return response.data
  },

  /**
   * Get impersonation status
   * @returns {Promise}
   */
  async getStatus() {
    const response = await window.axios.get('/api/impersonation/status')
    return response.data
  },
}
