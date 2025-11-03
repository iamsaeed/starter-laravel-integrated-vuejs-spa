# User Panel Implementation Guide

## Overview

This guide describes how to implement a user-level panel (`/user`) alongside the existing admin panel (`/admin`) while reusing all existing layouts and components.

## Architecture

### Current State
- **Admin Panel**: `/admin` routes with `requiresAdmin: true` meta
- **Components**: Reusable layouts (Classic, Compact, Mini, Horizontal)
- **Role System**: Users have roles via `role_user` pivot table
- **Admin Whitelist**: `config/admin.php` contains hardcoded user IDs allowed to access admin panel

### Target State
- **Admin Panel**: `/admin` → Admin-only access with **two-factor authorization**:
  1. User must have 'admin' role
  2. User ID must be in `config('admin.id')` whitelist
- **User Panel**: `/user` → Regular user access (authenticated users)
- **Shared Components**: 100% reuse of layouts, sidebars, and components
- **Key Difference**: Menu items filtered by role, admin access enforced by whitelist

## Implementation Plan

### 1. Add User Role Helper to User Model (2 min)

**File:** `app/Models/User.php`

Add the following method after `isAdmin()`:

```php
/**
 * Check if user is a regular user (not admin).
 */
public function isUser(): bool
{
    return $this->hasRole('user');
}

/**
 * Check if user can access admin panel.
 * Requires both admin role AND user ID to be in the whitelist.
 */
public function canAccessAdminPanel(): bool
{
    $allowedAdminIds = config('admin.id', []);

    return $this->isAdmin() && in_array($this->id, $allowedAdminIds);
}
```

**Update:** `app/Http/Resources/UserResource.php` to expose admin panel access:

```php
public function toArray(Request $request): array
{
    return [
        // ... existing fields
        'is_user' => $this->whenLoaded('roles', function () {
            return $this->isUser();
        }),
        'can_access_admin_panel' => $this->whenLoaded('roles', function () {
            return $this->canAccessAdminPanel();
        }),
    ];
}
```

**Note:** The `can_access_admin_panel` field checks:
1. User has 'admin' role (`isAdmin()`)
2. User ID exists in `config('admin.id')` array (hardcoded whitelist in `config/admin.php`)

### 2. Update Router - Add User Panel Routes (10 min)

**File:** `resources/js/router/index.js`

Add new route group for user panel:

```javascript
const routes = [
  // ... existing routes

  // User Panel Routes (NEW)
  { path: '/user', component: AdminLayout, meta: { requiresAuth: true, requiresUser: true }, children: [
    { path: 'dashboard', name: 'user.dashboard', component: () => import('@/pages/user/Dashboard.vue'), meta: { title: 'Dashboard' } },
    {
      path: 'profile',
      component: ProfileLayout,
      redirect: { name: 'user.profile.personal' },
      children: [
        { path: '', name: 'user.profile.personal', component: () => import('@/pages/profile/Profile.vue'), meta: { title: 'Profile' } },
        { path: 'security', name: 'user.profile.security', component: () => import('@/pages/profile/ChangePassword.vue'), meta: { title: 'Security' } }
      ]
    },
    {
      path: 'settings',
      component: SettingsLayout,
      redirect: { name: 'user.settings.appearance' },
      children: [
        { path: 'appearance', name: 'user.settings.appearance', component: () => import('@/pages/settings/Appearance.vue'), meta: { title: 'Appearance Settings' } },
        { path: 'notifications', name: 'user.settings.notifications', component: () => import('@/pages/settings/Notifications.vue'), meta: { title: 'Notification Settings' } },
        { path: 'preferences', name: 'user.settings.preferences', component: () => import('@/pages/settings/Preferences.vue'), meta: { title: 'Preferences' } },
      ]
    },
    { path: 'error/404', name: 'user.error.notFound', component: () => import('@/pages/errors/NotFound.vue'), meta: { title: '404 - Page Not Found' } },
    { path: 'error/403', name: 'user.error.forbidden', component: () => import('@/pages/errors/Forbidden.vue'), meta: { title: '403 - Access Forbidden' } },
  ]},

  // ... rest of routes
]
```

Update router guard to handle `requiresUser`:

