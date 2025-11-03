# Email Notification Management System

## **Architectural Approach**

### **Blade Templates Stored in Database**

Store complete Blade templates in the database and use Laravel's native `Blade::render()` to process them. This approach provides maximum flexibility while leveraging Laravel's built-in template engine.

**How It Works:**
1. Admin creates/edits email templates using Monaco code editor
2. Template content (Blade syntax) is saved to database
3. When sending email, retrieve template and render with `Blade::render()`
4. No custom compilation needed - Laravel handles everything

**Pros:**
- Maximum simplicity - use Laravel's native Blade rendering
- No custom parser or compilation logic needed
- Full Blade power (loops, conditionals, formatting, relationships)
- No code deployments for template changes
- Version history tracking possible
- A/B testing capabilities
- Emails are queueable for better performance
- Admin has complete control over HTML/CSS

**Cons:**
- Requires Blade syntax knowledge for advanced features
- Security validation needed to prevent malicious code
- Admin must understand basic Blade directives

**Note:** Multi-language support and external service integration are not included in the current implementation.

---

## **Template Management System Design**

### **Database Schema**
```
email_templates
├── id
├── key (unique: 'user_welcome', 'password_reset')
├── name ('User Welcome Email')
├── subject_template (Blade: 'Order #{{ $order->number }}')
├── body_content (Complete Blade template with full HTML structure)
├── preview_thumbnail (text: cached base64 or URL to thumbnail image)
├── is_active
├── created_by/updated_by
└── timestamps
```

**Note:** Each template contains its complete HTML structure (including layout, header, footer, styles). No separate layouts table - every template is self-contained.

### **Admin Interface Features**

#### **Monaco Code Editor (Primary Editor)**

The system uses **Monaco Editor** (VS Code engine) as the primary template editor:

**Features:**
- Full Blade syntax highlighting
- Autocomplete for Blade directives
- Variable reference sidebar
- Snippet insertion for common patterns (`@foreach`, `@if`, etc.)
- Real-time syntax validation
- Preview panel showing rendered output with sample data
- Split view: code on left, preview on right

**Blade Directives Supported:**
- Loops: `@foreach`, `@forelse`, `@endforeach`
- Conditionals: `@if`, `@elseif`, `@else`, `@endif`, `@unless`, `@isset`, `@empty`
- Output: `{{ $variable }}`, `{!! $html !!}`
- Formatting: Laravel helper functions and model methods

**Security Restrictions:**
- `@php` blocks - **Blocked**
- `@include`, `@extends` - **Blocked**
- `system()`, `exec()`, `eval()` - **Blocked**
- File access functions - **Blocked**

---

## **Blade Variable System**

### **Variable Registry for Documentation (Optional)**

The Variable Registry is an **optional helper service** that documents what data is available to each template type. It serves as:
- **Documentation** for admins showing available variables
- **UI Helper** for the variable reference sidebar in the editor
- **Sample Data Generator** for template previews

**Important:** This registry is for **documentation and UX purposes only**. Since templates use Blade, admins can reference any variables passed to the template - the registry does not enforce or restrict what variables can be used. It simply provides helpful documentation and examples.

```php
// app/Services/EmailVariableRegistry.php
class EmailVariableRegistry
{
    public function getVariablesForTemplate(string $key): array
    {
        return match($key) {
            'user_welcome' => [
                'user' => [
                    'label' => 'User Object',
                    'type' => 'object',
                    'description' => 'The registered user',
                    'properties' => [
                        'name' => 'string - User full name',
                        'email' => 'string - User email address',
                        'created_at' => 'Carbon - Registration date',
                        'isVerified()' => 'bool - Email verification status',
                    ],
                    'example' => '{{ $user->name }}, {{ $user->email }}',
                ],
                'verification_url' => [
                    'label' => 'Email Verification URL',
                    'type' => 'string',
                    'example' => '<a href="{{ $verification_url }}">Verify Email</a>',
                ],
            ],
            'order_confirmation' => [
                'user' => [
                    'label' => 'User Object',
                    'type' => 'object',
                    'properties' => [
                        'name' => 'string',
                        'email' => 'string',
                    ],
                ],
                'order' => [
                    'label' => 'Order Object',
                    'type' => 'object',
                    'properties' => [
                        'number' => 'string - Order number',
                        'status' => 'string - Order status',
                        'created_at' => 'Carbon - Order date',
                        'subtotal' => 'float - Subtotal amount',
                        'tax' => 'float - Tax amount',
                        'discount' => 'float - Discount amount',
                        'total' => 'float - Total amount',
                        'items' => 'Collection<OrderItem> - Order items',
                        'shipping_address' => 'Address|null - Shipping address',
                    ],
                    'relationships' => [
                        'items' => [
                            'type' => 'Collection<OrderItem>',
                            'properties' => [
                                'product->name' => 'string',
                                'quantity' => 'int',
                                'unit_price' => 'float',
                                'total' => 'float',
                            ],
                            'example' => '@foreach($order->items as $item) {{ $item->product->name }} @endforeach',
                        ],
                    ],
                ],
            ],
            default => []
        };
    }

    public function getSampleData(string $key): array
    {
        return match($key) {
            'user_welcome' => [
                'user' => User::factory()->make(['name' => 'John Doe', 'email' => 'john@example.com']),
                'verification_url' => 'https://example.com/verify/sample-token',
            ],
            'order_confirmation' => [
                'user' => User::factory()->make(['name' => 'Jane Smith']),
                'order' => Order::factory()
                    ->has(OrderItem::factory()->count(3), 'items')
                    ->make(['number' => 'ORD-12345', 'total' => 150.00]),
            ],
            default => []
        };
    }
}
```

