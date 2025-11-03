# Lovable Clone - AI Website Generator

## Plan Overview

Build an AI-powered website generator that uses Claude Sonnet 4/4.5 to create single or multi-page TailwindCSS/HTML/JS websites with:
- Chat interface (left) for user prompts
- Code/Preview toggle (right) for real-time preview
- Download generated sites as ZIP files
- Iterative editing through conversation
- Integration with free image repositories (Unsplash/Pexels)
- Pre-configured CDNs (TailwindCSS, FontAwesome, animation libraries)

---

## Backend Implementation

### 1. Database Migrations

**Create tables:**
- `user_api_keys` - Store encrypted Anthropic API keys per user
  - `id`, `user_id`, `provider` (anthropic), `api_key` (encrypted), `is_active`, `created_at`, `updated_at`

- `website_projects` - Store generated website projects
  - `id`, `user_id`, `name`, `description`, `status` (generating/ready/failed), `created_at`, `updated_at`

- `website_files` - Store individual files (HTML, CSS, JS)
  - `id`, `website_project_id`, `file_path`, `file_name`, `content` (text), `file_type` (html/css/js), `created_at`, `updated_at`

- `website_conversations` - Store chat history
  - `id`, `website_project_id`, `role` (user/assistant), `content` (text), `created_at`

### 2. Models

**Create models with relationships:**
- `UserApiKey` → belongs to User, encrypted api_key attribute
- `WebsiteProject` → belongs to User, has many WebsiteFiles, has many WebsiteConversations
- `WebsiteFile` → belongs to WebsiteProject
- `WebsiteConversation` → belongs to WebsiteProject

### 3. Service Classes

**`app/Services/AnthropicService.php`:**
- `chat(array $messages, string $apiKey, bool $stream = false)` - Call Claude API
- `streamChat(array $messages, string $apiKey)` - Stream responses for real-time chat
- `buildSystemPrompt()` - System prompt for website generation context
- `parseCodeBlocks(string $response)` - Extract HTML/CSS/JS from markdown code blocks

**`app/Services/WebsiteGeneratorService.php`:**
- `createProject(User $user, string $initialPrompt)` - Initialize new website project
- `generateWebsite(WebsiteProject $project, string $prompt)` - Generate/update website files
- `updateFile(WebsiteFile $file, string $content)` - Update individual file
- `parseGeneratedFiles(string $response)` - Parse Claude's response into file structure
- `saveFiles(WebsiteProject $project, array $files)` - Save files to database
- `injectCDNs(string $html)` - Inject TailwindCSS, FontAwesome, animation library CDNs

**`app/Services/ZipGeneratorService.php`:**
- `createZip(WebsiteProject $project)` - Generate downloadable ZIP file
- `addFilesToZip(ZipArchive $zip, Collection $files)` - Add files to ZIP
- `cleanup(string $zipPath)` - Delete temporary ZIP files

**`app/Services/ImageService.php`:**
- `searchUnsplash(string $query)` - Search Unsplash API
- `searchPexels(string $query)` - Search Pexels API
- `getRandomImage(string $query)` - Get random free image URL

### 4. Controllers & Routes

**`app/Http/Controllers/Api/WebsiteGeneratorController.php`:**
- `POST /api/website-generator/projects` - Create new project
- `GET /api/website-generator/projects` - List user's projects
- `GET /api/website-generator/projects/{id}` - Get project with files
- `POST /api/website-generator/projects/{id}/chat` - Send chat message (stream response)
- `PUT /api/website-generator/projects/{id}/files/{fileId}` - Update file content
- `GET /api/website-generator/projects/{id}/download` - Download as ZIP
- `DELETE /api/website-generator/projects/{id}` - Delete project

**`app/Http/Controllers/Api/ApiKeyController.php`:**
- `GET /api/api-keys` - Get user's API keys (masked)
- `POST /api/api-keys` - Store new API key
- `PUT /api/api-keys/{id}` - Update API key
- `DELETE /api/api-keys/{id}` - Delete API key
- `POST /api/api-keys/{id}/validate` - Validate API key with Anthropic

### 5. Form Requests

