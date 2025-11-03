# Editor.js Reusable Component Implementation Plan

## Overview

Build a **fully reusable Editor.js rich text editor component** that can be integrated into any part of the application (tasks, comments, posts, etc.) with inline image support using Spatie Media Library.

### Key Requirements:
1. ✅ **Reusable Component** - Can be used anywhere in the app
2. ✅ **Custom Vue Wrapper** - Build our own wrapper around `@editorjs/editorjs`
3. ✅ **Image Tool** - Use `@editorjs/image` for inline images
4. ✅ **Spatie Media** - Backend integration with existing Spatie Media Library
5. ✅ **URL Storage** - Images stored as URLs (no base64)
6. ✅ **Model Agnostic** - Works with any model that implements `HasMedia`
7. ✅ **Production Ready** - New migrations only (no breaking changes)
8. ✅ **Single Column** - Store in `description` JSON column (convert existing text)

---

## Architecture Overview

### Frontend Architecture

```
resources/js/
├── components/
│   └── form/
│       ├── EditorJsInput.vue           # Main wrapper component
│       └── EditorJsRenderer.vue        # Display component
├── utils/
│   └── editorjs/
│       ├── config.js                   # Default editor config
│       └── imageUploader.js            # Custom image upload handler
└── services/
    └── editorImageService.js           # API service for image uploads
```

### Backend Architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── EditorImageController.php    # Generic image upload endpoint
│   └── Requests/
│       └── UploadEditorImageRequest.php     # Validation
├── Services/
│   └── EditorImageService.php               # Business logic for editor images
└── Traits/
    └── HasEditorImages.php                  # Trait for models using editor
```

---

## Implementation Plan

## Phase 1: Backend Infrastructure (1-2 hours)

### 1.1 Create Generic Image Upload Controller

**File**: `app/Http/Controllers/Api/EditorImageController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadEditorImageRequest;
use App\Services\EditorImageService;
use Illuminate\Http\JsonResponse;

class EditorImageController extends Controller
{
    public function __construct(
        private EditorImageService $service
    ) {}

    /**
     * Upload image for Editor.js
     *
     * Works with any model that implements HasMedia
     */
    public function upload(UploadEditorImageRequest $request): JsonResponse
    {
        $media = $this->service->uploadImage(
            modelType: $request->input('model_type'),
            modelId: $request->input('model_id'),
            file: $request->file('image'),
            collection: $request->input('collection', 'editor-images')
        );

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => $media->getUrl(),
            ],
        ]);
    }

    /**
     * Upload image by URL (optional)
     */
    public function uploadByUrl(UploadEditorImageRequest $request): JsonResponse
    {
        $media = $this->service->uploadImageFromUrl(
            modelType: $request->input('model_type'),
            modelId: $request->input('model_id'),
            url: $request->input('url'),
            collection: $request->input('collection', 'editor-images')
        );

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => $media->getUrl(),
            ],
        ]);
    }
}
```

### 1.2 Create Form Request

**File**: `app/Http/Requests/UploadEditorImageRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadEditorImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or implement custom authorization
    }

    public function rules(): array
    {
        $rules = [
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'collection' => 'nullable|string|max:255',
        ];

        if ($this->routeIs('*.byFile')) {
            $rules['image'] = 'required|image|mimes:jpeg,jpg,png,gif,webp,svg|max:10240'; // 10MB
        } else {
            $rules['url'] = 'required|url';
        }

        return $rules;
    }
}
```

### 1.3 Create Service

**File**: `app/Services/EditorImageService.php`

```php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EditorImageService
{
    /**
     * Upload image file for editor
     */
    public function uploadImage(
        string $modelType,
        int $modelId,
        UploadedFile $file,
        string $collection = 'editor-images'
    ): Media {
        $model = $modelType::findOrFail($modelId);

        if (!method_exists($model, 'addMedia')) {
            throw new \InvalidArgumentException('Model does not support media uploads');
        }

        return $model->addMedia($file)
            ->toMediaCollection($collection);
    }

    /**
     * Upload image from URL for editor
     */
    public function uploadImageFromUrl(
        string $modelType,
        int $modelId,
        string $url,
        string $collection = 'editor-images'
    ): Media {
        $model = $modelType::findOrFail($modelId);

        if (!method_exists($model, 'addMedia')) {
            throw new \InvalidArgumentException('Model does not support media uploads');
        }

        return $model->addMediaFromUrl($url)
            ->toMediaCollection($collection);
    }

    /**
     * Delete editor image
     */
    public function deleteImage(int $mediaId): bool
    {
        $media = Media::findOrFail($mediaId);
        $media->delete();

        return true;
    }

    /**
     * Clean up orphaned editor images
     * (Images uploaded but not referenced in content)
     */
    public function cleanupOrphanedImages(
        string $modelType,
        int $modelId,
        string $collection,
        array $usedImageUrls
    ): int {
        $model = $modelType::findOrFail($modelId);
        $allImages = $model->getMedia($collection);

        $deleted = 0;
        foreach ($allImages as $image) {
            if (!in_array($image->getUrl(), $usedImageUrls)) {
                $image->delete();
                $deleted++;
            }
        }

        return $deleted;
    }
}
```

### 1.4 Add Routes

**File**: `routes/api.php` or `routes/tenant.php` (for tenant routes)

```php
// Generic Editor.js image upload routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/editor/upload-image', [EditorImageController::class, 'upload'])
        ->name('editor.upload.file');

    Route::post('/editor/upload-image-url', [EditorImageController::class, 'uploadByUrl'])
        ->name('editor.upload.url');
});
```

### 1.5 Update Task Model (Add Editor Images Collection)

**File**: `app/Models/Task.php`

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('attachments');      // Existing
    $this->addMediaCollection('editor-images');    // NEW - for inline images
}
```

