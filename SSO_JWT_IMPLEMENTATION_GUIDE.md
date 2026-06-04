# SSO JWT Implementation Guide

## Overview

This implementation provides a **secure, scalable, invitation-only authentication system** using JWT (JSON Web Tokens) for Single Sign-On (SSO).

## Key Features

✅ **No Public Registration** - Users cannot self-register via API
✅ **Invitation-Only** - Users join via workspace/project invitations
✅ **JWT-based SSO** - Signed tokens for partner applications
✅ **Database-Free Tokens** - JWTs verified by signature (no DB lookup)
✅ **Replay Attack Prevention** - One-time use tokens with cache tracking
✅ **Scalable** - No database bottleneck for token validation

## Architecture

### Flow 1: Invitation-Based User Creation

```
1. Workspace Owner → Invites user via email
2. User → Receives invitation email
3. User → Clicks invitation link
4. System → Creates user account
5. User → Sets password and completes profile
6. User → Can now login via API
```

### Flow 2: SSO Token Generation (for Partner Apps)

```
1. Authenticated User → POST /api/v1/sso/token
2. System → Generates signed JWT token
3. Response → Returns JWT + login URL
4. Partner App → Uses login URL to authenticate user
5. System → Validates JWT and creates session/API token
```

### Flow 3: JWT to API Token Exchange

```
1. Partner App → POST /api/v1/sso/exchange with JWT
2. System → Validates JWT signature
3. System → Creates long-lived API token
4. Response → Returns API token for subsequent requests
```

## Installation

### Step 1: Install JWT Library

```bash
composer require firebase/php-jwt
```

### Step 2: Generate JWT Secret

```bash
php artisan tinker
```

Then in tinker:
```php
echo \App\Services\SsoJwtService::generateSecret();
```

### Step 3: Add to .env

Copy the generated secret and add to `.env`:
```env
JWT_SECRET=your-generated-secret-here
```

**Important**: Keep this secret secure! Anyone with this secret can forge tokens.

## API Endpoints

### 1. Generate SSO Token (Authenticated)

**Endpoint**: `POST /api/v1/sso/token`

**Headers**:
```
Authorization: Bearer {api_token}
Content-Type: application/json
```

**Body**:
```json
{
  "expires_in": 3600,
  "organization_id": "workspace-uuid",
  "redirect_url": "https://partner-app.com/dashboard"
}
```

**Response**:
```json
{
  "message": "SSO token generated successfully",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "login_url": "https://yourapp.com/sso/login?token=eyJh...",
  "expires_in": 3600,
  "user": {
    "id": "user-uuid",
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### 2. Generate Token for User (Admin Only)

**Endpoint**: `POST /api/v1/sso/token/user/{user_id}`

**Headers**:
```
Authorization: Bearer {admin_api_token}
Content-Type: application/json
```

**Body**:
```json
{
  "organization_id": "workspace-uuid",
  "expires_in": 1800,
  "redirect_url": "https://partner-app.com/onboarding"
}
```

**Authorization**: Only workspace administrators can generate tokens for other users.

**Response**: Same as endpoint #1

### 3. Exchange JWT for API Token

**Endpoint**: `POST /api/v1/sso/exchange`

**Headers**:
```
Content-Type: application/json
```

**Body**:
```json
{
  "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Response**:
```json
{
  "message": "Token exchange successful",
  "api_token": "long-lived-api-token-here",
  "expires_at": "2024-04-15T10:30:00Z",
  "user": {
    "id": "user-uuid",
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Note**: The JWT can only be used once. Subsequent attempts will fail.

### 4. Validate JWT Token

**Endpoint**: `POST /api/v1/sso/validate`

**Headers**:
```
Content-Type: application/json
```

**Body**:
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Response** (Valid):
```json
{
  "valid": true,
  "payload": {
    "user_id": "user-uuid",
    "email": "john@example.com",
    "name": "John Doe",
    "issued_at": "2024-03-15T10:30:00Z",
    "expires_at": "2024-03-15T11:30:00Z"
  }
}
```

**Response** (Invalid):
```json
{
  "valid": false,
  "message": "Invalid or expired token"
}
```

### 5. Register Endpoint (Disabled)

**Endpoint**: `POST /api/v1/auth/register`

**Response**:
```json
{
  "message": "Registration is not available via API",
  "error": "Users can only join through workspace or project invitations"
}
```

## Use Cases

### Use Case 1: Partner Application Integration

**Scenario**: You have a partner application that needs to authenticate users from your platform.

**Implementation**:

1. User logs into your platform
2. User clicks "Connect to Partner App"
3. Your app calls `POST /api/v1/sso/token` with redirect URL
4. Your app redirects user to partner app with JWT in URL
5. Partner app exchanges JWT for API token via `POST /api/v1/sso/exchange`
6. Partner app uses API token for subsequent requests

### Use Case 2: Mobile App Auto-Login

**Scenario**: Your mobile app needs to auto-login users.

**Implementation**:

1. User logs in on web platform
2. Web platform generates SSO token
3. User scans QR code or clicks deep link with JWT
4. Mobile app exchanges JWT for API token
5. Mobile app stores API token for future requests

### Use Case 3: Admin-Generated Login Links

**Scenario**: Support team needs to help users login.

**Implementation**:

1. Support admin generates SSO token for user
2. Admin sends magic login link to user
3. User clicks link
4. System validates JWT and creates session
5. User is automatically logged in

## Security Features

### 1. Signature Verification
- All JWTs signed with HS256 algorithm
- Secret key stored securely in environment variables
- Tampering with token invalidates signature

### 2. Replay Attack Prevention
- Each JWT has unique ID (`jti` claim)
- JWT ID tracked in cache after first use
- Subsequent uses of same token rejected

### 3. Token Expiration
- Short-lived JWT tokens (default 1 hour)
- Configurable expiration time (1 min to 1 hour)
- Expired tokens rejected automatically

### 4. Organization-Scoped Access
- Tokens can be scoped to specific workspaces
- User's workspace membership verified before token generation
- Prevents unauthorized cross-workspace access

### 5. Admin Controls
- Only workspace admins can generate tokens for other users
- Regular users can only generate tokens for themselves
- Prevents privilege escalation

## JWT Payload Structure

```json
{
  "iss": "https://yourapp.com",
  "sub": "user-uuid",
  "email": "john@example.com",
  "name": "John Doe",
  "iat": 1710500000,
  "exp": 1710503600,
  "jti": "unique-token-id",
  "organization_id": "workspace-uuid",
  "redirect_url": "https://partner-app.com/dashboard"
}
```

**Claims**:
- `iss` - Issuer (your application URL)
- `sub` - Subject (user ID)
- `email` - User email
- `name` - User name
- `iat` - Issued at (Unix timestamp)
- `exp` - Expires at (Unix timestamp)
- `jti` - JWT ID (unique identifier)
- `organization_id` - Optional workspace context
- `redirect_url` - Optional redirect after login

## Error Handling

### 401 Unauthorized
```json
{
  "message": "Invalid or expired JWT token"
}
```

**Causes**:
- Token signature invalid
- Token expired
- Token already used
- User not found

### 403 Forbidden
```json
{
  "message": "Forbidden",
  "error": "Only workspace administrators can generate SSO tokens for users"
}
```

**Causes**:
- Non-admin trying to generate token for another user
- User not member of specified workspace
- Insufficient permissions

## Configuration

### Environment Variables

```env
# Required
JWT_SECRET=your-secure-secret-here

# Optional (defaults shown)
JWT_DEFAULT_EXPIRY=3600  # 1 hour in seconds
JWT_MAX_EXPIRY=3600      # Maximum allowed expiry
JWT_MIN_EXPIRY=60        # Minimum allowed expiry
```

### Cache Configuration

The system uses Laravel's cache to track used JWT IDs. Ensure cache is properly configured:

```env
CACHE_DRIVER=redis  # Recommended for production
# or
CACHE_DRIVER=database  # Alternative for production
```

**Note**: Do NOT use `file` or `array` cache in production with multiple servers.

## Testing

### Test JWT Generation

```bash
curl -X POST http://localhost:8000/api/v1/sso/token \
  -H "Authorization: Bearer your-api-token" \
  -H "Content-Type: application/json" \
  -d '{
    "expires_in": 300,
    "organization_id": "workspace-uuid"
  }'
