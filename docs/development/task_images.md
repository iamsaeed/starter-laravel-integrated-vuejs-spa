# Plan: Add Image Support to TaskFormModal

## Overview
Add comprehensive image/file attachment support to tasks using the existing `MediaUpload.vue` component:
1. **Multiple file attachments** - Upload one or multiple images/files as task attachments with preview, download, and delete capabilities
2. **Reuse existing components** - Leverage the already-built `MediaUpload.vue` component with drag-drop, image editing, and validation
3. **Full edit support** - Add new attachments, remove existing ones during task creation and editing

## Implementation Strategy

### Infrastructure Overview

**Spatie Media Library** is already configured in this multi-tenant application:
- **Package**: `spatie/laravel-medialibrary` is installed and configured
- **Migration**: `database/migrations/tenant/2025_01_01_000006_create_media_table.php` creates the `media` table in each workspace (tenant) database
- **Task Model**: Already implements `HasMedia` interface with `InteractsWithMedia` trait (see `app/Models/Task.php:14-16`)
- **Collections**: Task model already has `attachments` collection registered (see `app/Models/Task.php:271-274`)
- **Connection**: Media table uses tenant database connection (as tasks are tenant-scoped)

**Existing Components We Can Reuse:**
- **MediaUpload.vue** (`resources/js/components/form/MediaUpload.vue`) - Fully featured upload component with:
  - Drag & drop support
  - Image editor integration (crop, rotate, flip)
  - File type validation
  - File size validation (configurable max size)
  - Preview thumbnails
  - Single or multiple file uploads
  - Upload progress indicator
  - Error handling

This means we **don't need** to:
- Install Spatie Media Library (already done)
- Create media table migration (already exists)
- Add HasMedia interface to Task model (already implemented)
- Build a new upload component (MediaUpload.vue exists and works perfectly)

### Backend Changes

#### 1. No Migration Needed
- **Spatie Media Library is fully configured** with existing `media` table
- Task model already has `attachments` collection
- All media files are stored in tenant-scoped storage per workspace

#### 2. Backend Already Supports Task Attachments
The following already exist and work:
- **TaskService.php**:
  - `addAttachment(Task $task, UploadedFile $file): Media` (line 382)
  - `deleteAttachment(Task $task, int $mediaId): bool` (line 392)
- **TaskController.php**:
  - `POST /api/{workspace}/tasks/{task}/attachments` (line 330)
  - `DELETE /api/{workspace}/tasks/{task}/attachments/{media}` (line 358)

**What we need to verify/enhance:**
- Ensure TaskController returns attachments in the task response
- May need to add support for multiple file uploads in one request (currently supports single file)
- Ensure proper authorization checks are in place

### Frontend Changes

#### 1. Update TaskFormModal.vue
**Primary Change**: Add `MediaUpload` component to the task form

**Implementation Details:**
```vue
<!-- Add after the description field in TaskFormModal.vue -->

<!-- Task Attachments Section -->
<div class="form-group">
  <label class="form-label">Attachments</label>
  <MediaUpload
    v-model="form.attachments"
    :model-type="'App\\Models\\Task'"
    :model-id="task?.id || 'temp'"
    :multiple="true"
    :accepted-types="['image/*', 'application/pdf', '.doc', '.docx', '.xls', '.xlsx', '.txt', '.zip']"
    :max-file-size="20"
    collection="attachments"
    :editable="true"
    help-text="Upload images, documents, or other files (max 20MB per file)"
    @uploaded="handleAttachmentUploaded"
    @removed="handleAttachmentRemoved"
    @error="handleAttachmentError"
  />
</div>
```

**Handling Task Creation vs Editing:**
- **When creating a new task** (`task?.id` is null):
  - MediaUpload needs task ID to upload
  - Option 1: Create task first, then allow attachments
  - Option 2: Store files temporarily, upload after task creation
  - **Recommended**: Create task without attachments first, then allow user to add attachments

- **When editing existing task** (`task?.id` exists):
  - MediaUpload works immediately
  - Shows existing attachments
  - User can add/remove attachments

**State Management:**
```javascript
// Add to TaskFormModal.vue script
const form = ref({
  // ... existing fields
  attachments: props.task?.attachments || []
})

function handleAttachmentUploaded(media) {
  // Update local state
  if (Array.isArray(media)) {
    form.value.attachments.push(...media)
  } else {
    form.value.attachments.push(media)
  }
}

function handleAttachmentRemoved(mediaId) {
  // Remove from local state
  form.value.attachments = form.value.attachments.filter(a => a.id !== mediaId)
}

function handleAttachmentError(error) {
  errors.value.attachments = error
}
```

#### 2. Create AttachmentsList.vue Component (Optional Enhancement)
**Location**: `resources/js/components/tasks/AttachmentsList.vue`

This component would display attachments in a list format (useful for task detail views):
- **Features**:
  - Display list of attachments with file type icons
  - Image preview thumbnails
  - Download functionality
  - Delete button (if user has permission)
  - File size and upload date display
  - File type indicators (PDF, DOC, IMG, etc.)

**Usage:**
```vue
<AttachmentsList
  :attachments="task.attachments"
  :can-delete="canEdit"
  @delete="handleDeleteAttachment"
/>
```

#### 3. No TaskService.js Creation Needed (Yet)
- Since we're using MediaUpload component, it already uses `mediaService.js`
- Task CRUD operations can be added to parent components directly
- If task-specific service methods are needed later, create `taskService.js`

### API Endpoints

**Existing Endpoints (Already Working):**
```
POST   /api/{workspace}/tasks/{task}/attachments        - Add attachment (TaskController:330)
DELETE /api/{workspace}/tasks/{task}/attachments/{media} - Delete attachment (TaskController:358)
GET    /api/{workspace}/tasks/{task}                    - Show task (TaskController:128)
```