### 1.6 Create Migration for Task Description (Production Safe)

**File**: `database/migrations/tenant/tasks/2025_01_15_000001_convert_tasks_description_to_json.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing text data to Editor.js JSON format
        DB::table('tasks')->whereNotNull('description')->each(function ($task) {
            // Skip if already JSON
            $decoded = json_decode($task->description, true);
            if (is_array($decoded) && isset($decoded['blocks'])) {
                return; // Already migrated
            }

            // Convert plain text to Editor.js format
            $editorData = [
                'time' => now()->timestamp,
                'blocks' => [
                    [
                        'id' => uniqid(),
                        'type' => 'paragraph',
                        'data' => [
                            'text' => $task->description,
                        ],
                    ],
                ],
                'version' => '2.28.0',
            ];

            DB::table('tasks')
                ->where('id', $task->id)
                ->update(['description' => json_encode($editorData)]);
        });

        // Then, change column type to JSON
        Schema::table('tasks', function (Blueprint $table) {
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Extract plain text from Editor.js JSON
        DB::table('tasks')->whereNotNull('description')->each(function ($task) {
            $data = json_decode($task->description, true);

            if (isset($data['blocks'])) {
                $text = collect($data['blocks'])
                    ->map(fn($block) => $block['data']['text'] ?? '')
                    ->filter()
                    ->implode("\n\n");

                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update(['description' => $text]);
            }
        });

        // Change back to text
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }
};
```

**Important Notes:**
- ✅ **Data migration first** - Converts existing text to JSON before changing column type
- ✅ **Production safe** - Checks if already migrated to avoid data loss
- ✅ **Reversible** - Can rollback to plain text
- ✅ **No new column** - Uses existing `description` column
- ✅ **Backward compatible** - Preserves all existing data

### 1.7 Update Task Model

**File**: `app/Models/Task.php`

**Update cast for description:**
```php
protected function casts(): array
{
    return [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'description' => 'array',  // CHANGE THIS - cast description to array for Editor.js JSON
    ];
}
```

**Optional - Add accessor to get plain text from Editor.js JSON:**
```php
/**
 * Get plain text version of description (useful for search, previews, etc.)
 */
public function getDescriptionTextAttribute(): string
{
    if ($this->description && isset($this->description['blocks'])) {
        return collect($this->description['blocks'])
            ->map(fn($block) => $block['data']['text'] ?? '')
            ->filter()
            ->implode("\n\n");
    }

    return '';
}
```

**Note:** The `description` field is already in `$fillable` array, no changes needed there.