- `StoreApiKeyRequest` - Validate API key input
- `CreateProjectRequest` - Validate project creation
- `ChatRequest` - Validate chat messages
- `UpdateFileRequest` - Validate file updates

### 6. Backend Tests

**Feature tests:**
- `tests/Feature/WebsiteGeneratorTest.php` - CRUD operations, chat, download
- `tests/Feature/ApiKeyManagementTest.php` - API key storage, encryption, validation
- `tests/Feature/ZipGenerationTest.php` - ZIP creation, file inclusion

**Unit tests:**
- `tests/Unit/AnthropicServiceTest.php` - Mock API calls
- `tests/Unit/WebsiteGeneratorServiceTest.php` - File parsing, CDN injection
- `tests/Unit/ZipGeneratorServiceTest.php` - ZIP structure

---

## Frontend Implementation

### 1. Pages

**`resources/js/pages/admin/website-generator/Index.vue`:**
- List of user's website projects
- Create new project button
- Project cards with preview thumbnails

**`resources/js/pages/admin/website-generator/Generator.vue`:**
- Main generator interface
- Split layout: Chat (left) | Code/Preview (right)
- Real-time updates

**`resources/js/pages/admin/website-generator/ApiKeySetup.vue`:**
- Manage Anthropic API keys
- Validation UI
- Security warnings

### 2. Components

**Chat Panel (`components/website-generator/ChatPanel.vue`):**
- Message list (user/assistant)
- Input field with submit
- Streaming message support
- Loading states
- Auto-scroll to bottom

**Preview Panel (`components/website-generator/PreviewPanel.vue`):**
- Toggle buttons (Code | Preview)
- Iframe sandbox for HTML preview
- Syntax-highlighted code view (using Prism.js or Shiki)
- Responsive preview modes (mobile/tablet/desktop)

**File Tree (`components/website-generator/FileTree.vue`):**
- Display multi-file projects
- Click to switch active file
- File icons by type
- Add/rename/delete files

**Code Editor (`components/website-generator/CodeEditor.vue`):**
- Monaco Editor or CodeMirror integration
- Syntax highlighting
- Auto-save on blur
- Line numbers

**Download Button (`components/website-generator/DownloadButton.vue`):**
- Trigger ZIP generation
- Download progress
- Success/error states

**API Key Manager (`components/website-generator/ApiKeyManager.vue`):**
- List API keys (masked)
- Add new key form
- Validate key button
- Delete confirmation

**Image Picker (`components/website-generator/ImagePicker.vue`):**
- Search Unsplash/Pexels
- Image grid preview
- Insert image URL into prompt
- Optional: Direct insertion into generated HTML

### 3. Services

**`resources/js/services/websiteGeneratorService.js`:**
```javascript
export const websiteGeneratorService = {
  // Projects
  async getProjects() { ... },
  async createProject(initialPrompt) { ... },
  async getProject(id) { ... },
  async deleteProject(id) { ... },

  // Chat (streaming)
  async sendMessage(projectId, message, onChunk) { ... },

  // Files
  async updateFile(projectId, fileId, content) { ... },
  async getFile(projectId, fileId) { ... },

  // Download
  async downloadZip(projectId) { ... },
}
```

**`resources/js/services/apiKeyService.js`:**
```javascript
export const apiKeyService = {
  async getApiKeys() { ... },
  async storeApiKey(apiKey) { ... },
  async validateApiKey(id) { ... },
  async deleteApiKey(id) { ... },
}
```

### 4. Pinia Stores

**`resources/js/stores/websiteGenerator.js`:**
- `projects` - List of projects
- `currentProject` - Active project
- `currentFile` - Active file being edited
- `chatMessages` - Conversation history
- `isGenerating` - Loading state
- `previewMode` - 'code' | 'preview'
- `activeApiKey` - User's active API key
- Actions: `loadProjects`, `createProject`, `sendMessage`, `updateFile`, `downloadProject`

### 5. Routes

