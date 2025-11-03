# 100% Frontend Testing Implementation Plan for Settings System

## Overview
Comprehensive Vue.js testing strategy covering all frontend components, services, stores, and user flows for the settings system as described in `docs_dev/settings.md`.

---

## Test Architecture

### Testing Pyramid Strategy
```
E2E Tests (10%)          ← Complete user workflows
    ↑
Integration Tests (30%) ← Components + Stores + Services
    ↑
Unit Tests (60%)        ← Individual functions/components
```

---

## Phase 1: Test Infrastructure Setup

### 1.1 Additional Testing Dependencies
```bash
npm install --save-dev @testing-library/vue @testing-library/user-event msw
```

**New dependencies:**
- `@testing-library/vue` - DOM testing utilities
- `@testing-library/user-event` - User interaction simulation
- `msw` (Mock Service Worker) - API mocking for integration tests

### 1.2 Test Utilities Enhancement
**File:** `tests/utils/settingsTestUtils.js`
```javascript
// Mock data factories for settings
- createMockSetting(overrides)
- createMockSettingList(overrides)
- createMockCountry(overrides)
- createMockTimezone(overrides)
- createSettingsResponse(group, scope)
- createThemesList()
- createCountriesList()
- createTimezonesList()
```

### 1.3 MSW Server Setup
**File:** `tests/mocks/settingsHandlers.js`
```javascript
// API mock handlers for settings endpoints
- GET /api/settings
- GET /api/settings/{group}
- GET /api/settings/{key}
- POST /api/settings
- PUT /api/settings/{key}
- DELETE /api/settings/{key}
- GET /api/user/settings
- PUT /api/user/settings
- PUT /api/user/settings/{key}
- GET /api/settings/lists/{key}
- GET /api/countries
- GET /api/timezones
```

---

## Phase 2: Service Layer Tests (18 test files)

### 2.1 Settings Service Tests
**File:** `tests/unit/services/settingsService.test.js` (25 tests)

**Test Coverage:**
- `getSettings(scope, group)` - Success/failure scenarios
- `getSetting(key)` - Found/not found cases
- `updateSetting(key, value)` - Type validation
- `bulkUpdate(settings)` - Batch operations
- `getUserSettings()` - User-specific settings
- `updateUserSettings(settings)` - Bulk user updates
- Error handling (network errors, 422, 500)
- Request/response transformation
- Query parameter handling

### 2.2 Countries Service Tests
**File:** `tests/unit/services/countriesService.test.js` (15 tests)

**Test Coverage:**
- `getCountries()` - Fetch all countries
- `getCountriesByRegion(region)` - Filter by region
- `getCountryWithTimezones(code)` - Country + timezones
- `searchCountries(query)` - Search functionality
- Response caching logic
- Pagination handling
- Error scenarios

### 2.3 Timezones Service Tests
**File:** `tests/unit/services/timezonesService.test.js` (15 tests)

**Test Coverage:**
- `getTimezones()` - Fetch all timezones
- `getTimezonesByCountry(countryId)` - Country filtering
- `getTimezonesByRegion(region)` - Region filtering
- `searchTimezones(query)` - Search functionality
- DST offset calculations
- Response transformation
- Error handling

---

## Phase 3: Pinia Store Tests (24 test files)

### 3.1 Settings Store Tests
**File:** `tests/unit/stores/settings.test.js` (35 tests)

**State Tests:**
- Initial state values
- Reactive state updates

**Getters Tests:**
- `settingsByGroup(group)` - Group filtering
- `getSetting(key)` - Individual setting retrieval
- `publicSettings` - Filter public settings
- `settingGroups` - Group metadata

**Actions Tests:**
- `fetchSettings(scope, group)` - API call + state update
- `fetchUserSettings()` - User settings fetch
- `updateSetting(key, value)` - Single update
- `bulkUpdateSettings(settings)` - Batch update
- `deleteSetting(key)` - Deletion
- Loading state management
- Error handling
- Cache invalidation

### 3.2 Countries Store Tests
**File:** `tests/unit/stores/countries.test.js` (20 tests)

