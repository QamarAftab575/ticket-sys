# API Documentation Setup Guide

## Quick Setup (5 Minutes)

Follow these steps to get your API documentation up and running.

### Step 1: Install Scramble Package

```bash
composer require dedoc/scramble
```

### Step 2: Install JWT Library (if not already installed)

```bash
composer require firebase/php-jwt
```

### Step 3: Generate JWT Secret

```bash
php artisan tinker
```

In tinker console:
```php
echo \App\Services\SsoJwtService::generateSecret();
exit
```

Copy the output.

### Step 4: Update Environment File

Add to `.env`:
```env
JWT_SECRET=paste-your-generated-secret-here
```

### Step 5: Clear Caches

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Step 6: Access Documentation

Open your browser and navigate to:
```
http://localhost:8000/api/docs
```

You should see a beautiful Scalar UI documentation interface!

## What You Get

### ✅ Automatic API Documentation
- All 75+ endpoints automatically documented
- Request/response schemas
- Validation rules
- Authentication requirements
- Error responses

### ✅ Interactive Testing
- Try endpoints directly from docs
- Pre-filled examples
- Save authentication tokens
- Real-time responses

### ✅ Code Generation
- Curl commands
- JavaScript/TypeScript
- Python
- PHP
- And more...

### ✅ Export Options
- OpenAPI JSON spec
- Import to Postman
- Import to Insomnia
- Generate client SDKs

## Configuration

The file `config/scramble.php` has been created with optimal settings:

```php
return [
    'path' => 'api/docs',           // Documentation URL path
    'ui' => 'scalar',                // Beautiful Scalar UI
    'routes' => [
        'prefix' => 'api/v1',        // Document v1 endpoints
        'middleware' => ['api'],
    ],
    'info' => [
        'title' => 'Asana-like Project Management API',
        'version' => '1.0.0',
    ],
    'security' => [
        'BearerAuth' => [
            'type' => 'http',
            'scheme' => 'bearer',
        ],
    ],
];
```

## Testing the Documentation

### Test 1: View All Endpoints

1. Open `http://localhost:8000/api/docs`
2. You should see all endpoint groups:
   - Authentication (6 endpoints)
   - SSO (4 endpoints)
   - Users (4 endpoints)
   - Workspaces (11 endpoints)
   - Projects (18 endpoints)
   - Tasks (21 endpoints)
   - Custom Fields (6 endpoints)
   - Comments (5 endpoints)
   - Attachments (5 endpoints)

### Test 2: Try an Endpoint

1. Navigate to **Authentication → Login**
2. Click "Try it"
3. Enter test credentials:
   ```json
   {
     "email": "test@example.com",
     "password": "password"
   }
   ```
4. Click "Send"
5. Copy the token from response

### Test 3: Authenticated Request

1. Click the "Auth" button in top right
2. Select "BearerAuth"
3. Paste your token
4. Navigate to **Users → Get Current User**
5. Click "Try it" → "Send"
6. Should return your user profile

### Test 4: Export OpenAPI Spec

1. Click "Download OpenAPI Spec" button
2. Save as `openapi.json`
3. Open in Postman/Insomnia

## Customization

### Change Documentation URL

In `config/scramble.php`:
```php
'path' => 'docs', // Now accessible at /docs instead of /api/docs
```

### Add Custom Servers

In `config/scramble.php`:
```php
'servers' => [
    [
        'url' => 'http://localhost:8000/api/v1',
        'description' => 'Local Development',
    ],
    [
        'url' => 'https://staging-api.yourapp.com/v1',
        'description' => 'Staging',
    ],
    [
        'url' => 'https://api.yourapp.com/v1',
        'description' => 'Production',
    ],
],
```

### Customize API Info

In `config/scramble.php`:
```php
'info' => [
    'title' => 'Your API Name',
    'description' => 'Your API description',
    'version' => '1.0.0',
    'contact' => [
        'name' => 'API Support',
        'email' => 'api@yourcompany.com',
        'url' => 'https://support.yourcompany.com',
    ],
    'license' => [
        'name' => 'MIT',
        'url' => 'https://opensource.org/licenses/MIT',
    ],
],
```

## Production Deployment

### Step 1: Environment Variables

Ensure these are set in production `.env`:
```env
APP_URL=https://yourapp.com
JWT_SECRET=production-secret-here
```

### Step 2: Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
```

### Step 3: Restrict Access (Optional)

To restrict documentation access in production, update `config/scramble.php`:

```php
'middleware' => ['api', 'auth.api-token'], // Require authentication
```

Or create a custom middleware:

```php
// app/Http/Middleware/RestrictApiDocs.php
namespace App\Http\Middleware;