**Add to `resources/js/router/index.js`:**
```javascript
{
  path: '/admin/website-generator',
  component: () => import('@/layouts/AdminLayout.vue'),
  meta: { requiresAuth: true },
  children: [
    {
      path: '',
      name: 'admin.website-generator.index',
      component: () => import('@/pages/admin/website-generator/Index.vue'),
    },
    {
      path: 'create',
      name: 'admin.website-generator.create',
      component: () => import('@/pages/admin/website-generator/Generator.vue'),
    },
    {
      path: ':id',
      name: 'admin.website-generator.show',
      component: () => import('@/pages/admin/website-generator/Generator.vue'),
    },
    {
      path: 'settings/api-keys',
      name: 'admin.website-generator.api-keys',
      component: () => import('@/pages/admin/website-generator/ApiKeySetup.vue'),
    },
  ],
}
```

### 6. Frontend Tests

**Vitest unit/integration tests:**
- `tests/unit/stores/websiteGenerator.test.js` - Store logic
- `tests/unit/services/websiteGeneratorService.test.js` - API mocking
- `tests/unit/components/ChatPanel.test.js` - Message rendering
- `tests/unit/components/PreviewPanel.test.js` - Toggle, iframe rendering

**Playwright E2E tests:**
- `tests/e2e/website-generator.spec.js`:
  - Create new project flow
  - Chat and generate website
  - Preview toggle
  - Download ZIP
  - Edit files
  - Delete project

---

## Key Features & Technical Details

### 1. **Streaming Chat Integration**

**Backend (Laravel):**
```php
// Stream SSE responses
public function chat(ChatRequest $request, WebsiteGeneratorService $service)
{
    return response()->stream(function () use ($request, $service) {
        $service->streamGeneration($request->project_id, $request->message, function ($chunk) {
            echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
            ob_flush();
            flush();
        });
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
}
```

**Frontend (Vue):**
```javascript
// EventSource for SSE
const eventSource = new EventSource(`/api/website-generator/projects/${id}/chat`)
eventSource.onmessage = (event) => {
  const data = JSON.parse(event.data)
  appendToMessage(data.chunk)
}
```

### 2. **System Prompt for Claude**

```
You are an expert web developer assistant. Generate production-ready HTML, CSS, and JavaScript code based on user requirements.

IMPORTANT RULES:
1. Use TailwindCSS CDN for all styling (already included in template)
2. Use FontAwesome CDN for icons (already included)
3. Use AOS (Animate On Scroll) or GSAP for animations
4. For images, use placeholders or Unsplash URLs: https://source.unsplash.com/800x600/?[keyword]
5. Output files in markdown code blocks with language tags:
   ```html:index.html
   [HTML code here]
   ```
   ```css:styles.css
   [CSS code here]
   ```
   ```js:script.js
   [JS code here]
   ```
6. Create responsive, accessible, modern designs
7. Include semantic HTML5 elements
8. For multi-page sites, create separate HTML files
9. Always include complete, working code
10. Test your code mentally before outputting

When user asks for changes, update only the affected files.
```

### 3. **Default HTML Template with CDNs**

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{title}}</title>

  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <!-- GSAP (optional, for advanced animations) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

  <style>
    {{custom_css}}
  </style>
</head>
<body>
  {{content}}

  <script>
    AOS.init();
    {{custom_js}}
  </script>
