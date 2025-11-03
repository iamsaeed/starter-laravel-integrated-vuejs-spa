# Editor.js Data Conversion Strategy

## Overview

This document explains how existing task descriptions (plain text) will be converted to Editor.js JSON format during migration.

---

## Current State

### Database Schema
```sql
-- Current: tasks table
description TEXT NULL
```

**Example Data:**
```
"This is a simple task description with plain text."

"Multi-line description
with line breaks
and multiple paragraphs."

"A description with special characters: <html>, &amp;, \"quotes\""
```

---

## Target State

### Database Schema
```sql
-- After Migration: tasks table
description JSON NULL
```

**Example Data (Editor.js Format):**
```json
{
  "time": 1705320000000,
  "blocks": [
    {
      "id": "abc123",
      "type": "paragraph",
      "data": {
        "text": "This is a simple task description with plain text."
      }
    }
  ],
  "version": "2.28.0"
}
```

---

## Conversion Strategy

### Option 1: Simple Single Paragraph (Recommended for Phase 1)

**Approach:**
- Convert entire text description into a single paragraph block
- Preserve line breaks as-is within the paragraph
- Fastest, safest conversion

**Example:**
```php
// Input (plain text)
"Fix login bug\nCheck authentication flow\nTest on staging"

// Output (Editor.js JSON)
{
  "time": 1705320000000,
  "blocks": [
    {
      "id": "unique_id_1",
      "type": "paragraph",
      "data": {
        "text": "Fix login bug\nCheck authentication flow\nTest on staging"
      }
    }
  ],
  "version": "2.28.0"
}
```

**Pros:**
- ✅ Simple, fast conversion
- ✅ No data loss
- ✅ Preserves original formatting
- ✅ Low risk of errors

**Cons:**
- ⚠️ Doesn't split into multiple blocks
- ⚠️ Line breaks shown as `\n` in JSON

---

### Option 2: Smart Multi-Block Conversion (Enhanced)

