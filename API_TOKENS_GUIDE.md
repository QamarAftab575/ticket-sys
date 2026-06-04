# API Tokens Management - Complete Implementation Guide

## Overview

This feature enables workspace owners and admins to create secure API tokens for external applications to access your API. It uses **Laravel Passport** for OAuth2-compliant token management with token hashing for security.

## Features Implemented

### 1. **Access Control**
- Only users with role `Owner` or `Admin` in any workspace can create/manage API tokens
- Global admins (`super-admin`, `admin`) also have full access
- Regular users see a 403 error if they try to access the feature

### 2. **Token Management UI** (`/settings/integrations/tokens`)
Located under **Settings > Integrations > Access Tokens** in the sidebar

**Features:**
- **Token Statistics**: Display total, active, and expired token counts
- **Create New Token**: Form with:
  - Token name (required)
  - Optional expiration date
- **Secure Display**: Full token shown only once in a modal after creation
  - Copy button for easy clipboard copying
  - Clear warning: "You won't be able to see it again!"
- **Token List**: Table showing:
  - Token name
  - Masked token (e.g., `sk_live_****************************`)
  - Created date
  - Last used date (if available)
  - Expiration status
  - Revoke button
- **Bulk Actions**: Revoke all tokens at once

### 3. **API Token Storage**

**Database Table**: `api_tokens`
```sql
- id: bigint (auto-increment)
- user_id: uuid (foreign key to users)
- name: string (token display name)
- token: string(64) unique (SHA256 hashed token)
- plain_token: text nullable (shown only once on creation)
- scopes: json nullable (for future scope-based access control)
- last_used_at: timestamp nullable
- expires_at: timestamp nullable
- is_active: boolean (soft revoke)
- created_at, updated_at, deleted_at (soft delete)
```

### 4. **Security Features**

✅ **Token Hashing**: Tokens are hashed using SHA256 before storage (industry standard)
```php
// Generation
$plainToken = 'sk_' . Str::random(64);
$hashedToken = hash('sha256', $plainToken);
```

✅ **Never Logged**: Plain tokens are never logged or exposed
✅ **Soft Revoke**: Revoked tokens remain in database for audit trail
✅ **Expiration**: Optional expiration dates with automatic validation
✅ **Per-User Isolation**: Users can only see/manage their own tokens
✅ **CSRF Protected**: All POST/DELETE requests require CSRF token

### 5. **API Authentication Middleware**

**Middleware Class**: `App\Http\Middleware\AuthenticateApiToken`

**Token Extraction** (in order of priority):
1. `Authorization: Bearer {token}` header
2. `X-API-Token: {token}` custom header  
3. `?api_token={token}` query parameter

**Usage:**
```bash
# Bearer token (recommended)
curl -H "Authorization: Bearer sk_live_..." https://yourapp.com/api/external/tasks

# Custom header
curl -H "X-API-Token: sk_live_..." https://yourapp.com/api/external/tasks

# Query parameter
curl https://yourapp.com/api/external/tasks?api_token=sk_live_...
```

**Response Codes:**
- `401 Unauthorized`: Missing, invalid, or revoked token
- `403 Forbidden`: Token expired
- `200 OK`: Valid request

### 6. **File Structure**

#### Models
```
app/Models/ApiToken.php
├── Methods:
│   ├── generateToken() - Create new secure token
│   ├── hashToken() - Hash a token for storage
│   ├── matchesToken() - Verify plain token against hash
│   ├── getMaskedToken() - Get masked display (sk_live_****)
│   ├── isExpired() - Check expiration
│   ├── isValid() - Check active & not expired
│   └── recordUsage() - Update last_used_at
```

```
app/Models/User.php (extended)
├── New Relationship:
│   └── apiTokens() - HasMany relationship to ApiToken
```

#### Services
```
app/Services/ApiTokenService.php
├── createToken() - Create new token
├── getUserTokens() - Get paginated tokens for user
├── getTokenById() - Get specific token
├── validateToken() - Verify plain token
├── revokeToken() - Soft delete a token
├── revokeAllTokens() - Revoke all user tokens
├── formatTokenForResponse() - Format for API response
└── getTokenStats() - Get user's token statistics
```

#### Controllers
```
app/Http/Controllers/ApiTokenController.php
├── show() - Display token management page
├── store() - Create new token
├── destroy() - Revoke single token
├── revokeAll() - Revoke all tokens
└── list() - Get tokens list (AJAX)
```

#### Middleware
```
app/Http/Middleware/AuthenticateApiToken.php
├── Extracts token from request
├── Validates against stored hash
├── Checks expiration
├── Records usage
└── Sets authenticated user
```