</body>
</html>
```

### 4. **ZIP Generation Structure**

```
website-project-name.zip
├── index.html
├── about.html (if multi-page)
├── contact.html (if multi-page)
├── css/
│   └── styles.css (if custom CSS)
├── js/
│   └── script.js (if custom JS)
├── assets/
│   └── (placeholder for images if needed)
└── README.md (instructions)
```

### 5. **Security Considerations**

- **API Key Storage:** Encrypt using Laravel's `Crypt` facade before storing
- **Preview Sandbox:** Use `<iframe sandbox="allow-scripts">` to prevent XSS
- **Rate Limiting:** Limit API calls per user/hour to prevent abuse
- **Input Validation:** Sanitize all user inputs before sending to Claude
- **File Size Limits:** Max file size for generated websites (e.g., 50MB)

### 6. **UI/UX Features**

- **Responsive Split Layout:** Resizable panels (use `split.js` or custom)
- **Keyboard Shortcuts:** Ctrl+Enter to send chat, Ctrl+S to save file
- **Auto-save:** Debounced auto-save when editing files
- **Version History:** Optional: Store file versions for undo/redo
- **Export Options:** ZIP download, GitHub Gist, CodePen export
- **Templates:** Pre-built prompts ("Create a landing page", "Build a portfolio")
- **Dark Mode:** Consistent with app theme

---

## Implementation Phases

### **Phase 1: Backend Foundation** (Day 1-2)
1. Create migrations for all tables
2. Create models with relationships
3. Implement `AnthropicService` with streaming support
4. Create `WebsiteGeneratorService` for file parsing/generation
5. Build API endpoints (projects, chat, files)
6. Write PHPUnit tests for services and endpoints
7. Run `vendor/bin/pint --dirty`

### **Phase 2: API Key Management** (Day 2)
1. Implement `ApiKeyController` with encryption
2. Create API key validation endpoint
3. Add API key management UI components
4. Write tests for API key CRUD

### **Phase 3: Frontend Chat Interface** (Day 3-4)
1. Create Pinia store for website generator
2. Build `ChatPanel` component with streaming
3. Build `Generator.vue` page with split layout
4. Implement `websiteGeneratorService` with SSE
5. Add project creation flow
6. Write Vitest tests for chat components

### **Phase 4: Preview & Code Editor** (Day 4-5)
1. Build `PreviewPanel` with iframe sandbox
2. Integrate Monaco Editor or CodeMirror
3. Implement code/preview toggle
4. Add syntax highlighting (Prism.js)
5. Build `FileTree` component for multi-file projects
6. Add responsive preview modes

### **Phase 5: ZIP Download & File Management** (Day 5)
1. Implement `ZipGeneratorService`
2. Create download endpoint with streaming
3. Build download UI with progress
4. Add file CRUD operations (rename, delete, add)
5. Test ZIP structure and file integrity

### **Phase 6: Image Integration & Polish** (Day 6)
1. Integrate Unsplash/Pexels API
2. Build `ImagePicker` component
3. Add default templates/prompts
4. Implement keyboard shortcuts
5. Add loading states, error handling
6. Responsive design polish

### **Phase 7: Testing & Optimization** (Day 7)
1. Write comprehensive E2E tests (Playwright)
2. Performance optimization (lazy loading, code splitting)
3. Security audit (XSS prevention, rate limiting)
4. Accessibility testing
5. Bug fixes and edge cases
6. Documentation in `docs_dev/website-generator.md`

---

## Additional Enhancements (Optional)

1. **GitHub Integration:** Export directly to GitHub repo
2. **Deployment:** One-click deploy to Netlify/Vercel
3. **Version Control:** Git-like history for projects
4. **Collaboration:** Share projects with other users
5. **Templates Gallery:** Pre-built website templates
6. **AI Image Generation:** Integrate DALL-E/Stable Diffusion
7. **Component Library:** Reusable component picker
8. **Code Formatting:** Auto-format with Prettier
9. **Live Preview URL:** Temporary hosted preview link
10. **Analytics:** Track generation metrics, popular prompts

---

## Dependencies to Install

**Backend:**
```bash
composer require league/flysystem-ziparchive
composer require guzzlehttp/guzzle  # For API calls
```

**Frontend:**
```bash
npm install --save-dev @monaco-editor/loader monaco-editor  # Code editor
npm install prismjs  # Syntax highlighting (alternative to Monaco)
npm install file-saver  # Client-side file downloads
npm install splitpanes  # Resizable split panels
```

---

## Navigation Integration

Add to sidebar navigation (`resources/js/components/layout/Sidebar.vue`):
```javascript
{
  name: 'Website Generator',
  icon: 'fa-wand-magic-sparkles',
  route: 'admin.website-generator.index',
  children: [
    { name: 'Projects', route: 'admin.website-generator.index' },
    { name: 'Create New', route: 'admin.website-generator.create' },
    { name: 'API Keys', route: 'admin.website-generator.api-keys' },
  ]
}
```

---

This plan follows your application's architecture patterns (Resources, Services, API layers) and integrates seamlessly with existing authentication, settings, and UI components. Ready to implement when you approve! 🚀
