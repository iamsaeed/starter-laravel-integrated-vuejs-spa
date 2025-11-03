# Multi-Layout Admin Panel System - Implementation Guide

## Overview

Create a flexible admin panel layout system that allows users to choose from multiple layout configurations, similar to how themes work. This provides customization options for different workflow preferences and screen sizes.

**Key Features:**
- 8 distinct layout types with different navigation patterns
- User-specific layout preferences (stored per user)
- Global default layout setting (admin configurable)
- Seamless layout switching without page refresh
- Fully responsive designs
- Integration with existing settings system
- Compatible with all theme colors

## Layout Types

### 1. Classic Sidebar (Default)
**Current implementation** - Traditional admin interface.

**Features:**
- Left vertical sidebar with collapsible functionality
- Top horizontal navbar
- Main content area on the right
- Mobile: Slide-out sidebar with overlay

**Best For:**
- Traditional admin panels
- Users familiar with conventional layouts
- Multi-level navigation hierarchies

**Component:** `ClassicLayout.vue`

### 2. Horizontal Navigation
Top-only navigation layout with no sidebar.

**Features:**
- Full-width top navbar with horizontal menu
- Dropdown menus for sub-navigation
- Maximum content width utilization
- Clean, minimal appearance

**Best For:**
- Content-heavy applications
- Users who prefer more screen real estate
- Flat navigation structures

**Component:** `HorizontalLayout.vue`

### 3. Compact Sidebar
Always-collapsed sidebar with icons only.

**Features:**
- Narrow sidebar (64px) with icons
- Tooltips on hover for labels
- Top navbar for context/actions
- Maximized content area

**Best For:**
- Users who want maximum content space
- Icon-first navigation preference
- Large displays where tooltips work well

**Component:** `CompactLayout.vue`

### 4. Hybrid (Top + Side)
Two-tier navigation system.

**Features:**
- Horizontal top menu for main sections
- Vertical sidebar for sub-navigation within each section
- Contextual sidebar content based on top selection
- Best of both worlds approach

**Best For:**
- Multi-module applications
- Complex navigation hierarchies
- Users who need both breadth and depth

**Component:** `HybridLayout.vue`

### 5. Boxed Layout
Centered layout with constrained width.

**Features:**
- Content area with max-width (1280px or 1536px)
- Centered on screen with margins
- Sidebar integrated within boxed container
- More focused, less overwhelming

**Best For:**
- Users who find full-width overwhelming
- Focused task completion
- Reading-heavy interfaces

**Component:** `BoxedLayout.vue`

### 6. Detached/Floating
Modern floating card-style layout.

**Features:**
- Sidebar and content appear as floating cards
- Subtle shadows and spacing between elements
- More modern, material design aesthetic
- Backdrop blur effects

**Best For:**
- Modern, contemporary feel
- Users who prefer visual separation
- High-resolution displays

**Component:** `DetachedLayout.vue`

### 7. Mini Sidebar
Expandable sidebar that grows on hover.

**Features:**
- Collapsed by default (64px)
- Expands to full width (256px) on hover
- Smooth transition animations
- Combines space efficiency with accessibility

**Best For:**
- Power users who know navigation
- Temporary access to full navigation
- Maximum content + navigation access

**Component:** `MiniLayout.vue`

### 8. Split View
Dashboard-focused two-pane layout.

**Features:**
- Left pane: Fixed navigation and widgets
- Right pane: Main content area
- Resizable divider between panes (optional)
- Dashboard/analytics oriented

**Best For:**
- Dashboard-heavy applications
- Data visualization interfaces
- Split-screen workflows

**Component:** `SplitLayout.vue`

## Architecture

### Backend Components

```
database/seeders/
├── SettingListsSeeder.php    # Add admin_layouts list
└── SettingsSeeder.php         # Add default_admin_layout setting

app/Services/
└── SettingsService.php        # Already handles settings (no changes needed)

app/Http/Controllers/Api/
└── SettingsController.php     # Already handles settings (no changes needed)
```

### Frontend Components

```
resources/js/
├── layouts/
│   ├── AdminLayout.vue                 # Layout switcher (updated)
│   └── admin/                          # Layout implementations
│       ├── ClassicLayout.vue           # Existing layout
│       ├── HorizontalLayout.vue
│       ├── CompactLayout.vue
│       ├── HybridLayout.vue
│       ├── BoxedLayout.vue
│       ├── DetachedLayout.vue
│       ├── MiniLayout.vue
│       └── SplitLayout.vue
├── components/
│   ├── layout/
│   │   ├── Sidebar.vue                 # Existing
│   │   ├── Navbar.vue                  # Existing
│   │   ├── HorizontalNav.vue           # New
│   │   ├── CompactSidebar.vue          # New
│   │   └── MiniSidebar.vue             # New
├── stores/
│   └── settings.js                     # Add layout methods
├── services/
│   └── settingsService.js              # Add getAdminLayouts()
├── composables/
│   └── useLayout.js                    # New - Layout utilities
└── router/
    └── index.js                        # Add layout resolution
```

### CSS Structure