---

## Phase 2: Frontend Infrastructure (2-3 hours)

### 2.1 Install Dependencies

```bash
npm install @editorjs/editorjs @editorjs/header @editorjs/list @editorjs/quote @editorjs/code @editorjs/image --save
```

### 2.2 Create Image Upload Handler

**File**: `resources/js/utils/editorjs/imageUploader.js`

```javascript
import api from '@/utils/api'

export class ImageUploader {
  constructor(config) {
    this.modelType = config.modelType
    this.modelId = config.modelId
    this.collection = config.collection || 'editor-images'
    this.uploadEndpoint = config.uploadEndpoint || '/api/editor/upload-image'
    this.uploadByUrlEndpoint = config.uploadByUrlEndpoint || '/api/editor/upload-image-url'
  }

  /**
   * Upload file
   */
  async uploadByFile(file) {
    const formData = new FormData()
    formData.append('image', file)
    formData.append('model_type', this.modelType)
    formData.append('model_id', this.modelId)
    formData.append('collection', this.collection)

    const response = await api.post(this.uploadEndpoint, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    return response.data
  }

  /**
   * Upload by URL
   */
  async uploadByUrl(url) {
    const response = await api.post(this.uploadByUrlEndpoint, {
      url,
      model_type: this.modelType,
      model_id: this.modelId,
      collection: this.collection
    })

    return response.data
  }
}
```

### 2.3 Create Editor Config

**File**: `resources/js/utils/editorjs/config.js`

```javascript
import Header from '@editorjs/header'
import List from '@editorjs/list'
import Quote from '@editorjs/quote'
import Code from '@editorjs/code'
import ImageTool from '@editorjs/image'
import { ImageUploader } from './imageUploader'

/**
 * Get default Editor.js configuration
 */
export function getDefaultConfig(options = {}) {
  const {
    modelType,
    modelId,
    placeholder = 'Start typing...',
    minHeight = 300,
    enableImages = true,
  } = options

  const tools = {
    header: {
      class: Header,
      config: {
        placeholder: 'Enter a header',
        levels: [1, 2, 3, 4, 5, 6],
        defaultLevel: 2
      }
    },
    list: {
      class: List,
      inlineToolbar: true,
      config: {
        defaultStyle: 'unordered'
      }
    },
    quote: {
      class: Quote,
      inlineToolbar: true,
      config: {
        quotePlaceholder: 'Enter a quote',
        captionPlaceholder: 'Quote\'s author',
      }
    },
    code: {
      class: Code,
      config: {
        placeholder: 'Enter code'
      }
    },
  }

  // Add image tool if enabled and model info provided
  if (enableImages && modelType && modelId) {
    const uploader = new ImageUploader({
      modelType,
      modelId,
      collection: 'editor-images'
    })

    tools.image = {
      class: ImageTool,
      config: {
        uploader: {
          uploadByFile(file) {
            return uploader.uploadByFile(file)
          },
          uploadByUrl(url) {
            return uploader.uploadByUrl(url)
          }
        }
      }
    }
  }

  return {
    placeholder,
    minHeight,
    tools,
    i18n: {
      messages: {
        ui: {
          blockTunes: {
            toggler: {
              'Click to tune': 'Click to tune',
            },
          },
        },
        toolNames: {
          'Text': 'Paragraph',
          'Heading': 'Heading',
          'List': 'List',
          'Quote': 'Quote',
          'Code': 'Code',
          'Image': 'Image',
        },
      },
    },
  }
}
```

### 2.4 Create Vue Wrapper Component

**File**: `resources/js/components/form/EditorJsInput.vue`

