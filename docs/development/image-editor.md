# Image Editor Documentation

## Overview

The Image Editor provides pre-upload image editing capabilities for the Media field in the Resource CRUD system. Users can crop, resize, rotate, flip, and zoom images before uploading, ensuring perfect image quality and dimensions without requiring external tools.

Built with **Vue Advanced Cropper**, the editor offers a modern, touch-friendly interface with dark mode support.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Features](#features)
3. [Backend Configuration](#backend-configuration)
4. [Frontend Usage](#frontend-usage)
5. [Editor Options](#editor-options)
6. [Complete Examples](#complete-examples)
7. [Advanced Usage](#advanced-usage)
8. [Troubleshooting](#troubleshooting)

---

## Quick Start

### Enable Image Editing for a Media Field

```php
use App\Resources\Fields\Media;

Media::make('Avatar')
    ->single()
    ->collection('avatars')
    ->images()
    ->editable([
        'aspectRatio' => 1,  // Force square crop
        'minWidth' => 200,
        'minHeight' => 200
    ])
```

That's it! Users will now see an "Edit" button on uploaded images and can edit images before uploading new ones.

---

## Features

### ✅ Image Transformations
- **Crop**: Freeform or fixed aspect ratio cropping
- **Rotate**: 90° increments (left/right)
- **Flip**: Horizontal and vertical flipping
- **Zoom**: In/out with precise controls
- **Reset**: Return to original state

### ✅ User Interface
- Modern modal dialog
- Dark mode support
- Touch-friendly (mobile/tablet)
- Real-time preview
- Aspect ratio presets (1:1, 16:9, 4:3, 3:2, Free)
- Keyboard shortcuts (ESC to close)

### ✅ Integration
- Seamless integration with Media field
- Works with single file uploads
- Automatic format conversion (JPEG at 90% quality)
- Preserves original filename
- No backend changes required

---

## Backend Configuration

### Step 1: Add `editable()` Method to Media Field

```php
Media::make('Label')
    ->editable($options)
```

### Available Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `aspectRatio` | `number\|null` | `null` | Fixed aspect ratio (e.g., `1` for square, `16/9` for widescreen) |
| `minWidth` | `int\|null` | `null` | Minimum crop width in pixels |
| `minHeight` | `int\|null` | `null` | Minimum crop height in pixels |

### Option Details

#### `aspectRatio`
- **`null`**: Freeform cropping (user can crop to any ratio)
- **`1`**: Square (1:1) - perfect for avatars
- **`16/9`**: Widescreen (16:9) - perfect for banners
- **`4/3`**: Standard (4:3) - perfect for thumbnails
- **`3/2`**: Classic photo ratio

#### `minWidth` and `minHeight`
Enforce minimum dimensions for the cropped area. Useful for ensuring images meet quality requirements.

---

## Frontend Usage

### How It Works

1. **Upload Flow with Editor:**
   - User selects/drops an image file
   - If `editable` is enabled, Image Editor opens automatically
   - User edits the image (crop, rotate, flip, zoom)
   - User clicks "Save Changes"
   - Edited image is uploaded to the server

2. **Edit Existing Image:**
   - User hovers over uploaded image
   - Clicks the "Edit" button (pencil icon)
   - Image Editor opens with current image
   - User makes changes and saves
   - New edited version is uploaded, replacing the old one

### ImageEditor Component

The `ImageEditor.vue` component is used internally by `MediaUpload.vue`. You don't need to use it directly unless building custom upload components.

#### Props

```vue
<ImageEditor
  :show="true"
  :image-src="imageDataUrl"
  :options="{ aspectRatio: 1, minWidth: 200 }"
  @close="handleClose"
  @save="handleSave"
/>
```

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `show` | `Boolean` | Yes | Controls modal visibility |
| `imageSrc` | `String` | Yes | Image source (data URL or HTTP URL) |
| `options` | `Object` | No | Editor configuration (aspectRatio, minWidth, minHeight) |

#### Events

| Event | Payload | Description |
|-------|---------|-------------|
| `@close` | - | Emitted when user cancels editing |
| `@save` | `{ blob, canvas, options }` | Emitted when user saves edited image |

---

## Complete Examples

### Example 1: Avatar with Square Crop (1:1)

**Use Case:** User profile avatars that must be square.

```php
class UserResource extends Resource
{
    public function fields(): array
    {
        return [
            Media::make('Avatar')
                ->single()
                ->collection('avatars')
                ->images()
                ->maxFileSize(2)
                ->previewSize(48, 48)
                ->rounded()
                ->editable([
                    'aspectRatio' => 1,  // Square only
                    'minWidth' => 200,   // At least 200x200
                    'minHeight' => 200
                ])
                ->rules('nullable'),
        ];
    }
}
```

**User Experience:**
- User uploads/drops an image
- Editor opens with square crop forced
- User can only crop to 1:1 ratio
- Minimum 200x200px enforced
- Perfect for circular avatars

---

### Example 2: Banner with 16:9 Crop

**Use Case:** Website banners with consistent aspect ratio.

```php
Media::make('Banner Image', 'banner')
    ->single()
    ->collection('banners')
    ->images()
    ->maxFileSize(5)
    ->previewSize(320, 180)
    ->editable([
        'aspectRatio' => 16 / 9,  // Widescreen
        'minWidth' => 1280,       // HD width
        'minHeight' => 720        // HD height
    ])
    ->rules('required')
```

**User Experience:**
- User uploads banner image
- Editor forces 16:9 crop
- Minimum 1280x720 enforced (720p)
- Perfect for hero sections

---

### Example 3: Product Image with Free Crop

**Use Case:** Product photos that can be any size/ratio.

```php
Media::make('Product Image', 'image')
    ->single()
    ->collection('products')
    ->images()
    ->maxFileSize(3)
    ->editable([
        // No aspectRatio = freeform cropping
        'minWidth' => 400,
        'minHeight' => 400
    ])
    ->rules('required')
```

**User Experience:**
- User uploads product photo
- Editor allows any crop ratio (Free)
- Minimum 400x400px enforced
- Maximum flexibility

---

### Example 4: Gallery with Optional Editing

**Use Case:** Multiple images where editing is helpful but not enforced.

```php
Media::make('Gallery Images', 'gallery')
    ->multiple(10)
    ->collection('gallery')
    ->images()
    ->maxFileSize(5)
    ->editable([
        'aspectRatio' => 4 / 3  // Standard photo ratio
    ])
    ->rules('nullable|array')
```

**User Experience:**
- User can upload multiple images
- Each image can optionally be edited before upload
- 4:3 aspect ratio suggested
- Great for photo galleries

---

## Advanced Usage

### Custom Aspect Ratios

```php
// Square (Instagram)
->editable(['aspectRatio' => 1])

// Widescreen (YouTube)
->editable(['aspectRatio' => 16 / 9])

// Standard Photo (4:3)
->editable(['aspectRatio' => 4 / 3])

// Classic Photo (3:2)
->editable(['aspectRatio' => 3 / 2])

// Vertical (9:16, Stories)
->editable(['aspectRatio' => 9 / 16])

// Ultra-wide (21:9)
->editable(['aspectRatio' => 21 / 9])

// Free crop (any ratio)
->editable(['aspectRatio' => null])
```

### Conditional Editing

Enable editing only for specific cases:

```php
public function fields(): array
{
    $fields = [
        Media::make('Photo')
            ->single()
            ->collection('photos')
            ->images()
    ];

    // Only enable editing for admins
    if (auth()->user()->isAdmin()) {
        $fields[0]->editable(['aspectRatio' => 16 / 9]);
    }

    return $fields;
}
```

### Combining with Image Conversions

Use Spatie Media Library conversions alongside editing:

```php
// In Model
public function registerMediaCollections(): void
{
    $this->addMediaCollection('banners')
        ->singleFile()
        ->registerMediaConversions(function () {
            // After user edits to 16:9, create responsive versions
            $this->addMediaConversion('thumb')
                ->width(320)
                ->height(180);

            $this->addMediaConversion('medium')
                ->width(640)
                ->height(360);

            $this->addMediaConversion('large')
                ->width(1280)
                ->height(720);

            $this->addMediaConversion('webp')
                ->width(1280)
                ->height(720)
                ->format('webp');
        });
}

// In Resource
Media::make('Banner')
    ->collection('banners')
    ->editable([
        'aspectRatio' => 16 / 9,
        'minWidth' => 1280,
        'minHeight' => 720
    ])
```

**Workflow:**
1. User edits image to 16:9 @ 1280x720
2. Edited image is uploaded
3. Backend generates thumb, medium, large, and WebP versions
4. All images maintain 16:9 ratio

---

## Editor Toolbar Reference

### Aspect Ratio Buttons
- **Free**: Freeform cropping (no constraints)
- **1:1**: Square (avatars, thumbnails)
- **16:9**: Widescreen (banners, videos)
- **4:3**: Standard (photos, presentations)
- **3:2**: Classic photo ratio

*Note: If `aspectRatio` is specified in options, only that ratio is shown.*

### Transform Tools
- **Rotate Left**: Rotate 90° counter-clockwise
- **Rotate Right**: Rotate 90° clockwise
- **Flip Horizontal**: Mirror left/right
- **Flip Vertical**: Mirror top/bottom
- **Zoom In**: Increase zoom by 10%
- **Zoom Out**: Decrease zoom by 10%
- **Reset**: Return to original image state

### Actions
- **Cancel**: Close editor without saving
- **Save Changes**: Process and upload edited image

---

## Technical Details

### Image Processing

- **Output Format**: JPEG at 90% quality
- **Canvas Rendering**: HTML5 Canvas API
- **Blob Conversion**: Client-side processing
- **File Size**: Typically smaller after editing due to compression

### Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

### Performance

- Client-side processing (no server load)
- Lightweight library (~50KB gzipped)
- Smooth 60fps interactions
- Touch gestures optimized

---

## Troubleshooting

### Editor Doesn't Open

**Issue**: Clicking edit button does nothing.

**Solutions:**
1. Ensure `editable` is set on the Media field:
   ```php
   ->editable(['aspectRatio' => 1])
   ```
2. Check browser console for errors
3. Verify vue-advanced-cropper is installed:
   ```bash
   npm install vue-advanced-cropper
   ```

---

### Aspect Ratio Not Locked

**Issue**: User can crop to any ratio even with `aspectRatio` set.

**Solutions:**
1. Ensure `aspectRatio` is a number, not a string:
   ```php
   ->editable(['aspectRatio' => 1])  // ✅ Correct
   ->editable(['aspectRatio' => '1'])  // ❌ Wrong
   ```
2. Check that the value is calculated correctly:
   ```php
   ->editable(['aspectRatio' => 16 / 9])  // ✅ Results in 1.777...
   ```

---

### Edited Image Doesn't Upload

**Issue**: After saving edits, upload fails.

**Solutions:**
1. Check file size limits (edited image might exceed `maxFileSize`)
2. Verify model exists (media requires existing model ID)
3. Check Laravel logs for backend errors:
   ```bash
   php artisan pail
   ```

---

### Poor Image Quality

**Issue**: Edited images look pixelated or blurry.

**Solutions:**
1. Increase minimum dimensions:
   ```php
   ->editable([
       'minWidth' => 800,   // Higher resolution
       'minHeight' => 800
   ])
   ```
2. Adjust quality in `ImageEditor.vue` (if needed):
   ```javascript
   canvas.toBlob((blob) => {
       resolve(blob)
   }, 'image/jpeg', 0.95)  // Increase to 95% quality
   ```

---

### Dark Mode Styling Issues

**Issue**: Editor looks wrong in dark mode.

**Solution:**
The editor includes dark mode styles by default. If you see issues:

1. Ensure Tailwind dark mode is configured:
   ```javascript
   // tailwind.config.js
   module.exports = {
     darkMode: 'class',
     // ...
   }
   ```

2. Check that `dark` class is applied to `<html>` or `<body>`

---

## API Reference

### Media Field Methods

#### `editable(array $options = []): static`

Enable image editing before upload.

**Parameters:**
- `$options` (array): Editor configuration
  - `aspectRatio` (float|null): Fixed aspect ratio (e.g., 1, 16/9, 4/3)
  - `minWidth` (int|null): Minimum crop width in pixels
  - `minHeight` (int|null): Minimum crop height in pixels

**Returns:** `static` (for method chaining)

**Example:**
```php
Media::make('Photo')
    ->editable([
        'aspectRatio' => 16 / 9,
        'minWidth' => 1280,
        'minHeight' => 720
    ])
```

---

### ImageEditor Component API

#### Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `show` | Boolean | Yes | - | Show/hide modal |
| `imageSrc` | String | Yes | - | Image URL or data URL |
| `options` | Object | No | `{}` | Editor configuration |
| `options.aspectRatio` | Number | No | `null` | Fixed aspect ratio |
| `options.minWidth` | Number | No | `null` | Min crop width |
| `options.minHeight` | Number | No | `null` | Min crop height |

#### Events

**@close**
- **Payload**: None
- **Description**: Emitted when user cancels editing

**@save**
- **Payload**:
  ```javascript
  {
    blob: Blob,           // Edited image as Blob
    canvas: HTMLCanvasElement,  // Canvas element
    options: {
      rotation: Number,   // Total rotation applied
      flip: {
        horizontal: Boolean,
        vertical: Boolean
      },
      aspectRatio: Number|null
    }
  }
  ```
- **Description**: Emitted when user saves edited image

---

## Best Practices

### 1. Choose Appropriate Aspect Ratios

- **Avatars**: 1:1 (square)
- **Banners**: 16:9 or 21:9 (widescreen)
- **Thumbnails**: 4:3 or 3:2 (standard photo)
- **Stories**: 9:16 (vertical)
- **Product photos**: Free or 1:1

### 2. Set Minimum Dimensions

Always set minimum dimensions to ensure quality:

```php
->editable([
    'minWidth' => 400,
    'minHeight' => 400
])
```

### 3. Combine with File Size Limits

```php
Media::make('Banner')
    ->maxFileSize(3)  // 3MB max
    ->editable([
        'aspectRatio' => 16 / 9,
        'minWidth' => 1280
    ])
```

### 4. Use Descriptive Labels

```php
Media::make('Profile Picture')
    ->helpText('Square image, minimum 200x200px')
    ->editable(['aspectRatio' => 1, 'minWidth' => 200])
```

### 5. Don't Overuse Editing

Only enable editing when necessary:
- ✅ Avatars (need square)
- ✅ Banners (need specific ratio)
- ✅ Headers (need consistent size)
- ❌ Documents (no editing needed)
- ❌ General files (not images)

---

## Summary

The Image Editor provides a professional, user-friendly solution for pre-upload image editing in your Resource CRUD system. With just one method call (`->editable()`), you can enforce aspect ratios, minimum dimensions, and provide powerful editing tools to your users.

**Key Benefits:**
- ✅ **Zero backend changes** - Works with existing Media field
- ✅ **Touch-friendly** - Perfect for mobile/tablet users
- ✅ **Dark mode support** - Matches your theme
- ✅ **Flexible** - Freeform or fixed ratio cropping
- ✅ **Quality control** - Enforce minimum dimensions
- ✅ **Modern UI** - Beautiful modal interface
- ✅ **Production-ready** - Battle-tested library

**Perfect for:**
- User avatars
- Banner images
- Product photos
- Gallery images
- Any image upload that requires consistency

---

## Related Documentation

- [Media Field Documentation](./media-field.md) - Complete Media field guide
- [Resource CRUD System](../docs/resource-crud-system.md) - Resource system overview
- [Vue Advanced Cropper Docs](https://advanced-cropper.github.io/vue-advanced-cropper/) - Library documentation