```
resources/css/
├── app.css                             # Layout-specific CSS variables
└── layouts/
    ├── classic.css                     # Classic layout styles
    ├── horizontal.css                  # Horizontal layout styles
    ├── compact.css                     # Compact layout styles
    └── ... (other layouts)
```

## Backend Implementation

### 1. Update SettingListsSeeder

Add admin layout options to the setting lists.

```php
<?php
// database/seeders/SettingListsSeeder.php

namespace Database\Seeders;

use App\Models\SettingList;
use Illuminate\Database\Seeder;

class SettingListsSeeder extends Seeder
{
    public function run(): void
    {
        $settingLists = [
            // ... existing settings (themes, date_formats, etc.)

            // Admin Layouts
            [
                'key' => 'admin_layouts',
                'label' => 'Classic Sidebar',
                'value' => 'classic',
                'metadata' => json_encode([
                    'description' => 'Traditional left sidebar with top navbar',
                    'icon' => 'layout-sidebar',
                    'preview_image' => '/images/layouts/classic.png',
                    'features' => ['Collapsible sidebar', 'Top navbar', 'Traditional layout'],
                    'navigation_type' => 'vertical',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Horizontal Navigation',
                'value' => 'horizontal',
                'metadata' => json_encode([
                    'description' => 'Top horizontal menu with no sidebar',
                    'icon' => 'layout-navbar',
                    'preview_image' => '/images/layouts/horizontal.png',
                    'features' => ['Full-width content', 'Horizontal menu', 'Dropdown navigation'],
                    'navigation_type' => 'horizontal',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Compact Sidebar',
                'value' => 'compact',
                'metadata' => json_encode([
                    'description' => 'Always-collapsed icon-only sidebar',
                    'icon' => 'layout-sidebar-right',
                    'preview_image' => '/images/layouts/compact.png',
                    'features' => ['Icon-only navigation', 'Tooltip labels', 'Maximum content space'],
                    'navigation_type' => 'vertical-icons',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Hybrid (Top + Side)',
                'value' => 'hybrid',
                'metadata' => json_encode([
                    'description' => 'Horizontal top menu with contextual sidebar',
                    'icon' => 'layout-grid',
                    'preview_image' => '/images/layouts/hybrid.png',
                    'features' => ['Two-tier navigation', 'Contextual sidebar', 'Best of both worlds'],
                    'navigation_type' => 'hybrid',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 4,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Boxed Layout',
                'value' => 'boxed',
                'metadata' => json_encode([
                    'description' => 'Centered layout with constrained width',
                    'icon' => 'layout-align-center',
                    'preview_image' => '/images/layouts/boxed.png',
                    'features' => ['Max-width container', 'Centered content', 'Focused experience'],
                    'navigation_type' => 'vertical',
                    'content_width' => 'constrained',
                ]),
                'is_active' => true,
                'order' => 5,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Detached/Floating',
                'value' => 'detached',
                'metadata' => json_encode([
                    'description' => 'Modern floating card-style interface',
                    'icon' => 'layout-cards',
                    'preview_image' => '/images/layouts/detached.png',
                    'features' => ['Floating cards', 'Modern design', 'Visual separation'],
                    'navigation_type' => 'vertical',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 6,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Mini Sidebar',
                'value' => 'mini',
                'metadata' => json_encode([
                    'description' => 'Expandable sidebar that grows on hover',
                    'icon' => 'layout-sidebar-left',
                    'preview_image' => '/images/layouts/mini.png',
                    'features' => ['Hover to expand', 'Space efficient', 'Quick access'],
                    'navigation_type' => 'vertical-mini',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 7,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Split View',
                'value' => 'split',
                'metadata' => json_encode([
                    'description' => 'Two-pane dashboard layout',
                    'icon' => 'layout-columns',
                    'preview_image' => '/images/layouts/split.png',
                    'features' => ['Split panes', 'Dashboard focused', 'Resizable divider'],
                    'navigation_type' => 'split',
                    'content_width' => 'split',
                ]),
                'is_active' => true,
                'order' => 8,
            ],
        ];

        foreach ($settingLists as $settingList) {
            SettingList::updateOrCreate(
                ['key' => $settingList['key'], 'value' => $settingList['value']],
                $settingList
            );
        }
    }
}
```

### 2. Update SettingsSeeder

Add default admin layout global setting.

```php
<?php
// database/seeders/SettingsSeeder.php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SettingList;
// ... other imports

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Get default references
        $defaultTheme = SettingList::where('key', 'themes')->where('value', 'default')->first();
        $defaultLayout = SettingList::where('key', 'admin_layouts')->where('value', 'classic')->first();
        // ... other defaults

        $globalSettings = [
            // ... existing settings

            // Appearance Group (add to existing appearance settings)
            [
                'key' => 'default_admin_layout',
                'value' => json_encode('classic'),
                'type' => 'reference',
                'group' => 'appearance',
                'scope' => 'global',
                'label' => 'Default Admin Layout',
                'description' => 'Default admin panel layout for new users',
                'icon' => 'layout',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => $defaultLayout ? SettingList::class : null,
                'referenceable_id' => $defaultLayout?->id,
                'order' => 3,
            ],
        ];

        foreach ($globalSettings as $setting) {
            Setting::updateOrCreate(
                [
                    'key' => $setting['key'],
                    'scope' => $setting['scope'],
                    'settable_type' => $setting['settable_type'],
                    'settable_id' => $setting['settable_id'],
                ],
                $setting
            );
        }
    }
}
```