```vue
<template>
  <div class="editor-js-input">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Editor Container -->
    <div
      ref="editorElement"
      :class="[
        'editor-js-container border rounded-lg',
        error ? 'border-red-500' : 'border-gray-300 dark:border-gray-600',
        disabled ? 'opacity-50 pointer-events-none' : ''
      ]"
      :style="{ minHeight: `${minHeight}px` }"
    />

    <!-- Help Text -->
    <p v-if="helpText" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import EditorJS from '@editorjs/editorjs'
import { getDefaultConfig } from '@/utils/editorjs/config'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ blocks: [] })
  },
  label: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  placeholder: {
    type: String,
    default: 'Start typing...'
  },
  minHeight: {
    type: Number,
    default: 300
  },
  modelType: {
    type: String,
    default: null
  },
  modelId: {
    type: [Number, String],
    default: null
  },
  enableImages: {
    type: Boolean,
    default: true
  },
  autosave: {
    type: Boolean,
    default: true
  },
  autosaveDelay: {
    type: Number,
    default: 2000
  }
})

const emit = defineEmits(['update:modelValue', 'ready', 'change'])

const editorElement = ref(null)
let editor = null
let autosaveTimer = null

onMounted(async () => {
  await initializeEditor()
})

onBeforeUnmount(() => {
  if (autosaveTimer) {
    clearTimeout(autosaveTimer)
  }
  destroyEditor()
})

async function initializeEditor() {
  if (!editorElement.value) return

  const config = getDefaultConfig({
    modelType: props.modelType,
    modelId: props.modelId,
    placeholder: props.placeholder,
    minHeight: props.minHeight,
    enableImages: props.enableImages
  })

  editor = new EditorJS({
    holder: editorElement.value,
    data: props.modelValue || { blocks: [] },
    tools: config.tools,
    placeholder: config.placeholder,
    i18n: config.i18n,
    readOnly: props.disabled,
    onChange: handleChange,
    onReady: () => {
      emit('ready', editor)
    }
  })
}

async function handleChange() {
  if (!editor) return

  // Clear existing autosave timer
  if (autosaveTimer) {
    clearTimeout(autosaveTimer)
  }

  // Set new autosave timer
  if (props.autosave) {
    autosaveTimer = setTimeout(async () => {
      await saveData()
    }, props.autosaveDelay)
  }
}

async function saveData() {
  if (!editor) return

  try {
    const outputData = await editor.save()
    emit('update:modelValue', outputData)
    emit('change', outputData)
  } catch (error) {
    console.error('Editor.js save error:', error)
  }
}

async function destroyEditor() {
  if (editor) {
    try {
      await editor.destroy()
      editor = null
    } catch (error) {
      console.error('Editor.js destroy error:', error)
    }
  }
}

// Watch for external modelValue changes
watch(() => props.modelValue, async (newValue) => {
  if (!editor) return

  const currentData = await editor.save()
  if (JSON.stringify(currentData) !== JSON.stringify(newValue)) {
    await destroyEditor()
    await initializeEditor()
  }
}, { deep: true })

// Watch for disabled changes
watch(() => props.disabled, async (newValue) => {
  if (editor) {
    editor.readOnly.toggle(newValue)
  }
})

// Expose save method for manual saves
defineExpose({
  save: saveData,
  editor: () => editor
})
</script>

<style scoped>
.editor-js-container {
  padding: 1rem;
  background: white;
}

.dark .editor-js-container {
  background: rgb(31 41 55); /* dark:bg-gray-800 */
  color: white;
}

/* Editor.js overrides */
:deep(.codex-editor__redactor) {
  padding-bottom: 0 !important;
}

:deep(.ce-block__content) {
  max-width: 100%;
}

:deep(.ce-toolbar__actions) {
  right: 0;
}
</style>
```

### 2.5 Create Renderer Component

**File**: `resources/js/components/form/EditorJsRenderer.vue`

```vue
<template>
  <div class="editor-js-renderer prose dark:prose-invert max-w-none">
    <div v-if="!data || !data.blocks || data.blocks.length === 0" class="text-gray-400 italic">
      No content
    </div>

    <div v-else>
      <component
        v-for="(block, index) in data.blocks"
        :key="index"
        :is="getBlockComponent(block.type)"
        :data="block.data"
      />
    </div>
  </div>
</template>

<script setup>
import { defineAsyncComponent } from 'vue'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

// Block components
const ParagraphBlock = defineAsyncComponent(() => import('./blocks/ParagraphBlock.vue'))
const HeaderBlock = defineAsyncComponent(() => import('./blocks/HeaderBlock.vue'))
const ListBlock = defineAsyncComponent(() => import('./blocks/ListBlock.vue'))
const QuoteBlock = defineAsyncComponent(() => import('./blocks/QuoteBlock.vue'))
const CodeBlock = defineAsyncComponent(() => import('./blocks/CodeBlock.vue'))
const ImageBlock = defineAsyncComponent(() => import('./blocks/ImageBlock.vue'))

function getBlockComponent(type) {
  const components = {
    paragraph: ParagraphBlock,
    header: HeaderBlock,
    list: ListBlock,
    quote: QuoteBlock,
    code: CodeBlock,
    image: ImageBlock
  }

  return components[type] || ParagraphBlock
}
</script>

<style scoped>
/* Tailwind typography plugin styles will handle most formatting */
</style>
```