class RestrictApiDocs
{
    public function handle($request, Closure $next)
    {
        // Only allow specific IPs or authenticated admins
        if (!in_array($request->ip(), config('app.allowed_doc_ips', []))) {
            abort(403, 'Access denied');
        }
        
        return $next($request);
    }
}
```

Then register and use it:
```php
// config/scramble.php
'middleware' => ['api', RestrictApiDocs::class],
```

### Step 4: CDN Configuration (Optional)

If using CDN, configure CORS:

```php
// config/cors.php
'paths' => ['api/*', 'api/docs/*'],
```

## Integration with CI/CD

### Generate OpenAPI Spec in Pipeline

```yaml
# .github/workflows/api-docs.yml
name: Generate API Documentation

on:
  push:
    branches: [main]

jobs:
  generate-docs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
        
      - name: Generate OpenAPI spec
        run: php artisan scramble:export > openapi.json
        
      - name: Upload spec
        uses: actions/upload-artifact@v2
        with:
          name: openapi-spec
          path: openapi.json
```

### Validate OpenAPI Spec

```bash
# Install validator
npm install -g @apidevtools/swagger-cli

# Validate spec
swagger-cli validate openapi.json
```

## Monitoring and Analytics

### Track API Usage

Add middleware to track endpoint usage:

```php
// app/Http/Middleware/TrackApiUsage.php
namespace App\Http\Middleware;

class TrackApiUsage
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Log API usage
        \Log::channel('api')->info('API Request', [
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'status' => $response->status(),
            'duration' => microtime(true) - LARAVEL_START,
        ]);
        
        return $response;
    }
}
```

### Monitor Documentation Access

```php
// Track who accesses documentation
if ($request->is('api/docs*')) {
    \Log::info('API Docs Accessed', [
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);
}
```

## Troubleshooting

### Issue: Documentation Not Loading

**Symptoms**: Blank page at `/api/docs`

**Solutions**:
1. Check if Scramble is installed: `composer show dedoc/scramble`
2. Clear all caches: `php artisan optimize:clear`
3. Check logs: `storage/logs/laravel.log`
4. Verify route exists: `php artisan route:list | grep docs`

### Issue: Endpoints Not Showing

**Symptoms**: Some endpoints missing from documentation

**Solutions**:
1. Verify route prefix in `config/scramble.php` matches your routes
2. Check controller namespace
3. Ensure routes are registered: `php artisan route:list`
4. Clear route cache: `php artisan route:clear`

### Issue: Authentication Not Working in UI

**Symptoms**: Get 401 when testing endpoints

**Solutions**:
1. Ensure you've added token in "Auth" section
2. Check token format (should not include "Bearer" prefix when using BearerAuth field)
3. Verify token is not expired
4. Test token with curl first:
   ```bash
   curl -H "Authorization: Bearer YOUR_TOKEN" \
        http://localhost:8000/api/v1/users/me
   ```

### Issue: Wrong Request/Response Schemas

**Symptoms**: Documentation shows incorrect data types

**Solutions**:
1. Add type hints to controller methods
2. Use Form Request classes for validation
3. Add PHPDoc comments with `@param` and `@return` tags
4. Clear cache: `php artisan config:clear`

## Advanced Features

### Custom Response Examples

Add to controller methods:

```php
/**
 * Get workspace details
 * 
 * @response 200 {
 *   "data": {
 *     "id": "uuid",
 *     "name": "Engineering Team",
 *     "members_count": 15
 *   }
 * }
 */
public function show(Workspace $workspace): JsonResponse
{
    // ...
}
```

### Hide Specific Endpoints

In `config/scramble.php`:

```php
'ignore' => [
    '/api/v1/internal/*',
    '/api/v1/admin/*',
],
```

### Add Custom Tags

In controller:

```php
/**
 * @group Workspace Management
 */
class WorkspaceApiController extends Controller
{
    // ...
}
```

## Next Steps

After setup:

1. ✅ **Review all endpoints** - Ensure documentation is accurate
2. ✅ **Test authentication** - Verify token auth works
3. ✅ **Export OpenAPI spec** - Save for Postman/SDK generation
4. ✅ **Share with team** - Send documentation URL to developers
5. ✅ **Create examples** - Add real-world usage examples
6. ✅ **Setup monitoring** - Track API usage patterns
7. ✅ **Plan versioning** - Prepare for v2 when needed

## Resources

### Documentation
- **Scramble Docs**: https://scramble.dedoc.co/
- **Scalar UI**: https://github.com/scalar/scalar
- **OpenAPI 3.1**: https://spec.openapis.org/oas/v3.1.0

### Tools
- **Postman**: https://www.postman.com/
- **Insomnia**: https://insomnia.rest/
- **OpenAPI Generator**: https://openapi-generator.tech/

### Support
- **Laravel Docs**: https://laravel.com/docs
- **Internal Guides**: See `API_*.md` files in project root

---

**Setup Complete! 🎉**

Your API is now fully documented and ready for third-party integration.