### 3. Create Migration

Create a migration to seed the new settings.

```bash
php artisan make:migration add_admin_layout_settings --no-interaction
```

```php
<?php
// database/migrations/YYYY_MM_DD_HHMMSS_add_admin_layout_settings.php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\SettingListsSeeder;
use Database\Seeders\SettingsSeeder;

return new class extends Migration
{
    public function up(): void
    {
        // Seed admin layout options
        $this->call(SettingListsSeeder::class);
        $this->call(SettingsSeeder::class);
    }

    public function down(): void
    {
        // Remove admin layout settings
        \App\Models\SettingList::where('key', 'admin_layouts')->delete();
        \App\Models\Setting::where('key', 'default_admin_layout')->delete();
        \App\Models\Setting::where('key', 'user_admin_layout')->delete();
    }

    private function call(string $seeder): void
    {
        (new $seeder)->run();
    }
};
```

## Frontend Implementation

### 1. Update Settings Store

Add layout-related state and methods to the settings store.

```javascript
// resources/js/stores/settings.js

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { settingsService } from '@/services/settingsService'

export const useSettingsStore = defineStore('settings', () => {
  // State
  const userSettings = ref({})
  const globalSettings = ref({})
  const themes = ref([])
  const adminLayouts = ref([])  // NEW
  const countries = ref([])
  const timezones = ref([])
  const isLoading = ref(false)
  const isSaving = ref(false)

  // Getters
  const currentTheme = computed(() => userSettings.value.user_theme || 'default')
  const currentAdminLayout = computed(() => userSettings.value.user_admin_layout || 'classic')  // NEW
  const notificationsEnabled = computed(() => userSettings.value.notifications_enabled ?? true)
  const itemsPerPage = computed(() => userSettings.value.items_per_page || 25)

  // Actions

  /**
   * Load available admin layouts
   */
  async function loadAdminLayouts() {
    try {
      const response = await settingsService.getSettingLists('admin_layouts')
      adminLayouts.value = response.lists
      return response
    } catch (error) {
      console.error('Failed to load admin layouts:', error)
      throw error
    }
  }

  /**
   * Apply layout to DOM
   * @param {string} layoutName - Layout name
   */
  function applyLayout(layoutName) {
    // Remove all existing layout classes
    const layoutNames = ['classic', 'horizontal', 'compact', 'hybrid', 'boxed', 'detached', 'mini', 'split']
    layoutNames.forEach(layout => {
      document.documentElement.classList.remove(`layout-${layout}`)
    })

    // Add new layout class
    document.documentElement.classList.add(`layout-${layoutName}`)
    document.documentElement.setAttribute('data-layout', layoutName)
  }

  /**
   * Update user layout preference
   * @param {string} layoutName - Layout name
   */
  async function updateUserLayout(layoutName) {
    isSaving.value = true
    try {
      const response = await settingsService.updateUserSetting('user_admin_layout', layoutName)
      // Update local state
      userSettings.value.user_admin_layout = layoutName

      // Save to localStorage and apply to DOM
      localStorage.setItem('adminLayout', layoutName)
      applyLayout(layoutName)

      return response
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Initialize layout from settings or localStorage
   */
  async function initLayout() {
    try {
      // Try to get layout from user settings first
      if (userSettings.value.user_admin_layout) {
        const layout = userSettings.value.user_admin_layout
        localStorage.setItem('adminLayout', layout)
        applyLayout(layout)
      } else {
        // Fallback to localStorage
        const layout = localStorage.getItem('adminLayout') || 'classic'
        applyLayout(layout)
      }
    } catch (error) {
      console.error('Failed to initialize layout:', error)
      applyLayout('classic')
    }
  }

  /**
   * Reset settings store
   */
  function resetSettings() {
    userSettings.value = {}
    globalSettings.value = {}
    themes.value = []
    adminLayouts.value = []  // NEW
    countries.value = []
    timezones.value = []
  }

  return {
    // State
    userSettings,
    globalSettings,
    themes,
    adminLayouts,  // NEW
    countries,
    timezones,
    isLoading,
    isSaving,

    // Getters
    currentTheme,
    currentAdminLayout,  // NEW
    notificationsEnabled,
    itemsPerPage,

    // Actions
    loadUserSettings,
    updateUserSetting,
    updateUserSettings,
    loadGlobalSettings,
    updateGlobalSetting,
    loadThemes,
    loadAdminLayouts,  // NEW
    loadCountries,
    loadTimezones,
    applyTheme,
    applyLayout,  // NEW
    updateUserLayout,  // NEW
    initTheme,
    initLayout,  // NEW
    resetSettings
  }
})
```

### 2. Update Settings Service

Add method to fetch admin layouts.