### 2.6 Create Block Components

**Files**: `resources/js/components/form/blocks/*.vue`

**ParagraphBlock.vue:**
```vue
<template>
  <p v-html="sanitizedText" class="mb-4" />
</template>

<script setup>
import { computed } from 'vue'
import DOMPurify from 'dompurify'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const sanitizedText = computed(() => {
  return DOMPurify.sanitize(props.data.text || '')
})
</script>
```

**HeaderBlock.vue:**
```vue
<template>
  <component :is="`h${data.level || 2}`" v-html="sanitizedText" class="font-bold mb-4" />
</template>

<script setup>
import { computed } from 'vue'
import DOMPurify from 'dompurify'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const sanitizedText = computed(() => {
  return DOMPurify.sanitize(props.data.text || '')
})
</script>
```

**ListBlock.vue:**
```vue
<template>
  <component :is="listTag" class="mb-4 ml-6">
    <li v-for="(item, index) in data.items" :key="index" v-html="sanitize(item)" />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import DOMPurify from 'dompurify'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const listTag = computed(() => {
  return props.data.style === 'ordered' ? 'ol' : 'ul'
})

function sanitize(text) {
  return DOMPurify.sanitize(text)
}
</script>
```

**QuoteBlock.vue:**
```vue
<template>
  <blockquote class="border-l-4 border-gray-300 dark:border-gray-600 pl-4 italic mb-4">
    <p v-html="sanitizedText" class="mb-2" />
    <cite v-if="data.caption" class="text-sm text-gray-600 dark:text-gray-400">
      — {{ data.caption }}
    </cite>
  </blockquote>
</template>

<script setup>
import { computed } from 'vue'
import DOMPurify from 'dompurify'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const sanitizedText = computed(() => {
  return DOMPurify.sanitize(props.data.text || '')
})
</script>
```

**CodeBlock.vue:**
```vue
<template>
  <pre class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg mb-4 overflow-x-auto"><code>{{ data.code }}</code></pre>
</template>

<script setup>
const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})
</script>
```

**ImageBlock.vue:**
```vue
<template>
  <figure class="mb-4">
    <img
      :src="data.file.url"
      :alt="data.caption || 'Image'"
      :class="[
        'rounded-lg',
        data.stretched ? 'w-full' : 'max-w-full',
        data.withBorder ? 'border-2 border-gray-300 dark:border-gray-600' : '',
        data.withBackground ? 'bg-gray-100 dark:bg-gray-800 p-4' : ''
      ]"
    />
    <figcaption v-if="data.caption" class="text-sm text-gray-600 dark:text-gray-400 mt-2 text-center">
      {{ data.caption }}
    </figcaption>
  </figure>
</template>

<script setup>
const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})
</script>
```

### 2.7 Create API Service

**File**: `resources/js/services/editorImageService.js`

```javascript
import api from '@/utils/api'

export const editorImageService = {
  /**
   * Upload image file
   */
  async uploadImage(file, modelType, modelId, collection = 'editor-images') {
    const formData = new FormData()
    formData.append('image', file)
    formData.append('model_type', modelType)
    formData.append('model_id', modelId)
    formData.append('collection', collection)

    const response = await api.post('/api/editor/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    return response.data
  },

  /**
   * Upload image from URL
   */
  async uploadImageFromUrl(url, modelType, modelId, collection = 'editor-images') {
    const response = await api.post('/api/editor/upload-image-url', {
      url,
      model_type: modelType,
      model_id: modelId,
      collection
    })

    return response.data
  }
}
```