```javascript
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()

  // Update document title
  if (to.meta.title) {
    const suffix = to.path.startsWith('/admin') ? 'Admin Panel' :
                   to.path.startsWith('/user') ? 'User Dashboard' :
                   'Laravel Starter'
    document.title = `${to.meta.title} - ${suffix}`
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      if (authStore.token) {
        try {
          await authStore.fetchUser()

          // Check admin panel access (requires role AND whitelist)
          if (to.meta.requiresAdmin && !authStore.user?.can_access_admin_panel) {
            next({ name: to.path.startsWith('/admin') ? 'admin.error.forbidden' : 'user.error.forbidden' })
            return
          }

          // Check user requirement (block admins from user-only routes if needed)
          if (to.meta.requiresUser && authStore.user?.can_access_admin_panel) {
            // Optional: Redirect admins to admin panel or allow access
            // Uncomment next line to block admins from user panel:
            // next({ name: 'admin.dashboard' })
            // return
          }

          next()
        } catch (error) {
          next({ name: 'auth.login', query: { redirect: to.fullPath } })
        }
      } else {
        next({ name: 'auth.login', query: { redirect: to.fullPath } })
      }
    } else {
      // Check admin panel access for authenticated users (requires role AND whitelist)
      if (to.meta.requiresAdmin && !authStore.user?.can_access_admin_panel) {
        next({ name: to.path.startsWith('/admin') ? 'admin.error.forbidden' : 'user.error.forbidden' })
        return
      }
      next()
    }
  }
  // ... rest of guard logic
})
```

### 3. Make Sidebar Menu Items Role-Aware (15 min)

**Option A: Make `menuItems` a Prop (Recommended)**

This approach makes components truly reusable and testable.

#### Step 3.1: Update Sidebar.vue

**File:** `resources/js/components/layout/Sidebar.vue`

```javascript
export default {
  name: 'Sidebar',
  components: { Icon, NavItem },
  emits: ['close', 'logout', 'nav-click'],
  props: {
    // ... existing props
    mainMenuItems: {
      type: Array,
      default: () => []
    },
    moreMenuItems: {
      type: Array,
      default: () => []
    }
  },
  setup(props, { emit }) {
    // Remove computed menuItems - use props instead

    const handleNavItemClick = () => {
      emit('nav-click')
    }

    const handleActionItemClick = (item) => {
      if (item.action) {
        emit(item.action)
      } else {
        handleNavItemClick()
      }
    }

    return {
      // mainMenuItems and moreMenuItems now come from props
      handleNavItemClick,
      handleActionItemClick,
    }
  },
}
```

#### Step 3.2: Update CompactSidebar.vue

**File:** `resources/js/components/layout/CompactSidebar.vue`

Same changes as Sidebar.vue - add `mainMenuItems` and `moreMenuItems` props, remove computed menuItems.

#### Step 3.3: Update MiniSidebar.vue

**File:** `resources/js/components/layout/MiniSidebar.vue`

Same changes - add `mainMenuItems` and `moreMenuItems` props.

#### Step 3.4: Update HorizontalNav.vue

**File:** `resources/js/components/layout/HorizontalNav.vue`

Add `menuItems` prop (HorizontalNav uses a single menu array).

#### Step 3.5: Create Menu Configuration File

**File:** `resources/js/config/menuItems.js`

```javascript
// Admin menu items
export const adminMainMenuItems = [
  { to: { name: 'admin.dashboard' }, icon: 'dashboard', label: 'Dashboard', exactMatch: true },
  { to: { name: 'admin.users' }, icon: 'team', label: 'Users' },
  { to: { name: 'admin.roles' }, icon: 'shield', label: 'Roles' },
  { to: { name: 'admin.countries' }, icon: 'globe', label: 'Countries' },
  { to: { name: 'admin.timezones' }, icon: 'clock', label: 'Timezones' },
  { to: { name: 'admin.email-templates.index' }, icon: 'mail', label: 'Email Templates' },
]

export const adminMoreMenuItems = [
  { to: { name: 'profile.personal' }, icon: 'profile', label: 'Profile' },
  { to: { name: 'settings.appearance' }, icon: 'settings', label: 'Settings' },
]

// User menu items
export const userMainMenuItems = [
  { to: { name: 'user.dashboard' }, icon: 'dashboard', label: 'Dashboard', exactMatch: true },
  // Add user-specific menu items here
]

export const userMoreMenuItems = [
  { to: { name: 'user.profile.personal' }, icon: 'profile', label: 'Profile' },
  { to: { name: 'user.settings.appearance' }, icon: 'settings', label: 'Settings' },
]
```