**Note:** If you choose not to implement the Variable Registry, you can:
- Remove the variable reference sidebar from the editor
- Manually define sample data for previews
- Provide variable documentation through other means (wiki, comments, etc.)

The system will function perfectly without it - admins will just need to know what variables are available from your notification/mailable implementations.

### **Example Template Usage**

**User Welcome Email:**
```blade
{{-- Subject Template --}}
Welcome to {{ config('app.name') }}, {{ $user->name }}!

{{-- Body Content --}}
<h1>Welcome, {{ $user->name }}!</h1>

<p>Thank you for registering with {{ config('app.name') }}.</p>

@if(!$user->isVerified())
<p>Please verify your email address:</p>
<a href="{{ $verification_url }}" class="btn">Verify Email</a>
@endif

<p>Registration Date: {{ $user->created_at->format('F d, Y') }}</p>
```

**Order Confirmation Email:**
```blade
{{-- Subject Template --}}
Order Confirmation #{{ $order->number }}

{{-- Body Content --}}
<h1>Thank you, {{ $user->name }}!</h1>

<p><strong>Order Number:</strong> {{ $order->number }}</p>
<p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>

@if($order->items->count() > 0)
<h2>Order Items</h2>
<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>${{ number_format($item->unit_price, 2) }}</td>
            <td>${{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>Total:</strong></td>
            <td><strong>${{ number_format($order->total, 2) }}</strong></td>
        </tr>
    </tfoot>
</table>
@else
<p>No items in this order.</p>
@endif

<a href="{{ route('orders.show', $order->id) }}" class="btn">View Order Details</a>
```

---

## **Template Structure**

### **Complete Self-Contained Templates**

Each email template is completely self-contained with full HTML structure. There are no separate layouts - each template includes everything:

```blade
{{-- Example: Complete Order Confirmation Email Template --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
        }
        .email-header {
            background: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 150px;
            height: auto;
        }
        .email-content {
            padding: 30px;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ config('app.logo_url') }}" alt="{{ config('app.name') }}">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="email-content">
            <h1>Thank you, {{ $user->name }}!</h1>

            <p><strong>Order Number:</strong> {{ $order->number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>

            @if($order->items->count() > 0)
            <h2>Order Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>${{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total:</strong></td>
                        <td><strong>${{ number_format($order->total, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
            @endif

            <a href="{{ route('orders.show', $order->id) }}" class="btn">View Order Details</a>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>
                <a href="{{ $unsubscribe_url ?? '#' }}">Unsubscribe</a> |
                <a href="{{ config('app.url') }}">Visit Website</a>
            </p>
        </div>
    </div>
</body>
</html>
```

### **Benefits of Self-Contained Templates:**
- Complete design freedom per template
- No layout inheritance complexity
- Easy to duplicate and customize
- Each template is independent
- Simpler mental model for admins
- No layout management needed

### **Template Duplication Feature:**
To reuse common designs, admins can duplicate existing templates and modify them, rather than using shared layouts.

---

## **Preview & Testing System**

### **Live Preview Features:**

1. **Variable Preview with Sample Data**
   - Populate variables with factory-generated sample data
   - Admin sees exactly how email looks with real data
   - Toggle between different sample scenarios (new user, returning user, etc.)

2. **Device Preview**
   - Desktop/Mobile views
   - Responsive layout testing

