# Production Deployment Guide

## Filesystem Configuration

The application now uses `FILESYSTEM_DISK` environment variable to determine storage backend instead of hardcoding based on `APP_ENV`.

### Local/Server Storage (Default)

For local or server-based file storage:

```env
FILESYSTEM_DISK=local
```

This will use:
- `tenant` disk for tenant files (stored in `storage/app/tenants/{workspace_id}`)
- Media files stored locally

### AWS S3 Storage

For cloud-based S3 storage in production:

1. **Install AWS S3 Package:**
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

2. **Configure Environment:**
```env
FILESYSTEM_DISK=s3

# AWS Credentials
AWS_ACCESS_KEY_ID=your-access-key-id
AWS_SECRET_ACCESS_KEY=your-secret-access-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

When `FILESYSTEM_DISK=s3`, the application will automatically use:
- `tenant-s3` disk for tenant files (stored with prefix `tenants/{workspace_id}`)
- S3 for all media library uploads

## Error Resolution

### "Class PortableVisibilityConverter not found"

**Cause:** Application is configured to use S3 (`FILESYSTEM_DISK=s3` or production defaults) but AWS package is not installed.

**Solution 1 - Use Local Storage (Recommended if not using S3):**
```env
FILESYSTEM_DISK=local
```

**Solution 2 - Install AWS Package (If using S3):**
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### "There is no active transaction" (During Workspace Deletion)

**Cause:** This error occurred in versions before the fix where `forceDeleteWorkspace()` wrapped DDL operations (like `DROP DATABASE`) inside a transaction. MySQL DDL statements cause an implicit commit, which breaks Laravel's transaction management.

**Solution:** This has been fixed in the codebase. The workspace deletion now follows this pattern:
1. Delete workspace record in transaction
2. Drop database **outside** transaction (DDL causes auto-commit)
3. Clean up files **outside** transaction (no transaction needed)

If you're still seeing this error, ensure you have the latest version of `WorkspaceService.php` (after 2025-01-13).

## How It Works

The system determines which disk to use based on `FILESYSTEM_DISK`:

```php
// In TenantMediaBootstrap and MediaCleanupService
$defaultDisk = config('filesystems.default'); // Gets FILESYSTEM_DISK
$tenantDisk = $defaultDisk === 's3' ? 'tenant-s3' : 'tenant';
```

- If `FILESYSTEM_DISK=s3` → uses `tenant-s3` disk (requires AWS package)
- If `FILESYSTEM_DISK=local` or anything else → uses `tenant` disk (local storage)

## Deployment Checklist

### For Local/Server Deployments

1. ✅ Set `FILESYSTEM_DISK=local` in `.env`
2. ✅ Ensure storage directories are writable: `storage/app/tenants/`
3. ✅ Run `php artisan storage:link` to create symbolic links
4. ✅ Set proper permissions: `chmod -R 775 storage bootstrap/cache`

### For S3-Based Deployments

1. ✅ Install AWS package: `composer require league/flysystem-aws-s3-v3`
2. ✅ Set `FILESYSTEM_DISK=s3` in `.env`
3. ✅ Configure all AWS credentials in `.env`
4. ✅ Create S3 bucket with proper CORS and permissions
5. ✅ Test media uploads work correctly

## Multi-Tenancy Notes

- Each workspace gets isolated storage: `tenants/{workspace_id}/`
- Media files are automatically scoped to tenant context
- Workspace deletion cleans up all associated files
- Local: Files stored in `storage/app/tenants/{workspace_id}/`
- S3: Files stored with prefix `tenants/{workspace_id}/`

## Testing

Test file uploads and workspace operations:

```bash
# Local storage
FILESYSTEM_DISK=local php artisan test --filter=MediaTest

# S3 storage (requires package and credentials)
FILESYSTEM_DISK=s3 php artisan test --filter=MediaTest
```
