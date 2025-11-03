# Media Field Documentation

## Overview

The Media field integrates **Spatie Media Library** into the Resource CRUD system, enabling file uploads (images, documents, etc.) for any model. It supports both single and multiple file uploads with automatic image conversions, validation, and a beautiful drag-and-drop interface.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Backend Setup](#backend-setup)
3. [Field Configuration](#field-configuration)
4. [Available Methods](#available-methods)
5. [Frontend Component](#frontend-component)
6. [Complete Examples](#complete-examples)
7. [Advanced Usage](#advanced-usage)
8. [API Endpoints](#api-endpoints)

---

## Quick Start

### 1. Make Model Support Media

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(48)
                    ->height(48);
            });
    }
}
```

### 2. Add Media Field to Resource

```php
use App\Resources\Fields\Media;

Media::make('Avatar')
    ->single()
    ->collection('avatars')
    ->images()
    ->maxFileSize(2)
    ->previewSize(48, 48)
    ->rounded()
    ->rules('nullable')
```

That's it! Users can now upload avatars with drag-and-drop.

---

## Backend Setup

### Step 1: Install Spatie Media Library

```bash
composer require "spatie/laravel-medialibrary:^11.0"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
php artisan migrate
```

### Step 2: Implement HasMedia on Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        // Single file collection (e.g., featured image)
        $this->addMediaCollection('featured')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(150)
                    ->height(150);

                $this->addMediaConversion('large')
                    ->width(800)
                    ->height(600);
            });

        // Multiple files collection (e.g., gallery)
        $this->addMediaCollection('gallery')
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(200)
                    ->height(200);
            });
    }
}
```

---

## Field Configuration

### Basic Structure

```php
Media::make('Label', 'attribute_name')
    ->single()  // or ->multiple()
    ->collection('collection_name')
    ->rules('nullable')
```

---

## Available Methods

### Mode Selection

#### `single()`
Enable single file mode (default). Only one file can be uploaded.

```php
Media::make('Avatar')
    ->single()
```

#### `multiple(?int $maxFiles = null)`
Enable multiple files mode with optional max file limit.

```php
Media::make('Gallery')
    ->multiple(10)  // Max 10 files
```

### Collection Management

#### `collection(string $collection)`
Set the media collection name (must match model's `registerMediaCollections`).

```php
Media::make('Avatar')
    ->collection('avatars')
```

### File Type Restrictions

#### `acceptedTypes(array $types)`
Set accepted MIME types.

```php
Media::make('Document')
    ->acceptedTypes(['application/pdf', 'application/msword'])
```

#### `images()`
Shorthand for common image types.

```php
Media::make('Photo')
    ->images()  // Accepts: jpeg, png, gif, webp, svg+xml
```

#### `documents()`
Shorthand for common document types.

```php
Media::make('Resume')
    ->documents()  // Accepts: pdf, doc, docx
```

### Size & Validation

#### `maxFileSize(int $mb)`
Set maximum file size in megabytes.

```php
Media::make('Avatar')
    ->maxFileSize(2)  // 2MB max
```

#### `rules(string|array $rules)`
Standard Laravel validation rules.

```php
Media::make('Avatar')
    ->rules('required|image')
```

### Preview Configuration

#### `previewSize(int $width, int $height)`
Set preview dimensions in pixels.

```php
Media::make('Avatar')
    ->previewSize(128, 128)
```

#### `rounded(bool $rounded = true)`
Make preview images circular (great for avatars).

```php
Media::make('Avatar')
    ->rounded()
```

### Storage Configuration

#### `disk(string $disk)`
Set storage disk.

```php
Media::make('Document')
    ->disk('s3')
```

---

## Frontend Component

The `MediaUpload.vue` component provides:

- **Drag & Drop**: Drop files onto the upload area
- **Click to Browse**: Click to open file picker
- **Preview**: Shows uploaded image with delete button
- **Progress**: Animated upload spinner
- **Validation**: Client-side file type and size validation
- **Error Handling**: Clear error messages

### Props

```vue
<MediaUpload
  v-model="formData.avatar"
  label="Avatar"
  help-text="Upload your profile picture"
  :required="false"
  :disabled="false"
  :multiple="false"
  collection="avatars"
  :accepted-types="['image/*']"
  :max-file-size="2"
  :rounded="true"
  :preview-width="128"
  :preview-height="128"
  model-type="App\Models\User"
  :model-id="userId"
/>
```

### Events

- `@update:modelValue` - Emitted when media is uploaded/removed
- `@uploaded` - Emitted when upload succeeds
- `@removed` - Emitted when media is deleted
- `@error` - Emitted on validation or upload errors

---

## Complete Examples

### Example 1: User Avatar (Single Image)

**Model:**
```php
class User extends Authenticatable implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(48)
                    ->height(48)
                    ->sharpen(10);

                $this->addMediaConversion('medium')
                    ->width(200)
                    ->height(200)
                    ->sharpen(10);
            });
    }
}
```

**Resource:**
```php
class UserResource extends Resource
{
    public function fields(): array
    {
        return [
            ID::make()->sortable(),

            Media::make('Avatar')
                ->single()
                ->collection('avatars')
                ->images()
                ->maxFileSize(2)
                ->previewSize(48, 48)
                ->rounded()
                ->rules('nullable'),

            Text::make('Name')
                ->rules('required|string|max:255'),

            Email::make('Email')
                ->rules('required|email|unique:users,email'),
        ];
    }
}
```

**Result:**
- Circular avatar thumbnail in table
- Drag & drop avatar upload in form
- Automatic image optimization
- Max 2MB file size
- One avatar per user

---

### Example 2: Product Gallery (Multiple Images)

**Model:**
```php
class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(200)
                    ->height(200)
                    ->sharpen(10);

                $this->addMediaConversion('large')
                    ->width(1024)
                    ->height(768)
                    ->sharpen(10);
            });
    }
}
```

**Resource:**
```php
class ProductResource extends Resource
{
    public function fields(): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->rules('required|string|max:255'),

            Media::make('Product Images', 'images')
                ->multiple(10)
                ->collection('images')
                ->images()
                ->maxFileSize(5)
                ->previewSize(200, 200)
                ->hideFromIndex()
                ->rules('nullable|array'),

            Number::make('Price')
                ->rules('required|numeric|min:0'),
        ];
    }
}
```

**Result:**
- Upload up to 10 images
- Hidden from table view
- Shown in create/edit forms
- 5MB max per file

---

### Example 3: Document Upload

**Model:**
```php
class Document extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files')
            ->registerMediaConversions(function () {
                // No conversions for documents
            });
    }
}
```

**Resource:**
```php
class DocumentResource extends Resource
{
    public function fields(): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')
                ->rules('required|string|max:255'),

            Media::make('File')
                ->single()
                ->collection('files')
                ->documents()
                ->maxFileSize(10)
                ->hideFromIndex()
                ->rules('required'),

            Date::make('Uploaded At')
                ->sortable()
                ->exceptOnForm(),
        ];
    }
}
```

**Result:**
- PDF/DOC/DOCX uploads only
- 10MB max size
- Required field
- Hidden from table

---

## Advanced Usage

### Custom File Types

```php
Media::make('Video')
    ->single()
    ->collection('videos')
    ->acceptedTypes(['video/mp4', 'video/quicktime'])
    ->maxFileSize(50)
```

### Multiple Collections on Same Model

```php
class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        // Featured image
        $this->addMediaCollection('featured')->singleFile();

        // Gallery images
        $this->addMediaCollection('gallery');

        // Product documents
        $this->addMediaCollection('documents');
    }
}

// In Resource:
Media::make('Featured Image')->collection('featured')->single(),
Media::make('Gallery')->collection('gallery')->multiple(20),
Media::make('Documents')->collection('documents')->multiple(5)->documents(),
```

### Custom Storage Disk

```php
Media::make('Secure Document')
    ->collection('private')
    ->disk('s3-private')
    ->documents()
```

### Responsive Images

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('photos')
        ->registerMediaConversions(function () {
            $this->addMediaConversion('thumb')
                ->width(150)
                ->height(150);

            $this->addMediaConversion('medium')
                ->width(500)
                ->height(500);

            $this->addMediaConversion('large')
                ->width(1200)
                ->height(1200);

            $this->addMediaConversion('webp-thumb')
                ->width(150)
                ->height(150)
                ->format('webp');
        });
}
```

---

## API Endpoints

The Media field uses these API endpoints:

### Upload Single File

```http
POST /api/media/upload
Content-Type: multipart/form-data

file: (binary)
model_type: App\Models\User
model_id: 1
collection: avatars
```

**Response:**
```json
{
  "message": "File uploaded successfully",
  "data": {
    "id": 123,
    "name": "avatar.jpg",
    "url": "http://example.com/media/123/avatar.jpg",
    "thumbnail": "http://example.com/media/123/conversions/avatar-thumb.jpg",
    "size": 204800,
    "mime_type": "image/jpeg"
  }
}
```

### Upload Multiple Files

```http
POST /api/media/upload-multiple
Content-Type: multipart/form-data

files[]: (binary)
files[]: (binary)
model_type: App\Models\Product
model_id: 5
collection: gallery
```

### Delete Media

```http
DELETE /api/media/{mediaId}
```

---

## Best Practices

### Performance

1. **Use Image Conversions**: Generate thumbnails to reduce load times
2. **Set File Size Limits**: Prevent large uploads that slow down the server
3. **Use CDN**: Configure Spatie Media Library to use a CDN for media delivery

### Security

1. **Validate File Types**: Always restrict accepted file types
2. **Scan for Malware**: Consider integrating virus scanning for user uploads
3. **Use Private Disks**: For sensitive documents, use private S3 buckets

### User Experience

1. **Show Progress**: The component shows upload progress automatically
2. **Provide Feedback**: Error messages are shown for validation failures
3. **Use Appropriate Limits**: Don't make file size limits too restrictive

### Database

1. **Eager Load Media**: Media relationships are automatically loaded
2. **Clean Up**: Spatie Media Library handles deletion automatically
3. **Conversions**: Generate conversions asynchronously for better performance

---

## Troubleshooting

### Upload Fails with 413 Error

Increase PHP/Nginx upload limits:

```php
// php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Images Not Generating Conversions

Install image processing libraries:

```bash
sudo apt-get install jpegoptim optipng pngquant gifsicle webp
```

### Storage Permissions

Ensure storage directory is writable:

```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

---

## Notes

- Media uploads require the model to exist (you can't upload to an unsaved model)
- Files are stored in `storage/app/public/media` by default
- Run `php artisan storage:link` to make media publicly accessible
- The Media field automatically handles cleanup when models are deleted
- Image conversions are generated synchronously by default (can be queued)

---

## Summary

The Media field provides a production-ready file upload system that works seamlessly with your Resource CRUD system. It handles everything from drag-and-drop uploads to automatic image optimization, with zero configuration for basic use cases and extensive customization options for advanced scenarios.

**Key Features:**
- ✅ Single and multiple file uploads
- ✅ Drag & drop interface
- ✅ Automatic image conversions
- ✅ File type and size validation
- ✅ Circular avatar support
- ✅ Works with any model
- ✅ S3/CDN compatible
- ✅ Mobile-friendly
- ✅ Dark mode support