3. **Send Test Email with Demo Data**
   - Send to one or multiple email addresses specified by admin
   - Uses sample data from factories to replace all variables
   - Allows admin to view the email in real email clients (Gmail, Outlook, etc.)
   - Available while editing the template (before saving)
   - Validates email addresses before sending
   - Shows success/error notification after sending

4. **Blade Syntax Validation**
   - Real-time syntax checking
   - Error highlighting in editor
   - Security validation (blocked directives)

### **Implementation:**

```javascript
// Vue component for preview
<template>
  <div class="email-editor">
    <div class="editor-toolbar">
      <select v-model="selectedSampleData">
        <option value="default">Default Sample Data</option>
        <option value="scenario1">New User</option>
        <option value="scenario2">Returning Customer</option>
      </select>

      <input
        v-model="testEmails"
        type="text"
        placeholder="Enter email addresses (comma-separated)"
        class="test-email-input"
      />

      <button @click="sendTestEmail" :disabled="!testEmails || sending">
        {{ sending ? 'Sending...' : 'Send Test Email' }}
      </button>

      <button @click="validateSyntax">
        <Icon name="check" /> Validate Syntax
      </button>
    </div>

    <div class="editor-layout">
      <div class="editor-panel">
        <MonacoEditor
          v-model="template.body_content"
          language="blade"
          :options="editorOptions"
          @change="onTemplateChange"
        />

        <div v-if="syntaxErrors.length" class="syntax-errors">
          <div v-for="error in syntaxErrors" :key="error.line" class="error">
            Line {{ error.line }}: {{ error.message }}
          </div>
        </div>
      </div>

      <div class="preview-panel">
        <div class="preview-controls">
          <button @click="deviceView = 'desktop'" :class="{ active: deviceView === 'desktop' }">
            Desktop
          </button>
          <button @click="deviceView = 'mobile'" :class="{ active: deviceView === 'mobile' }">
            Mobile
          </button>
        </div>

        <iframe
          :srcdoc="previewHtml"
          :class="`preview-${deviceView}`"
          class="email-preview"
        ></iframe>
      </div>

      <div class="variable-reference">
        <h3>Available Variables</h3>
        <div v-for="(variable, key) in availableVariables" :key="key" class="variable-item">
          <strong>{{ variable.label }}</strong>
          <code>{{ variable.example }}</code>
          <button @click="insertVariable(variable.example)">Insert</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { emailTemplateService } from '@/services/emailTemplateService';

const template = ref({});
const previewHtml = ref('');
const syntaxErrors = ref([]);
const sending = ref(false);

async function onTemplateChange() {
  // Debounced preview update
  await updatePreview();
}

async function updatePreview() {
  try {
    const response = await emailTemplateService.preview(
      template.value.id,
      selectedSampleData.value
    );
    previewHtml.value = response.html;
    syntaxErrors.value = [];
  } catch (error) {
    if (error.response?.data?.syntax_errors) {
      syntaxErrors.value = error.response.data.syntax_errors;
    }
  }
}

async function sendTestEmail() {
  sending.value = true;
  try {
    await emailTemplateService.sendTest(
      template.value.id,
      testEmails.value.split(',').map(e => e.trim()),
      selectedSampleData.value
    );
    showToast({ message: 'Test email sent successfully!', type: 'success' });
  } catch (error) {
    showToast({ message: 'Failed to send test email', type: 'error' });
  } finally {
    sending.value = false;
  }
}

function insertVariable(example) {
  // Insert at cursor position in Monaco editor
}
</script>

<style scoped>
.editor-layout {
  display: grid;
  grid-template-columns: 1fr 1fr 300px;
  gap: 1rem;
  height: calc(100vh - 200px);
}

.editor-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.preview-panel {
  display: flex;
  flex-direction: column;
  height: 50vh; /* Half of viewport height */
}

.email-preview {
  flex: 1;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
}

.variable-reference {
  overflow-y: auto;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 4px;
}
</style>
```

---

## **Admin CRUD Interface**

### **Index View - Card-Based Layout**

The email template index page uses a **card-based layout** instead of the traditional table view:

**Features:**
- Each card displays a live preview thumbnail of the email template
- Template name shown below the thumbnail
- Card actions: Edit, Delete, Duplicate, Toggle Active
- Filtering and search capabilities
- Responsive grid layout (3-4 columns on desktop, 1-2 on mobile)
- Hover effects showing additional metadata (last updated, status)
- Quick preview on card click (modal with full email preview)