```javascript
// resources/js/services/settingsService.js

import api from '@/utils/api'

export const settingsService = {
  // ... existing methods

  /**
   * Get admin layouts list
   * @returns {Promise}
   */
  async getAdminLayouts() {
    const response = await api.get('/api/settings/lists/admin_layouts')
    return response.data
  },

  // Alias for consistency
  async getSettingLists(key) {
    const response = await api.get(`/api/settings/lists/${key}`)
    return response.data
  },
}
```

### 3. Create Layout Composable

Utility functions for layout management.

```javascript
// resources/js/composables/useLayout.js

import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '@/stores/settings'

export function useLayout() {
  const settingsStore = useSettingsStore()
  const { currentAdminLayout, adminLayouts } = storeToRefs(settingsStore)

  /**
   * Get current layout configuration
   */
  const currentLayoutConfig = computed(() => {
    return adminLayouts.value.find(l => l.value === currentAdminLayout.value)
  })

  /**
   * Check if current layout has sidebar
   */
  const hasSidebar = computed(() => {
    const navType = currentLayoutConfig.value?.metadata?.navigation_type
    return ['vertical', 'vertical-icons', 'vertical-mini', 'hybrid'].includes(navType)
  })

  /**
   * Check if current layout has horizontal navigation
   */
  const hasHorizontalNav = computed(() => {
    const navType = currentLayoutConfig.value?.metadata?.navigation_type
    return ['horizontal', 'hybrid'].includes(navType)
  })

  /**
   * Check if content is constrained width
   */
  const isContentConstrained = computed(() => {
    const contentWidth = currentLayoutConfig.value?.metadata?.content_width
    return contentWidth === 'constrained'
  })

  /**
   * Switch to a different layout
   */
  async function switchLayout(layoutName) {
    return await settingsStore.updateUserLayout(layoutName)
  }

  return {
    currentAdminLayout,
    currentLayoutConfig,
    hasSidebar,
    hasHorizontalNav,
    isContentConstrained,
    switchLayout,
  }
}
```

### 4. Update AdminLayout Component

Convert to a layout switcher that dynamically loads the appropriate layout.

```vue
<!-- resources/js/layouts/AdminLayout.vue -->
<template>
  <component :is="currentLayoutComponent" />
</template>

<script setup>
import { computed, onMounted, defineAsyncComponent } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { storeToRefs } from 'pinia'

const settingsStore = useSettingsStore()
const { currentAdminLayout } = storeToRefs(settingsStore)

// Layout component mapping
const layoutComponents = {
  classic: defineAsyncComponent(() => import('./admin/ClassicLayout.vue')),
  horizontal: defineAsyncComponent(() => import('./admin/HorizontalLayout.vue')),
  compact: defineAsyncComponent(() => import('./admin/CompactLayout.vue')),
  hybrid: defineAsyncComponent(() => import('./admin/HybridLayout.vue')),
  boxed: defineAsyncComponent(() => import('./admin/BoxedLayout.vue')),
  detached: defineAsyncComponent(() => import('./admin/DetachedLayout.vue')),
  mini: defineAsyncComponent(() => import('./admin/MiniLayout.vue')),
  split: defineAsyncComponent(() => import('./admin/SplitLayout.vue')),
}

// Dynamically load the current layout component
const currentLayoutComponent = computed(() => {
  return layoutComponents[currentAdminLayout.value] || layoutComponents.classic
})

// Initialize layout on mount
onMounted(async () => {
  await settingsStore.initLayout()
})
</script>
```

### 5. Create Layout Components

#### Classic Layout (Move existing AdminLayout.vue)

```vue
<!-- resources/js/layouts/admin/ClassicLayout.vue -->
<template>
  <div class="page-container font-sans">
    <!-- Mobile Overlay -->
    <div
      v-if="isMobileSidebarOpen"
      @click="closeMobileSidebar"
      class="mobile-overlay"
      :class="{ 'opacity-100': isMobileSidebarOpen, 'opacity-0': !isMobileSidebarOpen }"
    ></div>

    <!-- Sidebar -->
    <Sidebar
      :collapsed="isDesktopSidebarCollapsed"
      :is-open="isMobileSidebarOpen"
      :is-mobile="isMobile"
      @close="closeMobileSidebar"
      @nav-click="closeMobileSidebarOnNavigation"
      @logout="logout"
    />

    <!-- Main Content -->
    <div class="content-area" :class="mainContentClasses">
      <!-- Top Navbar -->
      <Navbar
        :user="user"
        :notification-count="notificationCount"
        @toggle-sidebar="toggleDesktopSidebar"
        @toggle-mobile-sidebar="toggleMobileSidebar"
        @logout="logout"
        @search="handleSearch"
      />

      <!-- Main Content Area -->
      <main class="p-4 sm:p-6 lg:p-8 content-area">
        <router-view />
      </main>
    </div>

    <!-- Confirmation Dialog Container -->
    <ConfirmDialogContainer />
  </div>
</template>

<script setup>
// ... (use existing AdminLayout.vue logic)
</script>
```

#### Horizontal Layout