**Test Coverage:**
- Fetch and cache countries
- Region filtering
- Search functionality
- Country selection state
- Loading/error states
- Cache expiration logic

### 3.3 Timezones Store Tests
**File:** `tests/unit/stores/timezones.test.js` (20 tests)

**Test Coverage:**
- Fetch and cache timezones
- Country/region filtering
- Timezone selection state
- DST calculations
- Loading/error states
- Cache management

---

## Phase 4: Component Unit Tests (70 test files)

### 4.1 SettingInput.vue Tests
**File:** `tests/unit/components/settings/SettingInput.test.js` (45 tests)

**Type-Specific Tests:**
- **String Input** (8 tests)
  - Renders text input
  - Value binding
  - Validation display
  - Max length enforcement
  - Placeholder text
  - Disabled state
  - Change events
  - Clear functionality

- **Integer Input** (8 tests)
  - Renders number input
  - Min/max validation
  - Step increments
  - Numeric-only input
  - Change events
  - Error messages
  - Disabled state
  - Default values

- **Boolean Input** (6 tests)
  - Renders toggle/checkbox
  - True/false states
  - Change events
  - Disabled state
  - Label display
  - Accessibility

- **Reference Input** (10 tests)
  - Renders select dropdown
  - Loads reference options
  - Selected value binding
  - Change events
  - Loading state
  - Empty state
  - Search functionality
  - Disabled state
  - Error handling
  - Option rendering

- **Array/JSON Input** (8 tests)
  - Renders textarea/JSON editor
  - JSON validation
  - Syntax highlighting
  - Parse errors
  - Change events
  - Disabled state
  - Formatting
  - Default values

- **Common Tests** (5 tests)
  - Props validation
  - Emits validation events
  - Required field indicator
  - Help text display
  - Icon display

### 4.2 SettingGroup.vue Tests
**File:** `tests/unit/components/settings/SettingGroup.test.js` (20 tests)

**Test Coverage:**
- Renders group with icon and title
- Displays group description
- Lists all settings in group
- Collapsible/expandable state
- Empty state handling
- Loading skeleton
- Settings ordering
- Group metadata display
- Accessibility (ARIA labels)
- Responsive behavior

### 4.3 SettingsForm.vue Tests
**File:** `tests/unit/components/settings/SettingsForm.test.js` (30 tests)

**Test Coverage:**
- Form initialization with settings
- VeeValidate integration
- Field-level validation
- Form-level validation
- Submit handler
- Cancel/reset functionality
- Dirty state detection
- Unsaved changes warning
- Loading state during submit
- Success/error message display
- Bulk updates
- Individual field updates
- Validation error display
- Form accessibility
- Keyboard navigation

### 4.4 CountrySelect.vue Tests
**File:** `tests/unit/components/settings/CountrySelect.test.js` (25 tests)

**Test Coverage:**
- Renders searchable dropdown
- Fetches countries on mount
- Search/filter functionality
- Country selection
- Flag emoji display
- Loading state
- Empty state
- Error state
- Pagination (if applicable)
- Keyboard navigation
- Accessibility (ARIA)
- Clear selection
- Disabled state
- Default selection
- Change events
- Region grouping
- Popular countries first
- Virtual scrolling (for large lists)

### 4.5 TimezoneSelect.vue Tests
**File:** `tests/unit/components/settings/TimezoneSelect.test.js` (30 tests)

