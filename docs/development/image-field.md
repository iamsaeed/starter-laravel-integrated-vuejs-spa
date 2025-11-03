# Image Field Documentation

## Overview

The `Image` field type allows you to display images, SVG content, and base64-encoded images in your Resource tables.

## Usage

### Basic Usage

```php
use App\Resources\Fields\Image;

Image::make('Avatar', 'avatar_url')
    ->asUrl()
    ->width(48)
    ->height(48)
    ->rounded()
```

## Display Types

### 1. URL/Path (asUrl)

Display an image from a URL or file path:

```php
Image::make('Profile Picture', 'photo_url')
    ->asUrl()
    ->width(64)
    ->height(64)
    ->rounded()
    ->fallback('/images/default-avatar.png')
    ->alt('User profile picture')
```

### 2. Inline SVG (asSvg)

Display SVG content stored in the database:

```php
Image::make('Flag SVG', 'flag_svg')
    ->asSvg()
    ->width(32)
    ->height(24)
    ->alt('Country flag')
```

**Example in CountryResource:**
```php
Image::make('Flag SVG', 'flag_svg')
    ->asSvg()
    ->width(32)
    ->height(24)
    ->rules('nullable')
    ->exceptOnForm()
    ->alt('Country flag')
```

### 3. Base64 (asBase64)

Display base64-encoded image data:

```php
Image::make('Thumbnail', 'thumbnail_base64')
    ->asBase64()
    ->width(100)
    ->height(100)
```

## Available Methods

### Display Type

- `displayType(string $type)` - Set display type: 'url', 'svg', or 'base64'
- `asUrl()` - Display as URL/path (shorthand)
- `asSvg()` - Display as inline SVG (shorthand)
- `asBase64()` - Display as base64 image (shorthand)

### Sizing

- `width(int $width)` - Set image width in pixels
- `height(int $height)` - Set image height in pixels
- `size(int $width, int $height)` - Set both width and height

### Styling

- `rounded(bool $rounded = true)` - Make image circular/rounded

### Fallback & Accessibility

- `fallback(string $url)` - Fallback image if primary fails to load
- `alt(string $alt)` - Alt text for accessibility

### Inherited from Field

All standard field methods are available:

- `rules(string|array $rules)` - Validation rules
- `sortable(bool $sortable = true)` - Enable sorting
- `hideFromIndex()` - Hide from table view
- `hideFromDetail()` - Hide from detail view
- `hideFromForm()` - Hide from create/edit forms
- `onlyOnIndex()` - Show only in table
- `exceptOnForm()` - Show everywhere except forms

## Examples

### User Avatar

```php
Image::make('Avatar', 'avatar_url')
    ->asUrl()
    ->width(48)
    ->height(48)
    ->rounded()
    ->fallback('/images/default-avatar.png')
    ->alt('User avatar')
    ->exceptOnForm()
```

### Product Image

```php
Image::make('Product Image', 'image_url')
    ->asUrl()
    ->width(80)
    ->height(80)
    ->fallback('/images/no-product.png')
    ->alt('Product image')
    ->rules('nullable|url')
```

### Company Logo (SVG)

```php
Image::make('Logo', 'logo_svg')
    ->asSvg()
    ->width(120)
    ->height(40)
    ->alt('Company logo')
    ->exceptOnForm()
```

### QR Code (Base64)

```php
Image::make('QR Code', 'qr_code_base64')
    ->asBase64()
    ->width(128)
    ->height(128)
    ->alt('QR Code')
    ->hideFromIndex()
```

### Icon with Fallback

```php
Image::make('Icon', 'icon_url')
    ->asUrl()
    ->width(24)
    ->height(24)
    ->fallback('/icons/default.svg')
    ->alt('Category icon')
```

## Frontend Behavior

### SVG Display
- Renders inline SVG content using `v-html`
- Respects width/height settings
- No external requests needed

### URL Display
- Uses `<img>` tag with `src` attribute
- Falls back to fallback URL on error
- Lazy loads automatically

### Base64 Display
- Converts to data URI: `data:image/png;base64,{content}`
- No external requests needed
- Embedded in HTML

### Error Handling
- If image fails to load and fallback is provided, shows fallback
- If no fallback, hides broken image icon
- Empty values show "-" placeholder

### Styling
- `rounded` class applies rounded corners or circular shape
- Width/height enforced via inline styles or attributes
- Dark mode compatible

## Best Practices

### Performance
- Use appropriate image sizes (don't load huge images in table)
- Consider using thumbnails for table view
- Use SVG for icons and logos when possible

### Accessibility
- Always provide meaningful alt text
- Use descriptive labels
- Ensure sufficient color contrast

### Database Storage
- **URLs**: Store relative or absolute paths
- **SVG**: Store complete SVG markup (including `<svg>` tags)
- **Base64**: Store encoded string without data URI prefix

### Sizing
- Keep table images small (24-80px) for performance
- Use consistent dimensions within the same column
- Consider responsive sizing for different screen sizes

## Notes

- Image fields are automatically hidden from forms by default (use `showOnForm()` to enable)
- SVG content is sanitized by DOMPurify on the frontend for security
- Base64 images should be used sparingly due to payload size
- Fallback images should be lightweight and always available
- Use `rounded()` for avatars and profile pictures
- Width and height are optional but recommended for layout stability