```vue
<!-- resources/js/layouts/admin/HorizontalLayout.vue -->
<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Top Navigation -->
    <HorizontalNav
      :user="user"
      :notification-count="notificationCount"
      @logout="logout"
      @search="handleSearch"
    />

    <!-- Main Content -->
    <main class="p-4 sm:p-6 lg:p-8 mt-16">
      <router-view />
    </main>

    <ConfirmDialogContainer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import HorizontalNav from '@/components/layout/HorizontalNav.vue'
import ConfirmDialogContainer from '@/components/common/ConfirmDialogContainer.vue'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const notificationCount = ref(3)

const user = computed(() => authStore.user ? {
  name: authStore.user.name,
  role: 'User',
  initials: authStore.user.name.charAt(0).toUpperCase()
} : {
  name: 'User',
  role: 'User',
  initials: 'U'
})

const logout = async () => {
  try {
    await authStore.logout()
    router.push({ name: 'auth.login' })
  } catch (error) {
    console.error('Logout error:', error)
    router.push({ name: 'auth.login' })
  }
}

const handleSearch = (query) => {
  console.log('Search query:', query)
}

onMounted(async () => {
  if (!authStore.user && authStore.token) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      console.error('Failed to fetch user:', error)
    }
  }
})
</script>
```

#### Compact Layout

```vue
<!-- resources/js/layouts/admin/CompactLayout.vue -->
<template>
  <div class="page-container font-sans">
    <!-- Compact Sidebar (always collapsed, icons only) -->
    <CompactSidebar
      @nav-click="handleNavClick"
      @logout="logout"
    />

    <!-- Main Content -->
    <div class="content-area pl-16">
      <!-- Top Navbar -->
      <Navbar
        :user="user"
        :notification-count="notificationCount"
        :hide-toggle="true"
        @logout="logout"
        @search="handleSearch"
      />

      <!-- Main Content Area -->
      <main class="p-4 sm:p-6 lg:p-8">
        <router-view />
      </main>
    </div>

    <ConfirmDialogContainer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import CompactSidebar from '@/components/layout/CompactSidebar.vue'
import Navbar from '@/components/layout/Navbar.vue'
import ConfirmDialogContainer from '@/components/common/ConfirmDialogContainer.vue'
import { useAuthStore } from '@/stores/auth'

// Similar setup to ClassicLayout but without sidebar toggle logic
</script>
```

#### Mini Layout

```vue
<!-- resources/js/layouts/admin/MiniLayout.vue -->
<template>
  <div class="page-container font-sans">
    <!-- Mini Sidebar (expands on hover) -->
    <MiniSidebar
      @nav-click="handleNavClick"
      @logout="logout"
    />

    <!-- Main Content -->
    <div class="content-area pl-16 hover-expand-sidebar">
      <!-- Top Navbar -->
      <Navbar
        :user="user"
        :notification-count="notificationCount"
        :hide-toggle="true"
        @logout="logout"
        @search="handleSearch"
      />

      <!-- Main Content Area -->
      <main class="p-4 sm:p-6 lg:p-8">
        <router-view />
      </main>
    </div>

    <ConfirmDialogContainer />
  </div>
</template>

<script setup>
// Similar to CompactLayout but with hover expansion
</script>

<style scoped>
.hover-expand-sidebar:has(~ .mini-sidebar:hover) {
  padding-left: 16rem; /* Expanded sidebar width */
  transition: padding-left 0.3s ease;
}
</style>
```

### 6. Create Navigation Components

#### Horizontal Navigation Component

```vue
<!-- resources/js/components/layout/HorizontalNav.vue -->
<template>
  <nav class="fixed top-0 left-0 right-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
            <Icon name="shield" class="w-5 h-5 text-white" />
          </div>
          <span class="text-xl font-bold text-gray-900 dark:text-white">Admin</span>
        </div>

        <!-- Horizontal Menu -->
        <div class="hidden md:flex items-center space-x-1">
          <NavMenuItem
            v-for="item in menuItems"
            :key="item.to"
            :item="item"
          />
        </div>

        <!-- Right Actions -->
        <div class="flex items-center space-x-4">
          <DarkModeToggle />
          <UserDropdown :user="user" @logout="$emit('logout')" />
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import Icon from '@/components/common/Icon.vue'
import DarkModeToggle from '@/components/common/DarkModeToggle.vue'
import UserDropdown from '@/components/layout/UserDropdown.vue'
import NavMenuItem from '@/components/layout/NavMenuItem.vue'

defineProps({
  user: Object,
  notificationCount: Number
})

defineEmits(['logout', 'search'])

const menuItems = ref([
  { to: { name: 'admin.dashboard' }, label: 'Dashboard', icon: 'dashboard' },
  { to: { name: 'admin.users' }, label: 'Users', icon: 'users' },
  { to: { name: 'admin.roles' }, label: 'Roles', icon: 'shield' },
  { to: { name: 'admin.countries' }, label: 'Countries', icon: 'globe' },
  { to: { name: 'admin.timezones' }, label: 'Timezones', icon: 'clock' },
  { to: { name: 'settings.index' }, label: 'Settings', icon: 'settings' },
])
</script>
```

#### Compact Sidebar Component