**Implementation:**
- Custom Vue component for card grid layout
- Generate thumbnail by rendering template with sample data at reduced scale
- Cache thumbnails for performance
- Update thumbnail on template save

### **Backend Controller & Routes**

```php
// app/Http/Controllers/Admin/EmailTemplateController.php
class EmailTemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateService $templateService
    ) {}

    public function index(Request $request)
    {
        $templates = EmailTemplate::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->filter_active !== null, fn($q) => $q->where('is_active', $request->filter_active))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $templates->items(),
            'meta' => [
                'total' => $templates->total(),
                'per_page' => $templates->perPage(),
                'current_page' => $templates->currentPage(),
            ],
        ]);
    }

    public function store(EmailTemplateRequest $request)
    {
        $template = EmailTemplate::create($request->validated());

        // Generate thumbnail
        $this->generateThumbnail($template);

        return response()->json(['data' => $template], 201);
    }

    public function show(EmailTemplate $template)
    {
        return response()->json(['data' => $template]);
    }

    public function update(EmailTemplateRequest $request, EmailTemplate $template)
    {
        $template->update($request->validated());

        // Regenerate thumbnail
        $this->generateThumbnail($template);

        return response()->json(['data' => $template]);
    }

    public function destroy(EmailTemplate $template)
    {
        $template->delete();

        return response()->json(null, 204);
    }

    public function duplicate(EmailTemplate $template)
    {
        $duplicate = $template->replicate()->fill([
            'key' => $template->key . '_copy_' . now()->timestamp,
            'name' => $template->name . ' (Copy)',
        ]);
        $duplicate->save();

        $this->generateThumbnail($duplicate);

        return response()->json(['data' => $duplicate], 201);
    }

    public function preview(Request $request, EmailTemplate $template)
    {
        $sampleData = app(EmailVariableRegistry::class)->getSampleData($template->key);

        $rendered = $this->templateService->render($template->key, $sampleData);

        return response()->json($rendered);
    }

    public function sendTest(Request $request, EmailTemplate $template)
    {
        $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'required|email',
        ]);

        $sampleData = app(EmailVariableRegistry::class)->getSampleData($template->key);

        $this->templateService->sendTest($template, $request->emails, $sampleData);

        return response()->json(['message' => 'Test emails sent successfully']);
    }

    protected function generateThumbnail(EmailTemplate $template): void
    {
        // Generate thumbnail logic here
        // Could use a service like Browsershot or simple HTML to image conversion
    }
}
```

### **Routes**

```php
// routes/web.php or routes/api.php
Route::prefix('admin/email-templates')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/', [EmailTemplateController::class, 'index']);
    Route::post('/', [EmailTemplateController::class, 'store']);
    Route::get('/{template}', [EmailTemplateController::class, 'show']);
    Route::put('/{template}', [EmailTemplateController::class, 'update']);
    Route::delete('/{template}', [EmailTemplateController::class, 'destroy']);
    Route::post('/{template}/duplicate', [EmailTemplateController::class, 'duplicate']);
    Route::post('/{template}/preview', [EmailTemplateController::class, 'preview']);
    Route::post('/{template}/send-test', [EmailTemplateController::class, 'sendTest']);
});
```

### **Frontend Vue Components**

**EmailTemplateIndex.vue** - Card grid view
**EmailTemplateForm.vue** - Create/edit form with Monaco editor
**EmailTemplatePreview.vue** - Preview modal component
**BladeTemplateEditor.vue** - Monaco editor with Blade syntax highlighting, variable reference sidebar, preview panel, and syntax validation

---

## **Implementation Strategy**

### **Phase 1: Foundation**
1. Database migration for `email_templates` table
2. `EmailTemplate` model
3. Blade rendering service (`EmailTemplateService`)
4. Security validation service (`BladeTemplateSecurityService`)
5. Basic controller and routes for CRUD operations

### **Phase 2: Admin Interface**
1. Vue components for email template management
2. Card-based index view with thumbnails
3. Create/edit form with Monaco editor
4. Template duplication feature
5. Filtering and search functionality

### **Phase 3: Editor Features**
1. Monaco editor with Blade syntax highlighting
2. Variable reference sidebar (optional - using EmailVariableRegistry)
3. Snippet insertion for common Blade patterns
4. Basic preview with sample data
5. Syntax validation and error highlighting

### **Phase 4: Advanced Features**
1. Live preview with real-time rendering
2. Test email sending with demo data to actual email addresses
3. Multiple sample data scenarios
4. Device preview (desktop/mobile toggle)
5. Thumbnail generation and caching

