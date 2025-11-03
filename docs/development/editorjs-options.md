# Editor.js Options for Task Description Rich Text Editor

## What is Editor.js?

Editor.js is a **block-styled rich text editor** that outputs clean JSON data instead of HTML markup. Each content element (paragraph, heading, image, list) is an independent block with structured data.

### Key Advantages:
- **Clean JSON Output** - No messy HTML, easy to store and render
- **Block-Based** - Each element is independent (prevents layout issues)
- **Extensible** - Plugin-based architecture for custom tools
- **Modern UX** - Clean, intuitive editing experience
- **Data Portability** - JSON works across web, mobile, APIs

### Key Difference from Traditional Editors:
```javascript
// Traditional WYSIWYG Output (messy HTML):
<div><h2>Title</h2><p>Text with <strong>bold</strong></p><img src="..."/></div>

// Editor.js Output (clean JSON):
{
  "blocks": [
    { "type": "header", "data": { "text": "Title", "level": 2 } },
    { "type": "paragraph", "data": { "text": "Text with <b>bold</b>" } },
    { "type": "image", "data": { "url": "...", "caption": "" } }
  ]
}
```

---

## Image Handling Options with Editor.js

### Official Image Tool (@editorjs/image)
**Package**: `@editorjs/image`

#### Features:
✅ File upload from device
✅ Paste images from clipboard
✅ Drag-and-drop support
✅ Image from URL
✅ Caption/border/background styling
✅ Stretch to full width

#### How It Works:
```javascript
import ImageTool from '@editorjs/image';

const editor = new EditorJS({
  tools: {
    image: {
      class: ImageTool,
      config: {
        endpoints: {
          byFile: '/api/upload-image',    // POST multipart/form-data
          byUrl: '/api/fetch-url'          // POST with { url: '...' }
        }
      }
    }
  }
});
```

#### Backend Integration:
**Upload Endpoint** (`/api/upload-image`):
- Receives: `multipart/form-data` with file
- Returns:
```json
{
  "success": 1,
  "file": {
    "url": "https://example.com/uploads/image.jpg"
  }
}
```

**For Your Laravel App:**
```php
// In TaskController.php or dedicated ImageController
public function uploadEditorImage(Request $request, string $workspace)
{
    $request->validate([
        'image' => 'required|image|max:10240' // 10MB
    ]);

    // Create temporary task or use existing task ID
    $task = Task::find($request->task_id);

    $media = $task->addMedia($request->file('image'))
        ->toMediaCollection('editor-images');

    return response()->json([
        'success' => 1,
        'file' => [
            'url' => $media->getUrl(),
        ]
    ]);
}
```

#### Data Structure:
```json
{
  "type": "image",
  "data": {
    "url": "https://example.com/image.jpg",
    "caption": "Image caption",
    "withBorder": false,
    "withBackground": false,
    "stretched": false
  }
}
```

---

## Vue 3 Integration Options

### Package 1: `vue3-editor-js` (by Kloen)
**Status**: Active, Vue 3 only

```bash
npm install vue3-editor-js
```

```vue
<template>
  <EditorJS
    v-model="editorData"
    :tools="tools"
    :config="config"
  />
</template>

<script setup>
import { EditorJS } from 'vue3-editor-js';
import Header from '@editorjs/header';
import List from '@editorjs/list';

const tools = {
  header: Header,
  list: List
};

const editorData = ref({
  blocks: []
});
</script>
```

### Package 2: `@junhao/vue-editorjs`
**Status**: Active, Vue 3 only

```bash
npm install @junhao/vue-editorjs
```

Similar API to above.

---

## Backend Considerations

### Database Storage

#### Store as JSON
```php
// Migration
$table->json('description');

// Model
protected function casts(): array
{
    return [
        'description' => 'array',
    ];
}

// Usage
$task->description = $editorJsOutput; // Stores as JSON
```

### Rendering on Frontend
```vue
<!-- Display task description -->
<div v-for="block in task.description.blocks" :key="block.id">
  <h2 v-if="block.type === 'header'">{{ block.data.text }}</h2>
  <p v-else-if="block.type === 'paragraph'">{{ block.data.text }}</p>
  <ul v-else-if="block.type === 'list'">
    <li v-for="item in block.data.items" :key="item">{{ item }}</li>
  </ul>
</div>
```

---

## Image Storage Strategy with Editor.js

### Approach 1: Store Images in 'editor-images' Collection
```php
// Task Model
public function registerMediaCollections(): void
{
    $this->addMediaCollection('attachments');      // Separate attachments
    $this->addMediaCollection('editor-images');    // Images in description
}
```

**Pros:**
- Separate concerns (description images vs attachments)
- Easy to clean up unused images

**Cons:**
- Need to track which images are in description
- Orphaned images if description edited -> Implement cleanup job to remove unused editor-images

---

## Recommended Implementation Path

**Why?**
1. ✅ **Simplest** - Reuse MediaUpload for all image needs
2. ✅ **Fastest** - No complex Editor.js image integration
3. ✅ **Consistent UX** - Same upload experience everywhere
4. ✅ **Image Editor** - Users get crop/rotate/flip
5. ✅ **Rich Text** - Editor.js provides formatting without image complexity

### Implementation Steps:

1. **Install Editor.js** (1 hour):
```bash
npm install vue3-editor-js @editorjs/header @editorjs/list @editorjs/quote @editorjs/code
```

2. **Update TaskFormModal.vue** (1 hour):
   - Add EditorJS component for description
   - Keep MediaUpload component for attachments
   - Update form state management

3. **Update Task Model** (15 minutes):
   - Change description column to JSON type
   - Add cast to array

4. **Create Description Renderer** (30 minutes):
   - Component to display Editor.js JSON blocks
   - Handle paragraph, header, list, quote, code

5. **Testing** (1 hour):
   - Create/edit tasks with rich text
   - Verify JSON storage
   - Test rendering

**Total Time: ~4 hours**

### Alternative: If You Want Images in Description

Use **Option 1 (Official Image Tool)** with these additions:

1. Create upload endpoint in TaskController
2. Configure Editor.js image tool
3. Add 'editor-images' media collection
4. Implement cleanup for orphaned images
5. Update renderer to handle image blocks

**Additional Time: +2-3 hours**

---

## Final Recommendation

**Start with Hybrid Approach:**
- Editor.js for rich text description
- Add Editor.js image tool for inline images  
- MediaUpload for all attachments/images
- Simple, clean, reuses existing code


