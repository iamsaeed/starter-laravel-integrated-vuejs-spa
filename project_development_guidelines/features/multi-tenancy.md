# Cross-Database Query Issues - Tenant/Landlord Architecture

## Overview
This document lists all potential cross-database query issues where tenant database models attempt to query the `users` table from the landlord database.

## Problem
Models using `$connection = 'tenant'` cannot directly use `belongsTo(User::class)` relationships with eager loading (`->with('user')` or `->load('user')`) because the `users` table exists in the landlord database, not the tenant database.

## Solution Pattern
Use the `workspace_users_cache` table (exists in tenant database) which contains cached user data (user_id, name, email, avatar_url) for all workspace members.

---

## Tenant Models with User Relationships

### 1. ✅ **Comment** (app/Models/Comment.php:33)
**Status:** FIXED
- **Relationship:** `user()` - belongsTo(User::class)
- **Issue:** `scopeWithReplies()` was using `->with('user')`
- **Fix:**
  - Created `CommentService` to handle user data attachment
  - Updated `CommentController` to use service
  - Removed `->with('user')` from scope

### 2. ✅ **ProjectMember** (app/Models/ProjectMember.php:37)
**Status:** FIXED
- **Relationship:** `user()` - belongsTo(User::class)
- **Issue:** `ProjectService::getProjectMembers()` was using `->with('user')`
- **Fix:** Modified `getProjectMembers()` to fetch from `workspace_users_cache` manually

### 3. ⚠️ **TaskAssignment** (app/Models/TaskAssignment.php:34-41)
**Status:** POTENTIAL ISSUE
- **Relationships:**
  - `user()` - belongsTo(User::class)
  - `assignedBy()` - belongsTo(User::class, 'assigned_by')
- **Potential Issue:** If eager loaded anywhere
- **Recommendation:** Check all services/controllers that load TaskAssignment
- **Safe Usage:** Task model already has `getAssignedUsersCached()` helper method

### 4. ⚠️ **TaskNotification** (app/Models/TaskNotification.php:44)
**Status:** POTENTIAL ISSUE
- **Relationship:** `user()` - belongsTo(User::class)
- **Potential Issue:** If eager loaded in notification queries
- **Recommendation:** Check `TaskNotificationService` for eager loading
- **Files to check:**
  - `app/Services/TaskNotificationService.php`
  - Any notification controllers

### 5. ⚠️ **ChatConversation** (app/Models/ChatConversation.php:97)
**Status:** POTENTIAL ISSUE
- **Relationship:** `user()` - belongsTo(User::class)
- **Potential Issue:** If eager loaded when fetching conversations
- **Recommendation:** Check chat-related controllers/services
- **Files to check:**
  - `app/Http/Controllers/Api/ChatController.php` (if exists)
  - Any chat services

### 6. ✅ **Task** (app/Models/Task.php:107)
**Status:** SAFE
- **Relationship:** `creator()` - belongsTo(User::class, 'created_by')
- **Safe:** Model has helper methods:
  - `getAssignedUsersCached()` - for assigned users
  - `getCreatorCached()` - for creator
  - `getNotificationRecipients()` - for notification recipients
  - `toArrayWithCachedUsers()` - safe serialization

---

## Action Items

### Verification Results

1. **TaskNotificationService.php** ✅
   - Line 323: `->with('notifiable')` - This loads Task (morphTo), NOT User relationship
   - **Status:** SAFE - No cross-database queries found

2. **TaskAssignment Usage** ✅
   - Searched all services and controllers
   - No eager loading of user relationships found
   - Task model has safe helper methods (`getAssignedUsersCached()`)
   - **Status:** SAFE

3. **ChatConversation Usage** ✅
   - Checked `app/Http/Controllers/Api/Workspace/ChatController.php`
   - No eager loading of user relationship found
   - Only queries by user_id without loading relationship
   - **Status:** SAFE

4. **InvitationService.php** ✅
   - Line 167: `->with(['workspace', 'inviter'])`
   - Line 178: `->with('inviter')`
   - **Status:** SAFE - These are landlord models (WorkspaceInvitation is landlord scope)

### Recommended Pattern for Services

When working with tenant models that have User relationships:

```php
// ❌ BAD: Eager loading User relationship
$notifications = TaskNotification::with('user')->get();

// ✅ GOOD: Manually attach from cache
$notifications = TaskNotification::all();
$userIds = $notifications->pluck('user_id')->unique();
$cachedUsers = DB::table('workspace_users_cache')
    ->whereIn('user_id', $userIds)
    ->get()
    ->keyBy('user_id');

$notifications->map(function($notification) use ($cachedUsers) {
    $user = $cachedUsers->get($notification->user_id);
    if ($user) {
        $notification->user = (object)[
            'id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
        ];
    }
    return $notification;
});
```

---

## Testing Commands

To test for cross-database query issues:

```bash
# Run specific feature tests
php artisan test --filter=Task
php artisan test --filter=Comment
php artisan test --filter=Project

# Check for SQL errors in logs
tail -f storage/logs/laravel.log | grep "users' doesn't exist"

# Test in browser and check network tab for API errors
```

---

## Summary

| Model | Relationship | Status | Action Required |
|-------|--------------|--------|-----------------|
| Comment | user() | ✅ Fixed | None |
| ProjectMember | user() | ✅ Fixed | None |
| TaskAssignment | user(), assignedBy() | ✅ Verified Safe | No eager loading found |
| TaskNotification | user() | ✅ Verified Safe | No eager loading found |
| ChatConversation | user() | ✅ Verified Safe | No eager loading found |
| Task | creator() | ✅ Safe | Has helper methods |

## Final Status

✅ **ALL CLEAR** - No additional cross-database query issues found.

The following have been fixed:
1. ✅ Comment model - Fixed via CommentService
2. ✅ ProjectMember model - Fixed via ProjectService

The following have been verified safe:
3. ✅ TaskAssignment - No eager loading in codebase
4. ✅ TaskNotification - Only morphTo relationship loaded (Task, not User)
5. ✅ ChatConversation - No eager loading in chat controller
6. ✅ Task - Has safe helper methods for user data

## Recommendation

When adding new tenant models with User relationships in the future:
1. **DO NOT** use `->with('user')` or `->load('user')`
2. **DO** create service methods to attach user data from `workspace_users_cache`
3. **DO** follow the patterns in `CommentService.php` and `ProjectService.php`
4. **DO** use the helper methods in `Task.php` as examples