### **Phase 5: Integration**
1. Create notification classes for common events
2. Seed default templates with complete HTML (welcome, password reset, etc.)
3. Queue integration for email sending
4. Fallback system for missing/inactive templates
5. Email tracking and analytics (optional)

---

## **Email Rendering Service**

### **Simple Service Layer**

```php
// app/Services/EmailTemplateService.php
class EmailTemplateService
{
    public function render(string $templateKey, array $data): array
    {
        $template = EmailTemplate::where('key', $templateKey)
            ->where('is_active', true)
            ->firstOrFail();

        // Validate template security before rendering
        $this->validateTemplate($template);

        try {
            // Render subject
            $subject = Blade::render($template->subject_template, $data);

            // Render complete body content (includes full HTML structure)
            $html = Blade::render($template->body_content, $data);

            return [
                'subject' => trim($subject),
                'html' => $html,
            ];

        } catch (\Throwable $e) {
            Log::error('Email template rendering failed', [
                'template_key' => $templateKey,
                'error' => $e->getMessage(),
            ]);

            // Fallback to default template or throw
            throw new EmailTemplateRenderException(
                "Failed to render email template: {$templateKey}",
                previous: $e
            );
        }
    }

    public function preview(EmailTemplate $template, array $sampleData): array
    {
        return $this->render($template->key, $sampleData);
    }

    public function sendTest(EmailTemplate $template, array $emails, array $sampleData): void
    {
        $rendered = $this->render($template->key, $sampleData);

        foreach ($emails as $email) {
            Mail::html($rendered['html'], function($message) use ($rendered, $email) {
                $message->to($email)
                        ->subject('[TEST] ' . $rendered['subject']);
            });
        }
    }

    protected function validateTemplate(EmailTemplate $template): void
    {
        app(BladeTemplateSecurityService::class)->validate($template->body_content);
        app(BladeTemplateSecurityService::class)->validate($template->subject_template);
    }
}
```

---

## **Integration with Laravel Notifications**

### **Notification Implementation:**

```php
// app/Notifications/OrderConfirmationNotification.php
class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    public function __construct(
        protected Order $order
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $rendered = app(EmailTemplateService::class)->render('order_confirmation', [
            'user' => $notifiable,
            'order' => $this->order->load(['items.product', 'shipping_address']),
            'unsubscribe_url' => route('unsubscribe', ['token' => $notifiable->unsubscribe_token]),
        ]);

        return (new MailMessage)
            ->subject($rendered['subject'])
            ->html($rendered['html']);
    }
}
```

### **Mailable Implementation (Alternative):**

```php
// app/Mail/TemplatedMail.php
class TemplatedMail extends Mailable
{
    public function __construct(
        protected string $templateKey,
        protected array $data
    ) {}

    public function build(): self
    {
        $rendered = app(EmailTemplateService::class)->render(
            $this->templateKey,
            $this->data
        );

        return $this->subject($rendered['subject'])
                    ->html($rendered['html']);
    }
}

// Usage
Mail::to($user)->queue(new TemplatedMail('user_welcome', [
    'user' => $user,
    'verification_url' => $verificationUrl,
]));
```

---

## **Security Validation**

### **Blade Template Security Service**

```php
// app/Services/BladeTemplateSecurityService.php
class BladeTemplateSecurityService
{
    protected array $blockedDirectives = [
        'php', 'endphp',           // Raw PHP execution
        'include', 'includeIf',    // File inclusion
        'extends', 'section',      // Layout inheritance
        'component', 'slot',       // Component system
    ];

    protected array $blockedPatterns = [
        '/<\?php/i',                        // PHP tags
        '/\bsystem\s*\(/i',                // System calls
        '/\bexec\s*\(/i',                  // Exec
        '/\beval\s*\(/i',                  // Eval
        '/\bshell_exec\s*\(/i',            // Shell exec
        '/\bpassthru\s*\(/i',              // Passthru
        '/\bfile_get_contents\s*\(/i',     // File read
        '/\bfile_put_contents\s*\(/i',     // File write
        '/\bfopen\s*\(/i',                 // File open
        '/\bunlink\s*\(/i',                // File delete
        '/\brmdir\s*\(/i',                 // Directory delete
    ];

    public function validate(string $content): void
    {
        // Check for blocked directives
        foreach ($this->blockedDirectives as $directive) {
            if (preg_match("/@{$directive}\b/i", $content)) {
                throw new BladeSecurityException(
                    "Blocked directive @{$directive} found in template"
                );
            }
        }

        // Check for blocked patterns
        foreach ($this->blockedPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new BladeSecurityException(
                    "Blocked pattern detected in template: {$pattern}"
                );
            }
        }

        // Validate Blade syntax
        try {
            Blade::compileString($content);
        } catch (\Throwable $e) {
            throw new BladeSyntaxException(
                "Invalid Blade syntax: " . $e->getMessage(),
                previous: $e
            );
        }
    }

    public function validateAndGetErrors(string $content): array
    {
        $errors = [];

        foreach ($this->blockedDirectives as $directive) {
            if (preg_match_all("/@{$directive}\b/i", $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $errors[] = [
                        'message' => "Blocked directive @{$directive}",
                        'position' => $match[1],
                    ];
                }
            }
        }

        return $errors;
    }
}
```