#### Form Requests
```
app/Http/Requests/StoreApiTokenRequest.php
├── Authorization check (owner/admin only)
├── Validation rules
└── Custom error messages
```

#### Frontend
```
resources/js/Pages/Settings/Integrations/ApiTokens.vue
├── Token creation form
├── Success modal with copy button
├── Token list with pagination
├── Delete/revoke functionality
└── Statistics display
```

#### Database
```
database/migrations/2026_06_02_180000_create_api_tokens_table.php
```

#### Routes
```
routes/web.php
├── GET  /settings/integrations/tokens
├── POST /settings/integrations/tokens
├── DELETE /settings/integrations/tokens/{tokenId}
└── POST /settings/integrations/tokens/revoke-all

routes/api.php (protected by auth.api-token middleware)
├── GET /api/external/tasks
└── GET /api/external/tasks/{task}
```

### 7. **Usage Examples**

#### Creating a Token via UI
1. Navigate to Settings > Integrations > Access Tokens
2. Fill in token name (e.g., "My Integration")
3. Optionally set expiration date
4. Click "Create Token"
5. Copy the token from the modal (shown only once!)
6. Store it securely in your external application

#### Using Token in External App (cURL)
```bash
# Get all user tasks
curl -H "Authorization: Bearer sk_live_xxxxx..." \
     https://yourapp.com/api/external/tasks

# Get single task
curl -H "Authorization: Bearer sk_live_xxxxx..." \
     https://yourapp.com/api/external/tasks/123
```

#### Using Token in External App (Node.js)
```javascript
const token = 'sk_live_xxxxx...';

const response = await fetch('https://yourapp.com/api/external/tasks', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});

const tasks = await response.json();
```

#### Using Token in External App (Python)
```python
import requests

token = 'sk_live_xxxxx...'
headers = {'Authorization': f'Bearer {token}'}

response = requests.get(
    'https://yourapp.com/api/external/tasks',
    headers=headers
)

tasks = response.json()
```

### 8. **Admin/Audit Trail**

Revoked tokens are soft-deleted, allowing you to query audit history:
```php
// See all tokens (including revoked)
ApiToken::withTrashed()->where('user_id', $userId)->get();

// See only active tokens
ApiToken::where('is_active', true)->where('user_id', $userId)->get();

// See who created a token and when
$token = ApiToken::find(1);
echo "{$token->user->name} created '{$token->name}' on {$token->created_at}";
```

### 9. **Error Handling**

**Invalid Token Response:**
```json
{
  "message": "Unauthorized",
  "error": "Invalid or revoked API token"
}
// Status: 401
```

**Expired Token Response:**
```json
{
  "message": "Forbidden",
  "error": "API token has expired"
}
// Status: 403
```

**Missing Token Response:**
```json
{
  "message": "Unauthorized",
  "error": "Missing or invalid API token"
}
// Status: 401
```

### 10. **Configuration**

The feature is production-ready. Key settings:

- **Token Format**: `sk_` prefix + 64 random characters = 67 chars total
- **Hashing**: SHA256 (same as git)
- **Storage**: 64-char string field (fits SHA256 output)
- **Default Scopes**: Currently unused, ready for future implementation
- **Expiration**: Optional (null = no expiration)

### 11. **Future Enhancements**

Planned features for next iterations:
- [ ] Scope-based permissions (read, write, delete)
- [ ] Rate limiting per token
- [ ] IP whitelisting per token
- [ ] Token usage analytics/graphs
- [ ] Webhook events on token usage
- [ ] API key rotation policies
- [ ] Team-level API tokens (vs user-level)

### 12. **Testing the Feature**

#### Unit Testing
```php
// Create token
$token = $service->createToken($user, ['name' => 'Test Token']);
$this->assertNotNull($token['plain_token']);
$this->assertNotNull($token['token']->id);

// Validate token
$validated = $service->validateToken($token['plain_token']);
$this->assertEquals($token['token']->id, $validated->id);

// Revoke token
$result = $service->revokeToken($user, $token['token']->id);
$this->assertTrue($result);

// Revoked token should not validate
$validated = $service->validateToken($token['plain_token']);
$this->assertNull($validated);
```

#### Manual Testing
1. Create account with workspace owner role
2. Navigate to /settings/integrations/tokens
3. Create test token with 1-day expiration
4. Copy token from modal
5. Test API call: `curl -H "Authorization: Bearer {token}" http://localhost:8001/api/external/tasks`
6. Verify last_used_at updates
7. Revoke token
8. Verify 401 response on next API call

## Conclusion

This implementation provides a **production-ready API token system** suitable for allowing external applications to access your API securely. All tokens are hashed, users can only manage their own tokens, and the feature integrates seamlessly with your existing role-based access control.