#### Step 3.6: Update Layout Components to Pass Menu Items

**File:** `resources/js/layouts/admin/ClassicLayout.vue`

```javascript
<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Sidebar from '@/components/layout/Sidebar.vue'
import Navbar from '@/components/layout/Navbar.vue'
import { adminMainMenuItems, adminMoreMenuItems, userMainMenuItems, userMoreMenuItems } from '@/config/menuItems'

export default {
  components: { Sidebar, Navbar },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    // Determine menu items based on current route
    const currentPath = computed(() => router.currentRoute.value.path)
    const isAdminPanel = computed(() => currentPath.value.startsWith('/admin'))

    const mainMenuItems = computed(() =>
      isAdminPanel.value ? adminMainMenuItems : userMainMenuItems
    )

    const moreMenuItems = computed(() =>
      isAdminPanel.value ? adminMoreMenuItems : userMoreMenuItems
    )

    // ... rest of component logic

    return {
      mainMenuItems,
      moreMenuItems,
      // ... rest of returns
    }
  }
}
</script>

<template>
  <!-- ... -->
  <Sidebar
    :main-menu-items="mainMenuItems"
    :more-menu-items="moreMenuItems"
    <!-- ... other props -->
  />
  <!-- ... -->
</template>
```

Repeat for `CompactLayout.vue`, `MiniLayout.vue`, and `HorizontalLayout.vue`.

### 4. Update Login Redirect Logic (5 min)

**File:** `resources/js/stores/auth.js`

Update the login action:

```javascript
async login(credentials) {
  try {
    const data = await authService.login(credentials)
    this.token = data.token
    this.user = data.user

    // Role-based redirect (checks whitelist AND admin role)
    const redirectPath = this.user.can_access_admin_panel
      ? '/admin/dashboard'
      : '/user/dashboard'

    router.push(redirectPath)

    showToast({ message: 'Login successful!', type: 'success' })
  } catch (error) {
    showToast({ message: error.response?.data?.message || 'Login failed', type: 'error' })
    throw error
  }
}
```

**Note:** Users with admin role but NOT in the whitelist (`config/admin.php`) will be redirected to `/user/dashboard`.

### 5. Create User Panel Pages (10 min)

**File:** `resources/js/pages/user/Dashboard.vue`

```vue
<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back, {{ user?.name }}!</h1>
      <p class="text-gray-600 dark:text-gray-400 mt-1">Here's what's happening with your account</p>
    </div>

    <!-- User dashboard content -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Stats cards, recent activity, etc. -->
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'UserDashboard',
  setup() {
    const authStore = useAuthStore()
    const user = computed(() => authStore.user)

    return {
      user
    }
  }
}
</script>
```

### 6. Security Considerations

#### Backend Security

**Important:** Ensure admin-only resources are protected with middleware.

**File:** `routes/api.php`

Add role-based middleware to admin-only endpoints:

```php
Route::middleware(['auth:sanctum'])->group(function () {
    // User accessible routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user/settings', [UserSettingsController::class, 'index']);

    // Admin-only routes (add middleware check)
    Route::middleware('can:access-admin-panel')->group(function () {
        Route::prefix('resources')->group(function () {
            // Resource routes
        });
    });
});
```

**Create Middleware (Recommended):**

```bash
php artisan make:middleware EnsureUserCanAccessAdminPanel --no-interaction
```

**File:** `app/Http/Middleware/EnsureUserCanAccessAdminPanel.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdminPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check both admin role AND whitelist
        if (!$request->user() || !$request->user()->canAccessAdminPanel()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
```

**Security Note:** This middleware enforces:
1. User must be authenticated
2. User must have 'admin' role
3. User ID must be in `config('admin.id')` whitelist

Register in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserCanAccessAdminPanel::class,
    ]);
})
```

**Usage in routes:**

```php
// Protect admin-only routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::prefix('resources')->group(function () {
        // All resource CRUD routes automatically protected
    });
});
```

#### Frontend Security

- **Always check roles** in route guards
- **Hide admin menu items** from regular users
- **Redirect on unauthorized access** to appropriate error pages
- **Double-check on backend** - never rely on frontend alone

### 7. Admin Whitelist Configuration

**File:** `config/admin.php`

This configuration file contains the hardcoded list of user IDs allowed to access the admin panel.

```php
<?php