### **Form Request Validation:**

```php
// app/Http/Requests/EmailTemplateRequest.php
class EmailTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'key' => 'required|unique:email_templates,key,' . $this->route('id'),
            'name' => 'required|max:255',
            'subject_template' => ['required', 'string', new ValidBladeTemplate],
            'body_content' => ['required', 'string', new ValidBladeTemplate],
            'is_active' => 'boolean',
        ];
    }
}

// app/Rules/ValidBladeTemplate.php
class ValidBladeTemplate implements Rule
{
    public function passes($attribute, $value): bool
    {
        try {
            app(BladeTemplateSecurityService::class)->validate($value);
            return true;
        } catch (BladeSecurityException | BladeSyntaxException $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    public function message(): string
    {
        return $this->message ?? 'The :attribute contains invalid or blocked Blade syntax.';
    }
}
```

---

## **Technical Considerations**

### **Security:**
- **Strict Blade validation** before saving templates
- Block dangerous directives (`@php`, `@include`, etc.)
- Block dangerous functions (`system()`, `exec()`, `eval()`, file operations)
- Validate Blade syntax on save
- Admin-only access with proper authorization
- Try/catch around rendering to prevent crashes

### **Performance:**
- Blade automatically caches compiled templates in `storage/framework/views`
- Queue email sending for non-blocking operations
- Cache rendered thumbnails for index view
- Lazy load preview data
- Use database indexing on `key` and `is_active` fields

### **Validation:**
- Blade syntax validation before save
- Security validation on save and render
- Email address validation for test sending
- Required fields validation

### **Testing:**
- Unit tests for `EmailTemplateService::render()`
- Unit tests for `BladeTemplateSecurityService::validate()`
- Feature tests for template CRUD operations
- Feature tests for email sending
- E2E tests for template editor and preview
- Security tests for blocked directives and patterns

---

## **Recommended Tech Stack**

**Backend:**
- Model: `EmailTemplate`
- Controller: `EmailTemplateController` (independent CRUD controller)
- Service: `EmailTemplateService` (simple Blade rendering)
- Service: `BladeTemplateSecurityService` (validation)
- Service: `EmailVariableRegistry` (optional - for documentation/UX)
- Jobs: `SendTemplatedEmail` (queued)
- Validation: `EmailTemplateRequest`, `ValidBladeTemplate` rule

**Frontend:**
- Editor: **Monaco Editor** with Blade syntax highlighting
- Preview: **iframe** with real-time rendered HTML
- Layout: Split view (code left, preview right, variables sidebar)
- Components:
  - `EmailTemplateIndex.vue` - Card grid view
  - `EmailTemplateForm.vue` - Create/edit form
  - `EmailTemplatePreview.vue` - Preview modal
  - `BladeTemplateEditor.vue` - Monaco editor component
- Service: `emailTemplateService.js` - API calls

**Packages to Consider:**
- `symfony/css-inliner` - Inline CSS for email compatibility (optional)
- Native Laravel Blade - No additional packages needed for rendering

---

## **Summary**

This simplified approach leverages Laravel's native Blade templating system by storing templates in the database and using `Blade::render()` for compilation. This eliminates the need for custom token parsers or compilation logic while providing admins with full control over email content, layouts, and styling.

**Key Benefits:**
- ✅ **Simplicity** - No custom compilation logic
- ✅ **Power** - Full Blade features (loops, conditionals, formatting)
- ✅ **Flexibility** - Admin control without code deployments
- ✅ **Security** - Validation prevents malicious code
- ✅ **Performance** - Blade's built-in caching
- ✅ **Maintainability** - Leverage Laravel's native features