**What May Need Enhancement:**
- `GET /api/{workspace}/tasks/{task}` - Verify it returns `attachments` in response (line 143 already does this)
- Consider adding batch upload endpoint if needed (currently uploads are single file)

### Media Collections Setup

**Existing Setup (No Changes Needed):**
- Task model already has `attachments` collection registered (see `app/Models/Task.php:271-274`)
- Spatie Media Library stores all media in the tenant database's `media` table
- Each workspace has its own isolated media storage

**Collection to Use:**
- `attachments` - All file attachments (images, PDFs, documents, etc.)

**Storage Strategy:**
- All task attachments stored in tenant-scoped storage via `attachments` collection
- Media files are isolated per workspace (tenant)
- Each media item is linked to the task via polymorphic relationship in `media` table
- MediaUpload component handles all upload/delete operations

### Security & Validation

**Already Handled by MediaUpload Component:**
- File size validation (configured via `max-file-size` prop)
- File type validation (configured via `accepted-types` prop)
- Upload progress and error handling

**Backend Validation (Already Exists):**
- TaskController validates file uploads (line 336-338)
- Authorization checks via policies (line 334, 362)
- Spatie Media Library handles file storage securely

**Configuration:**
- Max file size: 20MB (configurable per MediaUpload instance)
- Allowed types: images (jpg, jpeg, png, gif, webp), documents (pdf, doc, docx, xls, xlsx, txt, zip)

### Testing

**Backend Tests:**
- Verify existing attachment endpoints work correctly
- Test authorization for upload/delete operations
- Test file size and type validation

**Frontend Tests:**
- Test TaskFormModal with MediaUpload integration
- Test attachment upload/remove in create vs edit modes
- Test AttachmentsList component (if created)

**E2E Tests:**
- Create task → add attachments → verify displayed
- Edit task → add/remove attachments → verify updates
- Delete attachment → verify removed from task

### Files to Modify/Create

**Backend (Minimal Changes):**
1. ✅ **No changes needed** - Backend already supports task attachments fully
2. ⚠️ **Verify** `app/Http/Controllers/Api/TaskController.php` - Ensure `show()` returns attachments (line 143 already does)
3. ⚠️ **Optional** - Add bulk upload endpoint if single-file limitation is a concern

**Frontend:**
1. ✅ **Update** `resources/js/components/tasks/TaskFormModal.vue` - Add MediaUpload component
2. 🆕 **Create** `resources/js/components/tasks/AttachmentsList.vue` - Optional, for displaying attachments in list format
3. ✅ **Update** Task detail/view components to display attachments

**Tests:**
1. 🆕 **Create** `tests/Feature/TaskAttachmentFlowTest.php` - Test complete attachment workflow
2. 🆕 **Create** `resources/js/components/tasks/__tests__/TaskFormModal.test.js` - Test form with attachments
3. 🆕 **Create** `resources/js/components/tasks/__tests__/AttachmentsList.test.js` - If component is created

### Implementation Workflow

#### Phase 1: TaskFormModal Integration (30-60 minutes)
1. Add MediaUpload component to TaskFormModal.vue
2. Add state management for attachments
3. Handle create vs edit scenarios
4. Test in browser

#### Phase 2: Attachment Display (30-45 minutes)
1. Update task detail views to show attachments
2. Create AttachmentsList component (optional but recommended)
3. Add download/delete functionality
4. Test attachment display

#### Phase 3: Handle Task Creation Flow (30-45 minutes)
**Problem**: MediaUpload needs task ID, but task doesn't exist yet during creation

**Solution Options:**

**Option A - Disable Attachments on Create (Simplest)**
- Show message: "Save task first to add attachments"
- After task created, user can edit and add attachments
- Pros: Simple, no additional backend work
- Cons: Two-step process for users

**Option B - Two-Step Create Flow (Recommended)**
- Create task first (without attachments)
- Keep modal open, switch to "edit mode" automatically
- Enable MediaUpload with new task ID
- Pros: Seamless user experience
- Cons: Slightly more complex frontend logic

**Option C - Temporary File Storage**
- Store files in browser state/memory
- Upload all files after task creation
- Pros: Single submission
- Cons: Complex state management, file size limitations

**Recommended: Option B** - Best UX with moderate complexity

#### Phase 4: Testing (1-2 hours)
1. Write backend tests for attachment endpoints
2. Write frontend component tests
3. E2E tests for attachment workflows
4. Manual testing across scenarios

### Migration Strategy

1. ✅ Existing tasks work as-is (no attachments shown)
2. ✅ Existing tasks can be edited to add attachments
3. ✅ New tasks can have attachments added after creation
4. ✅ No data migration needed (backward compatible)
5. ✅ No breaking changes to API or database

## Estimated Implementation Time

**Simplified Approach (Using Existing Components):**
- Frontend integration: 1-2 hours
- Task creation flow: 1 hour
- Attachment display components: 1 hour
- Testing: 1-2 hours
- **Total: 4-6 hours**

**Previous Estimate (Building from Scratch):** 9-12 hours

**Time Saved by Reusing MediaUpload.vue:** ~5 hours ✨

## Summary

This plan leverages existing, working components to add attachment support to tasks with minimal effort:

✅ **No backend changes required** - Everything already works
✅ **MediaUpload component** - Full-featured, tested, ready to use
✅ **Spatie Media Library** - Configured and working
✅ **Simple integration** - Add component to TaskFormModal
✅ **Flexible approach** - Choose task creation flow that fits UX needs

Ready to implement! 🚀