return [
    'id' => [1], // Array of user IDs allowed to access admin panel
];
```

**Usage:**
- Add user IDs to this array to grant admin panel access
- Users MUST have both 'admin' role AND be in this whitelist
- Example: `'id' => [1, 5, 12]` allows users with IDs 1, 5, and 12

**Security Benefits:**
- **Defense in depth**: Even if someone gains admin role, they can't access admin panel without being whitelisted
- **Centralized control**: Easy to revoke admin panel access without changing roles
- **Emergency lockdown**: Clear the array to disable all admin access temporarily
- **Audit trail**: Version control tracks changes to whitelist

**Important:** This is a **hard restriction**. Admins not in this list will:
- Be redirected to `/user/dashboard` on login
- Receive 403 Forbidden when accessing `/admin` routes
- Have `can_access_admin_panel` = false in API responses

### 8. Testing Checklist

- [ ] User login redirects to `/user/dashboard`
- [ ] Admin login (in whitelist) redirects to `/admin/dashboard`
- [ ] Admin login (NOT in whitelist) redirects to `/user/dashboard`
- [ ] User accessing `/admin` routes → 403 Forbidden
- [ ] Admin (not in whitelist) accessing `/admin` routes → 403 Forbidden
- [ ] Admin (in whitelist) accessing `/admin` routes → Success
- [ ] Admin accessing `/user` routes → Works (optional: block or allow)
- [ ] Menu items show correctly for each role
- [ ] All 4 layout variants work (Classic, Compact, Mini, Horizontal)
- [ ] Profile and settings pages work from both panels
- [ ] Logout works from both panels
- [ ] Mobile responsive behavior intact
- [ ] Dark mode works correctly
- [ ] Backend middleware blocks non-whitelisted admins
- [ ] Frontend guards redirect non-whitelisted admins

## Alternative Approach: Option B - Filter Menu Items in Component

If you prefer to keep menu items inside components and filter by role:

```javascript
// In Sidebar.vue setup()
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const isAdmin = computed(() => authStore.user?.is_admin)

const allMainMenuItems = [
  { to: { name: 'admin.dashboard' }, icon: 'dashboard', label: 'Dashboard', roles: ['admin'] },
  { to: { name: 'user.dashboard' }, icon: 'dashboard', label: 'Dashboard', roles: ['user'] },
  { to: { name: 'admin.users' }, icon: 'team', label: 'Users', roles: ['admin'] },
  // ... more items with roles array
]

const mainMenuItems = computed(() => {
  const userRole = isAdmin.value ? 'admin' : 'user'
  return allMainMenuItems.filter(item =>
    !item.roles || item.roles.includes(userRole)
  )
})
```

**Note:** Option A (props) is recommended for better separation of concerns and testability.

## Summary

This implementation provides:
- ✅ **Separate user and admin panels** - `/admin` and `/user` paths
- ✅ **100% component reuse** - No code duplication
- ✅ **Role-based menu filtering** - Different menus per panel
- ✅ **Two-factor admin authorization**:
  - Must have 'admin' role
  - Must be in `config/admin.id` whitelist
- ✅ **Defense in depth** - Backend middleware + frontend guards
- ✅ **Secure backend enforcement** - Middleware on all admin routes
- ✅ **Clean separation of concerns** - Clear architecture
- ✅ **Easy to maintain and extend** - Centralized configuration
- ✅ **Emergency lockdown capability** - Clear whitelist to disable admin access
- ✅ **All existing features work** - Profile, settings, etc. work in both panels

### Key Security Features

**Whitelist-Based Access (`config/admin.php`):**
- Hardcoded user IDs that can access admin panel
- Even admins need to be whitelisted
- Easy to grant/revoke access
- Version controlled for audit trail

**Backend Protection:**
- `EnsureUserCanAccessAdminPanel` middleware
- Checks both role AND whitelist
- Returns 403 for unauthorized access

**Frontend Guards:**
- Route meta checks `can_access_admin_panel`
- Automatic redirects based on access level
- Consistent with backend enforcement

The key insight is that your layout components are already generic and well-designed - they just need role-aware menu items passed as props, combined with a secure whitelist-based access control system.