---

## Phase 3: Integration with Tasks (1 hour)

### 3.1 Update TaskFormModal.vue

```vue
<template>
  <div class="task-form-modal">
    <!-- ... other fields ... -->

    <!-- Description with Editor.js -->
    <div class="form-group">
      <EditorJsInput
        v-model="form.description"
        label="Description"
        :model-type="'App\\Models\\Task'"
        :model-id="task?.id"
        :enable-images="!!task?.id"
        :disabled="!task?.id && creating"
        :help-text="!task?.id ? 'Save task first to add images' : 'Add rich text description with inline images'"
        placeholder="Describe the task with rich formatting, code, images..."
        :min-height="300"
        @change="handleDescriptionChange"
      />
    </div>

    <!-- Separate Attachments Section -->
    <div class="form-group">
      <label class="form-label">Attachments</label>
      <MediaUpload
        v-model="form.attachments"
        :model-type="'App\\Models\\Task'"
        :model-id="task?.id"
        :multiple="true"
        collection="attachments"
        :disabled="!task?.id"
        help-text="Upload additional files (PDFs, documents, etc.)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import EditorJsInput from '@/components/form/EditorJsInput.vue'
import MediaUpload from '@/components/form/MediaUpload.vue'

const form = ref({
  title: '',
  description: { blocks: [] },  // Editor.js JSON (now stored in description column)
  attachments: [],
  // ... other fields
})

function handleDescriptionChange(data) {
  // Optional: Extract image URLs for cleanup tracking
  const imageUrls = data.blocks
    .filter(block => block.type === 'image')
    .map(block => block.data.file.url)

  // Store for later cleanup of orphaned images
  form.value.editorImageUrls = imageUrls
}
</script>
```

**Key Changes:**
- ✅ **Single field** - `description` now contains Editor.js JSON
- ✅ **Rich text** - Full Editor.js functionality in description field
- ✅ **Simplified** - No dual description/details confusion
- ✅ **Migration handles conversion** - Existing text descriptions automatically converted to JSON

### 3.2 Update Task Detail/View Components

```vue
<template>
  <div class="task-detail">
    <h1>{{ task.title }}</h1>

    <!-- Render Editor.js description content -->
    <div v-if="task.description && task.description.blocks && task.description.blocks.length > 0" class="mb-6">
      <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Description</h3>
      <EditorJsRenderer :data="task.description" />
    </div>

    <!-- Fallback for tasks without description -->
    <div v-else-if="!task.description || !task.description.blocks" class="mb-6">
      <p class="text-gray-400 italic">No description provided</p>
    </div>

    <!-- Show attachments -->
    <div v-if="task.attachments && task.attachments.length > 0" class="mt-6">
      <h3 class="text-lg font-semibold mb-3">Attachments</h3>
      <AttachmentsList :attachments="task.attachments" />
    </div>
  </div>
</template>

<script setup>
import EditorJsRenderer from '@/components/form/EditorJsRenderer.vue'
import AttachmentsList from '@/components/tasks/AttachmentsList.vue'

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
})
</script>
```

**Display Logic:**
- ✅ Renders `description` as Editor.js JSON
- ✅ Shows fallback if no description
- ✅ All tasks display consistently (migration converts old text)

---

## Phase 4: Testing (1-2 hours)

### 4.1 Backend Tests

**File**: `tests/Feature/EditorImageUploadTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_editor_image()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $user->id]);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($user)
            ->postJson('/api/editor/upload-image', [
                'image' => $file,
                'model_type' => 'App\\Models\\Task',
                'model_id' => $task->id,
                'collection' => 'editor-images',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'file' => ['url'],
            ]);

        $this->assertEquals(1, $task->fresh()->getMedia('editor-images')->count());
    }

    public function test_validates_image_upload()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson('/api/editor/upload-image', [
                'model_type' => 'App\\Models\\Task',
                'model_id' => $task->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }
}
```

### 4.2 Frontend Component Tests

**File**: `resources/js/components/form/__tests__/EditorJsInput.test.js`