**Test Coverage:**
- Renders searchable dropdown
- Fetches timezones on mount
- Country filter integration
- Offset display (e.g., UTC-05:00)
- DST indicator
- Search/filter functionality
- Timezone selection
- Loading state
- Empty state
- Error state
- Keyboard navigation
- Accessibility
- Clear selection
- Disabled state
- Default selection (user's current timezone)
- Change events
- Region grouping
- Primary timezone highlighting
- Time display preview
- Virtual scrolling

### 4.6 SettingsPage.vue Tests
**File:** `tests/unit/components/settings/SettingsPage.test.js` (40 tests)

**Test Coverage:**
- Page renders with tabs
- Tab navigation (General, Localization, Appearance, Notifications)
- Fetches settings on mount
- Displays settings by group
- Filters settings by scope
- Save button state (enabled/disabled)
- Save all settings
- Cancel changes
- Unsaved changes detection
- Loading state
- Error state
- Success message
- Permission-based visibility
- Admin-only settings guard
- Search/filter settings
- Settings reordering
- Responsive layout
- Tab persistence (URL params)
- Breadcrumbs
- Help tooltips

### 4.7 UserSettingsPage.vue Tests
**File:** `tests/unit/components/settings/UserSettingsPage.test.js` (35 tests)

**Test Coverage:**
- Fetches user-specific settings
- Displays only user-accessible settings
- Theme switcher integration
- Language selector
- Timezone selector
- Country selector
- Date/time format selectors
- Notifications toggle
- Save settings
- Reset to defaults
- Loading state
- Error handling
- Success feedback
- Real-time preview (theme changes)
- Validation errors
- Auto-save (debounced)
- Dirty state indicator
- Responsive design

---

## Phase 5: Integration Tests (35 test files)

### 5.1 Settings CRUD Flow
**File:** `tests/integration/settings/SettingsCrudFlow.test.js` (25 tests)

**Test Coverage:**
- **Fetch Settings**
  - Load all settings
  - Filter by group
  - Filter by scope
  - Store population

- **Create Setting**
  - Create new setting via form
  - Validation
  - API call
  - Store update
  - UI feedback

- **Update Setting**
  - Update existing setting
  - Optimistic update
  - API call
  - Store sync
  - Error rollback

- **Delete Setting**
  - Delete setting
  - Confirmation dialog
  - API call
  - Store removal
  - UI update

- **Bulk Operations**
  - Bulk update multiple settings
  - Validation for all fields
  - Transaction-like behavior
  - Partial failure handling

### 5.2 User Settings Flow
**File:** `tests/integration/settings/UserSettingsFlow.test.js` (30 tests)

**Test Coverage:**
- Load user settings on page mount
- Update theme and see preview
- Change language (with i18n update)
- Select country and auto-load timezones
- Change timezone
- Update date/time formats
- Toggle notifications
- Save all changes (bulk update)
- Reset to defaults
- Settings persistence
- Store + Service + Component integration
- Error recovery
- Loading states across components
- Real-time updates (WebSocket/polling if applicable)

### 5.3 Theme Switching Flow
**File:** `tests/integration/settings/ThemeSwitchingFlow.test.js` (20 tests)

**Test Coverage:**
- Load available themes from settings API
- Display theme previews with metadata
- Select new theme
- Apply theme instantly (CSS variables)
- Save theme preference
- Theme persistence across sessions
- Store integration
- Component re-rendering
- Accessibility contrast checks
- Default theme fallback
- Theme migration (from old user.theme)

### 5.4 Country-Timezone Selection Flow
**File:** `tests/integration/settings/CountryTimezoneFlow.test.js` (25 tests)

**Test Coverage:**
- Select country
- Auto-load country's timezones
- Display primary timezone first
- Select timezone from country's list
- DST information display
- Current time preview
- Offset display
- Search across countries
- Search across timezones
- Store synchronization
- API caching
- Error handling (no timezones for country)

### 5.5 Settings Validation Flow
**File:** `tests/integration/settings/ValidationFlow.test.js` (20 tests)

**Test Coverage:**
- Field-level validation (VeeValidate)
- Backend validation errors (422 responses)
- Display validation errors
- Clear errors on correction
- Form submission prevention
- Required field validation
- Type validation (string, integer, boolean)
- Custom validation rules
- Cross-field validation
- Async validation (uniqueness checks)

---

## Phase 6: E2E Tests (10 test files)

### 6.1 Complete Settings Management Workflow
**File:** `tests/e2e/settings/CompleteSettingsWorkflow.test.js` (15 tests)

**User Journey:**
1. Login as admin
2. Navigate to settings page
3. Switch between tabs
4. Update general settings
5. Update localization settings
6. Save changes
7. Verify persistence
8. Logout and login
9. Verify settings applied

### 6.2 User Personalization Journey
**File:** `tests/e2e/settings/UserPersonalizationJourney.test.js` (20 tests)

**User Journey:**
1. Login as user
2. Navigate to user settings
3. Change theme (verify instant preview)
4. Select country
5. Select timezone from country
6. Change date/time format
7. Toggle notifications
8. Save all settings
9. Navigate away and back
10. Verify all settings persisted
11. Reset to defaults
12. Verify reset successful

### 6.3 Theme Migration Journey
**File:** `tests/e2e/settings/ThemeMigrationJourney.test.js` (10 tests)

**Journey:**
1. User has old theme in user.theme column
2. Migration command runs
3. Theme moved to settings table
4. User logs in
5. Theme loads from settings API
6. Theme switcher works
7. Old theme column removed
8. No breaking changes

---

## Phase 7: Accessibility Tests (5 test files)

### 7.1 Settings Page Accessibility
**File:** `tests/a11y/settings/SettingsPageAccessibility.test.js` (15 tests)

**Test Coverage:**
- Keyboard navigation (Tab, Enter, Escape)
- ARIA labels and roles
- Screen reader announcements
- Focus management
- Color contrast (WCAG AA)
- Form labels association
- Error announcements
- Success announcements
- Skip navigation links
- Landmark regions

### 7.2 Form Controls Accessibility
**File:** `tests/a11y/settings/FormControlsAccessibility.test.js` (20 tests)

**Test Coverage:**
- Input fields accessibility
- Select dropdowns accessibility
- Toggle switches accessibility
- Checkboxes/radios accessibility
- Error message association
- Required field indicators
- Help text association
- Disabled state communication

---

## Phase 8: Performance Tests (3 test files)

### 8.1 Settings Page Performance
**File:** `tests/performance/settings/SettingsPagePerformance.test.js` (10 tests)

**Test Coverage:**
- Initial page load time
- Settings fetching speed
- Component mount time
- Re-render performance
- Virtual scrolling (large lists)
- Debounced search performance
- Cache effectiveness
- Memory leak detection
- Bundle size analysis

---

## Test Summary by Numbers

### Total Test Files: **163 files**

### Test Count Breakdown:
| Category | Test Files | Estimated Tests | Coverage |
|----------|-----------|----------------|----------|
| **Unit Tests** |
| Services | 3 | 55 | 100% |
| Stores | 3 | 75 | 100% |
| Components | 7 | 225 | 100% |
| **Integration Tests** | 5 | 120 | 100% |
| **E2E Tests** | 3 | 45 | 90% |
| **Accessibility** | 2 | 35 | 95% |
| **Performance** | 1 | 10 | 85% |
| **TOTAL** | **24** | **565 tests** | **~98%** |

---

## Testing Tools & Libraries

### Core Testing
- ✅ Vitest (already installed)
- ✅ @vue/test-utils (already installed)
- ✅ @pinia/testing (already installed)
- ✅ jsdom/happy-dom (already installed)

### Additional Tools Needed
- `@testing-library/vue` - DOM testing utilities
- `@testing-library/user-event` - User interactions
- `msw` - API mocking
- `axe-core` - Accessibility testing
- `@vitest/coverage-v8` - Code coverage
- `playwright` or `cypress` - E2E testing

---

## Implementation Timeline

### Week 1: Infrastructure
- Setup MSW handlers
- Create test utilities
- Mock data factories

### Week 2-3: Service & Store Tests
- Complete all service tests (3 files, 55 tests)
- Complete all store tests (3 files, 75 tests)

### Week 4-5: Component Unit Tests
- SettingInput.vue (45 tests)
- SettingGroup.vue (20 tests)
- SettingsForm.vue (30 tests)

### Week 6: Component Tests (Continued)
- CountrySelect.vue (25 tests)
- TimezoneSelect.vue (30 tests)
- SettingsPage.vue (40 tests)
- UserSettingsPage.vue (35 tests)

### Week 7: Integration Tests
- All 5 integration test files (120 tests)

### Week 8: E2E & Accessibility
- E2E tests (45 tests)
- Accessibility tests (35 tests)
- Performance tests (10 tests)

### Week 9: Polish & Documentation
- Fix failing tests
- Achieve 98%+ coverage
- Document testing patterns
- CI/CD integration

---

## Coverage Goals

### Target Coverage Metrics
- **Unit Tests:** 95%+ line coverage
- **Integration Tests:** 90%+ feature coverage
- **E2E Tests:** 80%+ user journey coverage
- **Overall:** 98%+ code coverage

### Coverage Reports
```bash
npm run test:coverage -- --reporter=html --reporter=lcov
```

---

## CI/CD Integration

### GitHub Actions Workflow
```yaml
- Run unit tests on every PR
- Run integration tests on merge to main
- Run E2E tests nightly
- Generate coverage reports
- Block PR if coverage drops below 95%
```

---

## Key Testing Principles

1. **Test behavior, not implementation**
2. **Use real user interactions** (clicks, typing, not direct method calls)
3. **Test accessibility from the start**
4. **Mock external dependencies** (API calls via MSW)
5. **Test edge cases and error states**
6. **Keep tests fast and isolated**
7. **Follow AAA pattern** (Arrange, Act, Assert)
8. **Write readable test descriptions**
9. **Avoid test interdependencies**
10. **Test responsive behavior**

---

## Testing Best Practices for Settings System

### 1. Mock Data Consistency
- Use factory functions for all mock data
- Keep mock data structure consistent with API responses
- Version mock data alongside API versions

### 2. Test Isolation
- Each test should be independent
- Reset store state between tests
- Clear localStorage/sessionStorage

### 3. Async Testing
- Always use `async/await` with API calls
- Use `flushPromises()` for Vue reactivity
- Mock timers for debounced operations

### 4. Error Handling
- Test both happy and sad paths
- Verify error messages display correctly
- Test error recovery mechanisms

### 5. Performance Testing
- Monitor component mount times
- Test with large datasets (1000+ settings)
- Verify virtual scrolling works correctly
- Check memory usage with Chrome DevTools

### 6. Accessibility Testing
- Run axe-core on every component
- Test keyboard navigation flows
- Verify screen reader announcements
- Check ARIA attributes

---

## Example Test Structure

```javascript
describe('SettingsPage.vue', () => {
  describe('Component Rendering', () => {
    it('should render with all tabs', () => {
      // Arrange
      const wrapper = mountWithSetup(SettingsPage)

      // Act
      const tabs = wrapper.findAll('.tab')

      // Assert
      expect(tabs).toHaveLength(4)
      expect(tabs[0].text()).toBe('General')
      expect(tabs[1].text()).toBe('Localization')
      expect(tabs[2].text()).toBe('Appearance')
      expect(tabs[3].text()).toBe('Notifications')
    })
  })

  describe('Settings CRUD', () => {
    it('should save updated settings', async () => {
      // Arrange
      const wrapper = mountWithSetup(SettingsPage)
      const settingsStore = useSettingsStore()

      // Act
      await wrapper.find('#app-name').setValue('New App Name')
      await wrapper.find('.save-button').trigger('click')
      await flushPromises()

      // Assert
      expect(settingsStore.updateSetting).toHaveBeenCalledWith(
        'app_name',
        'New App Name'
      )
      expect(wrapper.find('.success-message').text()).toBe(
        'Settings saved successfully'
      )
    })
  })
})
```

---

## Resources

### Documentation
- [Vitest Documentation](https://vitest.dev/)
- [Vue Test Utils](https://test-utils.vuejs.org/)
- [Testing Library](https://testing-library.com/docs/vue-testing-library/intro/)
- [MSW Documentation](https://mswjs.io/)
- [Pinia Testing](https://pinia.vuejs.org/cookbook/testing.html)

### Reference Projects
- Vue 3 official examples
- Vite testing examples
- Pinia testing examples

---

This comprehensive testing plan ensures 98%+ code coverage and confidence in the settings system implementation!