**Approach:**
- Split text by double line breaks (`\n\n`) into separate paragraph blocks
- Detect headings (lines starting with #, ##, etc.)
- Detect lists (lines starting with -, *, 1., etc.)
- Convert to appropriate block types

**Example:**
```php
// Input (plain text with structure)
"Task Overview

This is the main description.

Steps to reproduce:
- Step 1
- Step 2
- Step 3

Expected behavior:
Should work correctly."

// Output (Editor.js JSON)
{
  "time": 1705320000000,
  "blocks": [
    {
      "id": "unique_id_1",
      "type": "header",
      "data": {
        "text": "Task Overview",
        "level": 2
      }
    },
    {
      "id": "unique_id_2",
      "type": "paragraph",
      "data": {
        "text": "This is the main description."
      }
    },
    {
      "id": "unique_id_3",
      "type": "paragraph",
      "data": {
        "text": "Steps to reproduce:"
      }
    },
    {
      "id": "unique_id_4",
      "type": "list",
      "data": {
        "style": "unordered",
        "items": [
          "Step 1",
          "Step 2",
          "Step 3"
        ]
      }
    },
    {
      "id": "unique_id_5",
      "type": "paragraph",
      "data": {
        "text": "Expected behavior:"
      }
    },
    {
      "id": "unique_id_6",
      "type": "paragraph",
      "data": {
        "text": "Should work correctly."
      }
    }
  ],
  "version": "2.28.0"
}
```

**Pros:**
- ✅ Better structure
- ✅ More readable in editor
- ✅ Preserves semantic meaning
- ✅ Easier to edit after migration

**Cons:**
- ⚠️ More complex conversion logic
- ⚠️ Risk of misinterpreting structure
- ⚠️ Longer migration time

---

## Migration Implementation

### Phase 1 Migration (Simple Conversion)

**File**: `database/migrations/tenant/tasks/2025_01_15_000001_convert_tasks_description_to_json.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Convert existing text data to Editor.js JSON
        $this->convertTextToEditorJs();

        // Step 2: Change column type to JSON
        Schema::table('tasks', function (Blueprint $table) {
            $table->json('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Step 1: Extract plain text from JSON
        $this->convertEditorJsToText();

        // Step 2: Change column type back to TEXT
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Convert plain text descriptions to Editor.js JSON format
     */
    private function convertTextToEditorJs(): void
    {
        DB::table('tasks')->whereNotNull('description')->chunk(100, function ($tasks) {
            foreach ($tasks as $task) {
                // Skip if already JSON
                if ($this->isAlreadyJson($task->description)) {
                    continue;
                }

                // Convert to Editor.js format
                $editorData = $this->textToEditorJs($task->description);

                // Update task
                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update(['description' => json_encode($editorData)]);
            }
        });
    }

    /**
     * Convert Editor.js JSON back to plain text
     */
    private function convertEditorJsToText(): void
    {
        DB::table('tasks')->whereNotNull('description')->chunk(100, function ($tasks) {
            foreach ($tasks as $task) {
                $data = json_decode($task->description, true);

                if (!is_array($data) || !isset($data['blocks'])) {
                    continue;
                }

                // Extract text from all blocks
                $text = collect($data['blocks'])
                    ->map(function ($block) {
                        return $this->extractTextFromBlock($block);
                    })
                    ->filter()
                    ->implode("\n\n");

                // Update task
                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update(['description' => $text]);
            }
        });
    }

    /**
     * Check if description is already JSON
     */
    private function isAlreadyJson(string $description): bool
    {
        $decoded = json_decode($description, true);
        return is_array($decoded) && isset($decoded['blocks']);
    }

    /**
     * Convert plain text to Editor.js format (Simple approach)
     */
    private function textToEditorJs(string $text): array
    {
        // Simple approach: Single paragraph block
        return [
            'time' => now()->timestamp * 1000, // milliseconds
            'blocks' => [
                [
                    'id' => uniqid('block_'),
                    'type' => 'paragraph',
                    'data' => [
                        'text' => $this->escapeHtml($text),
                    ],
                ],
            ],
            'version' => '2.28.0',
        ];
    }

    /**
     * Extract text from a single Editor.js block
     */
    private function extractTextFromBlock(array $block): ?string
    {
        $type = $block['type'] ?? 'paragraph';
        $data = $block['data'] ?? [];

        switch ($type) {
            case 'paragraph':
            case 'header':
                return $data['text'] ?? '';

            case 'list':
                $items = $data['items'] ?? [];
                return collect($items)
                    ->map(fn($item) => "- {$item}")
                    ->implode("\n");

            case 'quote':
                $text = $data['text'] ?? '';
                $caption = $data['caption'] ?? '';
                return $caption ? "\"{$text}\" - {$caption}" : "\"{$text}\"";

            case 'code':
                return $data['code'] ?? '';

            default:
                return $data['text'] ?? '';
        }
    }

    /**
     * Escape HTML entities in text
     */
    private function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
    }
};
```

---

### Advanced Migration (Smart Multi-Block Conversion)

**File**: `database/migrations/tenant/tasks/2025_01_15_000002_convert_tasks_description_to_json_advanced.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only run if you want enhanced conversion
        $this->convertTextToEditorJsAdvanced();
    }

    public function down(): void
    {
        // Same as simple migration
    }

    /**
     * Advanced conversion with block detection
     */
    private function convertTextToEditorJsAdvanced(): void
    {
        DB::table('tasks')->whereNotNull('description')->chunk(100, function ($tasks) {
            foreach ($tasks as $task) {
                if ($this->isAlreadyJson($task->description)) {
                    continue;
                }

                $editorData = $this->textToEditorJsAdvanced($task->description);

                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update(['description' => json_encode($editorData)]);
            }
        });
    }

    /**
     * Convert text to Editor.js with intelligent block detection
     */
    private function textToEditorJsAdvanced(string $text): array
    {
        $blocks = [];

        // Split by double line breaks for paragraphs
        $paragraphs = preg_split('/\n\s*\n/', trim($text));

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if (empty($paragraph)) {
                continue;
            }

            // Detect heading (lines starting with #)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $paragraph, $matches)) {
                $level = strlen($matches[1]);
                $blocks[] = [
                    'id' => uniqid('block_'),
                    'type' => 'header',
                    'data' => [
                        'text' => htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8'),
                        'level' => $level,
                    ],
                ];
                continue;
            }

            // Detect unordered list
            if (preg_match_all('/^[\-\*]\s+(.+)$/m', $paragraph, $matches)) {
                $blocks[] = [
                    'id' => uniqid('block_'),
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => array_map(
                            fn($item) => htmlspecialchars(trim($item), ENT_QUOTES, 'UTF-8'),
                            $matches[1]
                        ),
                    ],
                ];
                continue;
            }

            // Detect ordered list
            if (preg_match_all('/^\d+\.\s+(.+)$/m', $paragraph, $matches)) {
                $blocks[] = [
                    'id' => uniqid('block_'),
                    'type' => 'list',
                    'data' => [
                        'style' => 'ordered',
                        'items' => array_map(
                            fn($item) => htmlspecialchars(trim($item), ENT_QUOTES, 'UTF-8'),
                            $matches[1]
                        ),
                    ],
                ];
                continue;
            }

            // Detect code block (indented or surrounded by backticks)
            if (preg_match('/^```(.+?)```$/s', $paragraph, $matches)) {
                $blocks[] = [
                    'id' => uniqid('block_'),
                    'type' => 'code',
                    'data' => [
                        'code' => trim($matches[1]),
                    ],
                ];
                continue;
            }

            // Default: Regular paragraph
            $blocks[] = [
                'id' => uniqid('block_'),
                'type' => 'paragraph',
                'data' => [
                    'text' => htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8'),
                ],
            ];
        }

        return [
            'time' => now()->timestamp * 1000,
            'blocks' => $blocks,
            'version' => '2.28.0',
        ];
    }

    private function isAlreadyJson(string $description): bool
    {
        $decoded = json_decode($description, true);
        return is_array($decoded) && isset($decoded['blocks']);
    }
};
```

---

## Data Conversion Examples

### Example 1: Simple Text

**Before:**
```
"Update the user authentication flow to support OAuth"
```

**After (Simple):**
```json
{
  "time": 1705320000000,
  "blocks": [
    {
      "id": "block_abc123",
      "type": "paragraph",
      "data": {
        "text": "Update the user authentication flow to support OAuth"
      }
    }
  ],
  "version": "2.28.0"
}
```

---

### Example 2: Multi-line Text

**Before:**
```
"Bug in login page
Users cannot login with special characters in password
Need to update validation"
```

**After (Simple):**
```json
{
  "time": 1705320000000,
  "blocks": [
    {
      "id": "block_abc123",
      "type": "paragraph",
      "data": {
        "text": "Bug in login page\nUsers cannot login with special characters in password\nNeed to update validation"
      }
    }
  ],
  "version": "2.28.0"
}
```

**After (Advanced):**
```json
{
  "time": 1705320000000,
  "blocks": [
    {
      "id": "block_abc123",
      "type": "paragraph",
      "data": {
        "text": "Bug in login page"
      }
    },
    {
      "id": "block_def456",
      "type": "paragraph",
      "data": {
        "text": "Users cannot login with special characters in password"
      }
    },
    {
      "id": "block_ghi789",
      "type": "paragraph",
      "data": {
        "text": "Need to update validation"
      }
    }
  ],
  "version": "2.28.0"
}
```

---

### Example 3: Structured Text

**Before:**
```
"## Sprint Planning

Tasks for this sprint:
- Implement OAuth
- Update documentation
- Write tests

Expected completion: 2 weeks"
```

**After (Advanced Only):**
```json
{
  "time": 1705320000000,
  "blocks": [
    {
      "id": "block_1",
      "type": "header",
      "data": {
        "text": "Sprint Planning",
        "level": 2
      }
    },
    {
      "id": "block_2",
      "type": "paragraph",
      "data": {
        "text": "Tasks for this sprint:"
      }
    },
    {
      "id": "block_3",
      "type": "list",
      "data": {
        "style": "unordered",
        "items": [
          "Implement OAuth",
          "Update documentation",
          "Write tests"
        ]
      }
    },
    {
      "id": "block_4",
      "type": "paragraph",
      "data": {
        "text": "Expected completion: 2 weeks"
      }
    }
  ],
  "version": "2.28.0"
}
```

---

## Edge Cases & Handling

### Empty Descriptions
```php
// Input: null or empty string
null

// Output: Empty blocks array
{
  "time": 1705320000000,
  "blocks": [],
  "version": "2.28.0"
}
```

### HTML in Text
```php
// Input: Text with HTML tags
"Fix the <div> rendering issue"

// Output: HTML escaped
{
  "blocks": [
    {
      "type": "paragraph",
      "data": {
        "text": "Fix the &lt;div&gt; rendering issue"
      }
    }
  ]
}
```

### Special Characters
```php
// Input: Text with quotes, ampersands
"Update \"config\" & settings"

// Output: Properly escaped
{
  "blocks": [
    {
      "type": "paragraph",
      "data": {
        "text": "Update &quot;config&quot; &amp; settings"
      }
    }
  ]
}
```

---

## Migration Performance

### Chunking Strategy
```php
// Process in batches of 100 to avoid memory issues
DB::table('tasks')->whereNotNull('description')->chunk(100, function ($tasks) {
    foreach ($tasks as $task) {
        // Convert and update
    }
});
```

### Estimated Time
- **1,000 tasks**: ~10-30 seconds (simple conversion)
- **10,000 tasks**: ~1-3 minutes (simple conversion)
- **100,000 tasks**: ~10-30 minutes (simple conversion)

**Advanced conversion** will take 2-3x longer due to regex processing.

---

## Rollback Strategy

### Automatic Rollback (migration down())
```php
public function down(): void
{
    // Extract text from JSON
    $this->convertEditorJsToText();

    // Change column back to text
    Schema::table('tasks', function (Blueprint $table) {
        $table->text('description')->nullable()->change();
    });
}
```

### Manual Rollback (if needed)
```sql
-- If migration fails mid-way
UPDATE tasks
SET description = JSON_UNQUOTE(JSON_EXTRACT(description, '$.blocks[0].data.text'))
WHERE JSON_VALID(description);
```

---

## Recommendation

### For Production Launch: **Simple Conversion (Option 1)**

**Reasons:**
1. ✅ **Safe** - Low risk, simple logic
2. ✅ **Fast** - Quick migration even with many records
3. ✅ **Reliable** - No complex parsing that could fail
4. ✅ **Reversible** - Easy rollback
5. ✅ **Good enough** - Users can manually format after migration

### For Future Enhancement: **Advanced Conversion (Option 2)**

**When to use:**
- After initial launch is stable
- If users complain about formatting
- Can run as separate optional migration
- Or provide a "Re-format description" button in UI

---

## Testing Migration

### Test on Staging First

```bash
# 1. Backup database
mysqldump saas_test > backup_before_migration.sql

# 2. Run migration
php artisan migrate

# 3. Check sample tasks
php artisan tinker
>>> Task::find(1)->description
>>> Task::find(2)->description

# 4. Rollback if issues
php artisan migrate:rollback

# 5. Restore backup if needed
mysql saas_test < backup_before_migration.sql
```

### Sample Test Cases

```php
// Test in tinker
$task = Task::find(1);
$task->description; // Should be array
$task->description['blocks']; // Should have blocks
$task->description_text; // Should have plain text (if accessor added)
```

---

## Conclusion

**Recommended Approach:**
1. Start with **Simple Conversion (Option 1)** for production safety
2. Monitor user feedback
3. Optionally add **Advanced Conversion (Option 2)** later if needed
4. Always test on staging first
5. Always have database backup before migration

This ensures a smooth, safe migration with minimal risk to production data.