```javascript
import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import EditorJsInput from '../EditorJsInput.vue'

describe('EditorJsInput', () => {
  let wrapper

  beforeEach(() => {
    wrapper = mount(EditorJsInput, {
      props: {
        modelValue: { blocks: [] },
        modelType: 'App\\Models\\Task',
        modelId: 1
      }
    })
  })

  it('renders editor container', () => {
    expect(wrapper.find('.editor-js-container').exists()).toBe(true)
  })

  it('displays label when provided', async () => {
    await wrapper.setProps({ label: 'Test Label' })
    expect(wrapper.text()).toContain('Test Label')
  })

  it('shows error message', async () => {
    await wrapper.setProps({ error: 'Error message' })
    expect(wrapper.text()).toContain('Error message')
  })
})
```

---

## Summary

### Files to Create

**Backend:**
1. ✅ `app/Http/Controllers/Api/EditorImageController.php`
2. ✅ `app/Http/Requests/UploadEditorImageRequest.php`
3. ✅ `app/Services/EditorImageService.php`
4. ✅ `database/migrations/tenant/tasks/2025_01_15_000001_convert_tasks_description_to_json.php`
5. ✅ Add routes to `routes/api.php` or `routes/tenant.php`

**Frontend:**
1. ✅ `resources/js/components/form/EditorJsInput.vue`
2. ✅ `resources/js/components/form/EditorJsRenderer.vue`
3. ✅ `resources/js/components/form/blocks/ParagraphBlock.vue`
4. ✅ `resources/js/components/form/blocks/HeaderBlock.vue`
5. ✅ `resources/js/components/form/blocks/ListBlock.vue`
6. ✅ `resources/js/components/form/blocks/QuoteBlock.vue`
7. ✅ `resources/js/components/form/blocks/CodeBlock.vue`
8. ✅ `resources/js/components/form/blocks/ImageBlock.vue`
9. ✅ `resources/js/utils/editorjs/config.js`
10. ✅ `resources/js/utils/editorjs/imageUploader.js`
11. ✅ `resources/js/services/editorImageService.js`

**Tests:**
1. ✅ `tests/Feature/EditorImageUploadTest.php`
2. ✅ `resources/js/components/form/__tests__/EditorJsInput.test.js`

### Models to Update

1. ✅ `app/Models/Task.php` - Add `editor-images` collection, cast `description` to array

### Time Estimate

- **Backend Setup**: 1-2 hours
- **Frontend Components**: 2-3 hours
- **Integration**: 1 hour
- **Testing**: 1-2 hours
- **Total**: 5-8 hours

### Key Features

✅ Fully reusable component (use anywhere)
✅ Model-agnostic (works with any HasMedia model)
✅ Official @editorjs/image tool
✅ Spatie Media Library integration
✅ URL-based image storage (no base64)
✅ Production-safe migration
✅ Backward compatible
✅ Comprehensive testing

### Usage Examples

```vue
<!-- Use in Task Form -->
<EditorJsInput
  v-model="task.description"
  label="Task Description"
  :model-type="'App\\Models\\Task'"
  :model-id="task.id"
/>

<!-- Use in Comment Form -->
<EditorJsInput
  v-model="comment.body"
  label="Comment"
  :model-type="'App\\Models\\Comment'"
  :model-id="comment.id"
/>

<!-- Use in Blog Post -->
<EditorJsInput
  v-model="post.content"
  label="Post Content"
  :model-type="'App\\Models\\Post'"
  :model-id="post.id"
/>

<!-- Display rendered content -->
<EditorJsRenderer :data="task.description" />
```

### Database Schema Summary

**Tasks Table After Migration:**

| Column | Type (Before) | Type (After) | Description |
|--------|---------------|--------------|-------------|
| `description` | text | **json** | Converted to JSON - stores Editor.js rich content |

**Migration Strategy:**
- ✅ Converts existing text to Editor.js JSON format
- ✅ Changes column type from text to json
- ✅ Fully reversible (can rollback to text)
- ✅ No data loss - all content preserved
- ✅ Production safe - checks if already migrated

**Benefits:**
- ✅ Single source of truth (no dual fields)
- ✅ Clean, simple architecture
- ✅ Rich text with images in description
- ✅ Backward compatible (migration handles conversion)
- ✅ All existing code works after migration

**Ready to implement! 🚀**