```vue
<!-- resources/js/components/layout/CompactSidebar.vue -->
<template>
  <aside class="fixed inset-y-0 left-0 z-50 w-16 bg-gradient-to-b from-primary-600 to-secondary-600 dark:from-primary-800 dark:to-secondary-800">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 border-b border-white/10">
      <div class="w-8 h-8 bg-white dark:bg-gray-100 rounded-lg flex items-center justify-center">
        <Icon name="shield" class="w-5 h-5 text-primary-600" />
      </div>
    </div>

    <!-- Navigation Items -->
    <nav class="flex flex-col items-center py-4 space-y-2">
      <CompactNavItem
        v-for="item in menuItems"
        :key="item.to"
        :to="item.to"
        :icon="item.icon"
        :label="item.label"
        v-tooltip="item.label"
      />
    </nav>

    <!-- Logout Button -->
    <div class="absolute bottom-4 left-0 right-0 flex justify-center">
      <button
        @click="$emit('logout')"
        v-tooltip="'Logout'"
        class="p-3 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-all"
      >
        <Icon name="logout" :size="20" />
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import Icon from '@/components/common/Icon.vue'
import CompactNavItem from '@/components/layout/CompactNavItem.vue'

defineEmits(['nav-click', 'logout'])

const menuItems = ref([
  { to: { name: 'admin.dashboard' }, icon: 'dashboard', label: 'Dashboard' },
  { to: { name: 'admin.users' }, icon: 'users', label: 'Users' },
  { to: { name: 'admin.roles' }, icon: 'shield', label: 'Roles' },
  { to: { name: 'settings.index' }, icon: 'settings', label: 'Settings' },
])
</script>
```

### 7. Update App Initialization

Initialize layout on app mount.

```javascript
// resources/js/spa.js (or main app file)

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useSettingsStore } from './stores/settings'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Initialize layout
const settingsStore = useSettingsStore()
settingsStore.initLayout()

app.mount('#app')
```

### 8. Update Appearance Settings Page

Add layout selector to the appearance settings page.

```vue
<!-- resources/js/pages/settings/Appearance.vue -->
<template>
  <div class="space-y-6">
    <!-- Theme Selection (existing) -->
    <SettingGroup title="Theme" description="Choose your color theme">
      <!-- ... existing theme selector -->
    </SettingGroup>

    <!-- Layout Selection (NEW) -->
    <SettingGroup title="Admin Layout" description="Choose your preferred admin panel layout">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <LayoutOption
          v-for="layout in adminLayouts"
          :key="layout.value"
          :layout="layout"
          :is-active="currentAdminLayout === layout.value"
          @select="selectLayout(layout.value)"
        />
      </div>
    </SettingGroup>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '@/stores/settings'
import { useToast } from '@/composables/useToast'
import SettingGroup from '@/components/settings/SettingGroup.vue'
import LayoutOption from '@/components/settings/LayoutOption.vue'

const settingsStore = useSettingsStore()
const toast = useToast()

const { adminLayouts, currentAdminLayout } = storeToRefs(settingsStore)

async function selectLayout(layoutValue) {
  try {
    await settingsStore.updateUserLayout(layoutValue)
    toast.success('Layout updated successfully!')
  } catch (error) {
    toast.error('Failed to update layout')
  }
}

onMounted(async () => {
  await settingsStore.loadAdminLayouts()
})
</script>
```

#### Layout Option Component

```vue
<!-- resources/js/components/settings/LayoutOption.vue -->
<template>
  <div
    @click="$emit('select')"
    class="relative cursor-pointer rounded-lg border-2 p-4 transition-all hover:shadow-md"
    :class="isActive
      ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20'
      : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
    "
  >
    <!-- Active Indicator -->
    <div
      v-if="isActive"
      class="absolute top-2 right-2 w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center"
    >
      <Icon name="check" class="w-4 h-4 text-white" />
    </div>

    <!-- Layout Preview (if available) -->
    <div v-if="layout.metadata.preview_image" class="mb-3 rounded overflow-hidden">
      <img
        :src="layout.metadata.preview_image"
        :alt="layout.label"
        class="w-full h-32 object-cover"
      />
    </div>

    <!-- Layout Icon (fallback) -->
    <div v-else class="mb-3 flex justify-center">
      <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
        <Icon :name="layout.metadata.icon" :size="32" class="text-gray-400" />
      </div>
    </div>

    <!-- Layout Info -->
    <div class="text-center">
      <h3 class="font-semibold text-gray-900 dark:text-white mb-1">
        {{ layout.label }}
      </h3>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
        {{ layout.metadata.description }}
      </p>

      <!-- Features -->
      <div class="flex flex-wrap gap-1 justify-center">
        <span
          v-for="(feature, index) in layout.metadata.features?.slice(0, 2)"
          :key="index"
          class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-gray-600 dark:text-gray-400"
        >
          {{ feature }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import Icon from '@/components/common/Icon.vue'

defineProps({
  layout: {
    type: Object,
    required: true
  },
  isActive: {
    type: Boolean,
    default: false
  }
})

defineEmits(['select'])
</script>
```

## CSS Styling

### Layout-Specific CSS Variables

Add layout-specific styling to `resources/css/app.css`.