```

### Test Token Exchange

```bash
curl -X POST http://localhost:8000/api/v1/sso/exchange \
  -H "Content-Type: application/json" \
  -d '{
    "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }'
```

### Test Token Validation

```bash
curl -X POST http://localhost:8000/api/v1/sso/validate \
  -H "Content-Type: application/json" \
  -d '{
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }'
```

### Test Registration Disabled

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123"
  }'

# Expected: 403 Forbidden
```

## Migration Guide

### From Old System (with registration)

If you had the old system with public registration:

1. **Install JWT library**: `composer require firebase/php-jwt`
2. **Generate JWT secret**: Use `SsoJwtService::generateSecret()`
3. **Add to .env**: `JWT_SECRET=your-secret`
4. **Test existing users**: Existing users can still login
5. **Test invitations**: Ensure invitation flow works
6. **Disable old endpoints**: Registration returns 403

### For Partner Applications

Update your integration to:

1. **Exchange JWT for API token** on user login
2. **Store API token** securely
3. **Use API token** for subsequent requests
4. **Handle token expiration** gracefully

## Best Practices

### 1. Token Lifetime
- Use short-lived JWTs (5-60 minutes)
- Exchange for long-lived API tokens (days/weeks)
- Refresh API tokens periodically

### 2. Secret Management
- **Never** commit JWT_SECRET to version control
- Use different secrets for dev/staging/production
- Rotate secrets periodically

### 3. HTTPS Only
- **Always** use HTTPS in production
- JWTs in URLs are visible in logs
- Consider POST body instead of URL params

### 4. Rate Limiting
- Limit token generation endpoints
- Prevent abuse by malicious users
- Monitor token exchange patterns

### 5. Logging
- Log all SSO token generations
- Log failed validation attempts
- Monitor for suspicious patterns

## Troubleshooting

### "JWT_SECRET not configured"
**Solution**: Add `JWT_SECRET` to your `.env` file.

### "Token has already been used"
**Solution**: Generate a new token. JWTs are one-time use.

### "Invalid signature"
**Solution**: Verify JWT_SECRET matches on both sides.

### "Token expired"
**Solution**: Generate a new token. Old tokens cannot be refreshed.

### Cache not working
**Solution**: Verify cache driver is configured and working:
```bash
php artisan cache:clear
php artisan config:cache
```

## Production Checklist

- [ ] JWT_SECRET generated and added to .env
- [ ] JWT_SECRET different from dev/staging
- [ ] HTTPS enabled on all environments
- [ ] Cache driver configured (Redis recommended)
- [ ] Rate limiting enabled on SSO endpoints
- [ ] Logging configured for security events
- [ ] Invitation flow tested end-to-end
- [ ] Token expiration times configured
- [ ] Partner apps updated to use new flow
- [ ] Documentation shared with partners

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Verify JWT_SECRET is set correctly
- Test token validation independently
- Contact your system administrator

---

**Secure by Design. Scalable by Architecture. 🔐**
