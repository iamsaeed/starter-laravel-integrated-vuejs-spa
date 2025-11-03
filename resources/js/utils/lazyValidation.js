/**
 * Lazy Loading Validation Rules
 * Dynamically imports validation rules only when needed to reduce bundle size
 */

// Cache for loaded rules to avoid re-importing
const ruleCache = new Map()

/**
 * Lazy load validation rules from @vee-validate/rules
 * @param {string|string[]} ruleNames - Rule name(s) to load
 * @returns {Promise<Object>} - Object containing the loaded rules
 */
export async function loadValidationRules(ruleNames) {
  const rules = Array.isArray(ruleNames) ? ruleNames : [ruleNames]
  const loadedRules = {}
  const rulesToLoad = []

  // Check cache first
  for (const ruleName of rules) {
    if (ruleCache.has(ruleName)) {
      loadedRules[ruleName] = ruleCache.get(ruleName)
    } else {
      rulesToLoad.push(ruleName)
    }
  }

  // Load uncached rules
  if (rulesToLoad.length > 0) {
    try {
      // Dynamically import only the rules we need
      const { defineRule } = await import('vee-validate')
      const ruleModules = await Promise.all(
        rulesToLoad.map(async (ruleName) => {
          try {
            const module = await import(`@vee-validate/rules/dist/${ruleName}`)
            return { name: ruleName, rule: module[ruleName] }
          } catch (error) {
            console.warn(`Failed to load validation rule: ${ruleName}`, error)
            return null
          }
        })
      )

      // Define and cache the loaded rules
      ruleModules.forEach((ruleModule) => {
        if (ruleModule) {
          defineRule(ruleModule.name, ruleModule.rule)
          ruleCache.set(ruleModule.name, ruleModule.rule)
          loadedRules[ruleModule.name] = ruleModule.rule
        }
      })
    } catch (error) {
      console.error('Failed to load validation rules:', error)
    }
  }

  return loadedRules
}

/**
 * Preload commonly used validation rules
 * Call this during app initialization for rules you know you'll need
 */
export async function preloadCommonRules() {
  const commonRules = [
    'required',
    'email',
    'min',
    'max',
    'min_value',
    'max_value',
    'confirmed',
    'alpha',
    'alpha_num',
    'numeric',
    'url',
    'regex'
  ]

  return loadValidationRules(commonRules)
}

/**
 * Load rules based on field type
 * @param {string} fieldType - Type of field (email, password, number, etc.)
 * @returns {Promise<Object>} - Relevant rules for the field type
 */
export async function loadRulesForFieldType(fieldType) {
  const ruleMap = {
    email: ['required', 'email'],
    password: ['required', 'min'],
    number: ['numeric', 'min_value', 'max_value'],
    text: ['required', 'min', 'max'],
    url: ['url'],
    phone: ['regex'],
    date: ['required'],
    file: ['size', 'mimes']
  }

  const rules = ruleMap[fieldType] || ['required']
  return loadValidationRules(rules)
}

/**
 * Create a lazy validation schema that loads rules on demand
 * @param {Object} schemaDefinition - Schema definition with rule names
 * @returns {Promise<Object>} - Yup schema with loaded rules
 */
export async function createLazySchema(schemaDefinition) {
  const yup = await import('yup')
  const allRules = new Set()

  // Extract all rule names from schema definition
  const extractRules = (definition) => {
    Object.values(definition).forEach(field => {
      if (field.rules) {
        if (Array.isArray(field.rules)) {
          field.rules.forEach(rule => allRules.add(rule))
        } else if (typeof field.rules === 'string') {
          field.rules.split('|').forEach(rule => allRules.add(rule.split(':')[0]))
        }
      }
    })
  }

  extractRules(schemaDefinition)

  // Load all required rules
  await loadValidationRules(Array.from(allRules))

  // Build the schema
  const schema = {}
  Object.entries(schemaDefinition).forEach(([fieldName, fieldDef]) => {
    let fieldSchema = yup.string()

    if (fieldDef.type === 'number') {
      fieldSchema = yup.number()
    } else if (fieldDef.type === 'boolean') {
      fieldSchema = yup.boolean()
    } else if (fieldDef.type === 'date') {
      fieldSchema = yup.date()
    } else if (fieldDef.type === 'array') {
      fieldSchema = yup.array()
    }

    // Apply rules
    if (fieldDef.required) {
      fieldSchema = fieldSchema.required(fieldDef.requiredMessage || `${fieldName} is required`)
    }

    if (fieldDef.min) {
      fieldSchema = fieldSchema.min(fieldDef.min, fieldDef.minMessage)
    }

    if (fieldDef.max) {
      fieldSchema = fieldSchema.max(fieldDef.max, fieldDef.maxMessage)
    }

    if (fieldDef.email) {
      fieldSchema = fieldSchema.email(fieldDef.emailMessage || 'Must be a valid email')
    }

    schema[fieldName] = fieldSchema
  })

  return yup.object(schema)
}

/**
 * Validation rule loader hook for Vue components
 */
export function useValidationRules() {
  const loadedRules = new Set()

  const ensureRulesLoaded = async (rules) => {
    const rulesToLoad = rules.filter(rule => !loadedRules.has(rule))
    
    if (rulesToLoad.length > 0) {
      await loadValidationRules(rulesToLoad)
      rulesToLoad.forEach(rule => loadedRules.add(rule))
    }
  }

  return {
    loadedRules,
    ensureRulesLoaded,
    loadValidationRules,
    loadRulesForFieldType
  }
}