```css
/* Layout-specific CSS variables */

/* Classic Layout (default) */
[data-layout='classic'] {
  --sidebar-width: 16rem;
  --sidebar-collapsed-width: 4rem;
  --navbar-height: 4rem;
}

/* Horizontal Layout */
[data-layout='horizontal'] {
  --navbar-height: 4rem;
}

/* Compact Layout */
[data-layout='compact'] {
  --sidebar-width: 4rem;
  --navbar-height: 4rem;
}

/* Hybrid Layout */
[data-layout='hybrid'] {
  --top-nav-height: 4rem;
  --sidebar-width: 16rem;
}

/* Boxed Layout */
[data-layout='boxed'] {
  --container-max-width: 1280px;
  --sidebar-width: 16rem;
  --navbar-height: 4rem;
}

/* Detached Layout */
[data-layout='detached'] {
  --sidebar-width: 16rem;
  --navbar-height: 4rem;
  --card-spacing: 1rem;
}

/* Mini Layout */
[data-layout='mini'] {
  --sidebar-width-collapsed: 4rem;
  --sidebar-width-expanded: 16rem;
  --navbar-height: 4rem;
}

/* Split Layout */
[data-layout='split'] {
  --left-pane-width: 20rem;
  --divider-width: 0.25rem;
}
```

## Usage Examples

### In Components

```vue
<script setup>
import { useLayout } from '@/composables/useLayout'

const { currentAdminLayout, hasSidebar, switchLayout } = useLayout()

async function changeToHorizontal() {
  await switchLayout('horizontal')
}
</script>
```

### In Templates

```vue
<template>
  <div v-if="hasSidebar">
    <!-- Sidebar-specific content -->
  </div>

  <div v-else>
    <!-- Non-sidebar layout content -->
  </div>
</template>
```

### Programmatic Layout Switch

```javascript
import { useSettingsStore } from '@/stores/settings'

const settingsStore = useSettingsStore()

// Switch layout
await settingsStore.updateUserLayout('compact')

// Get current layout
console.log(settingsStore.currentAdminLayout)
```

## Testing Strategy

### Backend Tests

```php
// tests/Feature/AdminLayoutSettingsTest.php

class AdminLayoutSettingsTest extends TestCase
{
    public function test_can_get_admin_layouts_list()
    {
        $response = $this->getJson('/api/settings/lists/admin_layouts');

        $response->assertOk()
            ->assertJsonStructure([
                'lists' => [
                    '*' => ['key', 'label', 'value', 'metadata']
                ]
            ]);
    }

    public function test_user_can_update_layout_preference()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/user/settings/user_admin_layout', [
                'value' => 'horizontal'
            ]);

        $response->assertOk();
        $this->assertEquals('horizontal', $user->getSetting('user_admin_layout'));
    }

    public function test_admin_can_set_default_layout()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->putJson('/api/settings/default_admin_layout', [
                'value' => 'compact'
            ]);

        $response->assertOk();
    }
}
```

### Frontend Tests

```javascript
// tests/unit/stores/settings.layout.test.js

import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useSettingsStore } from '@/stores/settings'

describe('Settings Store - Layout Management', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('applies layout to DOM', () => {
    const store = useSettingsStore()

    store.applyLayout('horizontal')

    expect(document.documentElement.classList.contains('layout-horizontal')).toBe(true)
    expect(document.documentElement.getAttribute('data-layout')).toBe('horizontal')
  })

  it('removes old layout class when applying new one', () => {
    const store = useSettingsStore()

    store.applyLayout('classic')
    store.applyLayout('compact')

    expect(document.documentElement.classList.contains('layout-classic')).toBe(false)
    expect(document.documentElement.classList.contains('layout-compact')).toBe(true)
  })

  it('loads admin layouts from API', async () => {
    const store = useSettingsStore()

    await store.loadAdminLayouts()

    expect(store.adminLayouts).toHaveLength(8)
  })
})
```

```javascript
// tests/integration/layout/LayoutSwitching.test.js

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import AdminLayout from '@/layouts/AdminLayout.vue'

describe('Layout Switching', () => {
  it('loads correct layout component based on setting', async () => {
    const wrapper = mount(AdminLayout, {
      global: {
        plugins: [createTestingPinia({
          initialState: {
            settings: {
              userSettings: { user_admin_layout: 'horizontal' }
            }
          }
        })]
      }
    })

    await wrapper.vm.$nextTick()

    // Should load HorizontalLayout component
    expect(wrapper.html()).toContain('horizontal-nav')
  })
})
```

### E2E Tests

