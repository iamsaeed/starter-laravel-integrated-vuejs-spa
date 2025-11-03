/**
 * Generic Resource Service
 * Handles all API calls for resource CRUD operations
 * Works with any resource registered in config/resources.php
 */

export const resourceService = {
  /**
   * Get resource metadata (fields, filters, actions)
   * @param {string} resource - Resource name (e.g., 'users', 'countries')
   * @param {string} context - Context: 'index' for table view, 'form' for forms
   * @returns {Promise}
   */
  async getMeta(resource, context = 'index') {
    const response = await window.axios.get(`/api/resources/${resource}/meta`, {
      params: { context }
    })
    return response.data
  },

  /**
   * Get paginated list of resources
   * @param {string} resource - Resource name
   * @param {Object} params - Query parameters (page, perPage, search, sort, filters)
   * @returns {Promise}
   */
  async index(resource, params = {}) {
    const response = await window.axios.get(`/api/resources/${resource}`, { params })
    return response.data
  },

  /**
   * Get single resource by ID
   * @param {string} resource - Resource name
   * @param {number|string} id - Resource ID
   * @returns {Promise}
   */
  async show(resource, id) {
    const response = await window.axios.get(`/api/resources/${resource}/${id}`)
    return response.data
  },

  /**
   * Create new resource
   * @param {string} resource - Resource name
   * @param {Object} data - Resource data
   * @returns {Promise}
   */
  async store(resource, data) {
    const response = await window.axios.post(`/api/resources/${resource}`, data)
    return response.data
  },

  /**
   * Update existing resource
   * @param {string} resource - Resource name
   * @param {number|string} id - Resource ID
   * @param {Object} data - Resource data
   * @returns {Promise}
   */
  async update(resource, id, data) {
    const response = await window.axios.put(`/api/resources/${resource}/${id}`, data)
    return response.data
  },

  /**
   * Partially update existing resource (for toggles and quick edits)
   * @param {string} resource - Resource name
   * @param {number|string} id - Resource ID
   * @param {Object} data - Partial resource data (key-value pairs)
   * @returns {Promise}
   */
  async patch(resource, id, data) {
    const response = await window.axios.patch(`/api/resources/${resource}/${id}`, data)
    return response.data
  },

  /**
   * Delete resource
   * @param {string} resource - Resource name
   * @param {number|string} id - Resource ID
   * @returns {Promise}
   */
  async destroy(resource, id) {
    const response = await window.axios.delete(`/api/resources/${resource}/${id}`)
    return response.data
  },

  /**
   * Bulk delete resources
   * @param {string} resource - Resource name
   * @param {Array} ids - Array of resource IDs
   * @returns {Promise}
   */
  async bulkDelete(resource, ids) {
    const response = await window.axios.post(`/api/resources/${resource}/bulk/delete`, { ids })
    return response.data
  },

  /**
   * Bulk update resources
   * @param {string} resource - Resource name
   * @param {Array} ids - Array of resource IDs
   * @param {Object} data - Data to update
   * @returns {Promise}
   */
  async bulkUpdate(resource, ids, data) {
    const response = await window.axios.post(`/api/resources/${resource}/bulk/update`, { ids, ...data })
    return response.data
  },

  /**
   * Run custom action on resources
   * @param {string} resource - Resource name
   * @param {string} action - Action key
   * @param {Array} ids - Array of resource IDs
   * @param {Object} data - Additional action data
   * @returns {Promise}
   */
  async runAction(resource, action, ids, data = {}) {
    const response = await window.axios.post(`/api/resources/${resource}/actions/${action}`, { ids, ...data })
    return response.data
  },

  /**
   * Search related resources (for BelongsTo fields)
   * @param {string} resource - Resource name
   * @param {string} relation - Relation name
   * @param {string} query - Search query
   * @param {number} perPage - Results per page
   * @returns {Promise}
   */
  async searchRelated(resource, relation, query, perPage = 10) {
    const response = await window.axios.get(`/api/resources/${resource}/search`, {
      params: { relation, search: query, perPage }
    })
    return response.data
  }
}