```javascript
// tests/e2e/layout/layout-switching.spec.js

import { test, expect } from '@playwright/test'

test.describe('Admin Layout Switching', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/settings/appearance')
  })

  test('can switch between layouts', async ({ page }) => {
    // Click horizontal layout option
    await page.click('[data-layout-option="horizontal"]')

    // Wait for layout to change
    await page.waitForSelector('[data-layout="horizontal"]')

    // Verify sidebar is not present
    await expect(page.locator('.sidebar')).not.toBeVisible()

    // Verify horizontal nav is present
    await expect(page.locator('.horizontal-nav')).toBeVisible()
  })

  test('layout preference persists across page reload', async ({ page }) => {
    // Switch to compact layout
    await page.click('[data-layout-option="compact"]')
    await page.waitForSelector('[data-layout="compact"]')

    // Reload page
    await page.reload()

    // Verify compact layout is still active
    await expect(page.locator('[data-layout="compact"]')).toBeVisible()
  })

  test('all layouts render correctly', async ({ page }) => {
    const layouts = ['classic', 'horizontal', 'compact', 'hybrid', 'boxed', 'detached', 'mini', 'split']

    for (const layout of layouts) {
      await page.click(`[data-layout-option="${layout}"]`)
      await page.waitForSelector(`[data-layout="${layout}"]`)
      await expect(page.locator(`[data-layout="${layout}"]`)).toBeVisible()
    }
  })
})
```

## Migration Guide

For existing applications upgrading to the multi-layout system:

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Update AdminLayout.vue:**
   - Move current `AdminLayout.vue` to `layouts/admin/ClassicLayout.vue`
   - Replace `AdminLayout.vue` with the layout switcher

3. **Load layouts in app:**
   ```javascript
   const settingsStore = useSettingsStore()
   await settingsStore.loadAdminLayouts()
   await settingsStore.initLayout()
   ```

4. **Set default for existing users:**
   ```php
   // In a migration or seeder
   User::chunk(100, function ($users) {
       foreach ($users as $user) {
           $user->settings()->create([
               'key' => 'user_admin_layout',
               'value' => json_encode('classic'),
               'scope' => 'user',
           ]);
       }
   });
   ```

## Future Enhancements

1. **Layout Customization:**
   - Allow users to customize sidebar width, navbar height, etc.
   - Save custom layout configurations

2. **Layout Templates:**
   - Create layout templates for specific use cases
   - Industry-specific layouts (e-commerce, SaaS, etc.)

3. **Layout Preview:**
   - Live preview before applying layout
   - Screenshot/video demos of each layout

4. **Responsive Layouts:**
   - Different layouts for mobile/tablet/desktop
   - Automatic layout switching based on screen size

5. **Layout Analytics:**
   - Track which layouts are most popular
   - Usage metrics per layout

6. **Export/Import:**
   - Export layout configurations
   - Share layouts between users/teams

## File Checklist

### Backend
- [ ] Update `database/seeders/SettingListsSeeder.php` (add admin_layouts)
- [ ] Update `database/seeders/SettingsSeeder.php` (add default_admin_layout)
- [ ] Create migration for admin layout settings
- [ ] Write backend tests

### Frontend
- [ ] Update `resources/js/stores/settings.js` (add layout methods)
- [ ] Update `resources/js/services/settingsService.js` (add getAdminLayouts)
- [ ] Create `resources/js/composables/useLayout.js`
- [ ] Update `resources/js/layouts/AdminLayout.vue` (convert to switcher)
- [ ] Create `resources/js/layouts/admin/ClassicLayout.vue`
- [ ] Create `resources/js/layouts/admin/HorizontalLayout.vue`
- [ ] Create `resources/js/layouts/admin/CompactLayout.vue`
- [ ] Create `resources/js/layouts/admin/HybridLayout.vue`
- [ ] Create `resources/js/layouts/admin/BoxedLayout.vue`
- [ ] Create `resources/js/layouts/admin/DetachedLayout.vue`
- [ ] Create `resources/js/layouts/admin/MiniLayout.vue`
- [ ] Create `resources/js/layouts/admin/SplitLayout.vue`
- [ ] Create `resources/js/components/layout/HorizontalNav.vue`
- [ ] Create `resources/js/components/layout/CompactSidebar.vue`
- [ ] Create `resources/js/components/layout/MiniSidebar.vue`
- [ ] Create `resources/js/components/settings/LayoutOption.vue`
- [ ] Update `resources/js/pages/settings/Appearance.vue`
- [ ] Update `resources/css/app.css` (add layout CSS variables)
- [ ] Write frontend tests (unit, integration, E2E)

## Estimated Implementation Time

- Backend setup: 1 hour
- Settings store/service updates: 1 hour
- Layout switcher: 30 minutes
- Classic layout (move existing): 30 minutes
- Horizontal layout: 2 hours
- Compact layout: 1.5 hours
- Hybrid layout: 2.5 hours
- Boxed layout: 1 hour
- Detached layout: 2 hours
- Mini layout: 1.5 hours
- Split layout: 2 hours
- Navigation components: 2 hours
- Settings UI: 1.5 hours
- CSS styling: 2 hours
- Testing: 3 hours
- **Total: ~24-28 hours**

## Support & Maintenance

**Common Issues:**

1. **Layout not changing:**
   - Clear browser cache
   - Check localStorage
   - Verify setting is saved in database

2. **Layout breaks on mobile:**
   - Ensure responsive classes are applied
   - Test all breakpoints

3. **Navigation items not showing:**
   - Check route permissions
   - Verify navigation item configuration

**Best Practices:**

- Always test layouts on multiple screen sizes
- Ensure keyboard navigation works
- Test with screen readers
- Verify theme compatibility
- Document any custom modifications
