# API Tokens - Quick Start Guide

## 🎯 What's New

The API Tokens feature is now fully integrated into your Asana-like project management system. This allows workspace owners and admins to:

1. Create API tokens for external integrations
2. Manage and revoke tokens
3. Track token usage
4. Secure API access with token authentication

## 🔗 Access the Feature

**URL**: `http://localhost:8001/settings/integrations/tokens`

**Navigation Path**:
1. Go to Settings
2. Look for "Integrations" section in sidebar
3. Click "Access Tokens" (appears directly under "Google Social Login")

## 🎯 For End Users

### Creating an API Token

1. Navigate to `/settings/integrations/tokens`
2. Scroll to "Create New Token" section
3. Enter a descriptive name (e.g., "Zapier Integration", "External App")
4. (Optional) Set an expiration date
5. Click "Create Token"
6. **IMPORTANT**: Copy the token immediately - you won't see it again!
7. Store it securely in your external application

### Using Your Token

Use the token in API requests via:

```bash
# Recommended: Authorization header
curl -H "Authorization: Bearer sk_xxxxxxxxxx" \
  http://localhost:8001/api/external/tasks

# Alternative: Custom header
curl -H "X-API-Token: sk_xxxxxxxxxx" \
  http://localhost:8001/api/external/tasks

# Alternative: Query parameter
curl "http://localhost:8001/api/external/tasks?api_token=sk_xxxxxxxxxx"
```

### Managing Tokens

- **View all tokens**: Listed in a table with:
  - Token Name
  - Masked token (first 6 chars visible, last 6 visible, rest masked)
  - Created date
  - Last used date
  - Expiration status

- **Revoke a token**: 
  1. Click "Revoke" next to the token
  2. Confirm deletion
  3. Token becomes immediately inactive

- **Revoke all tokens**: 
  1. Click "Revoke All Tokens" button
  2. Confirm deletion
  3. All tokens become inactive

## 🔐 Security

### What You Should Know

- **Tokens are hashed**: Only the hash is stored in the database
- **One-time display**: The full token is shown only once after creation
- **Lost tokens**: If you lose a token, create a new one
- **Expiration**: Tokens can have optional expiration dates
- **Usage tracking**: Last used timestamp is recorded for audit purposes
- **Access control**: Only workspace owners and admins can create tokens

### Best Practices

1. ✅ Use descriptive token names to identify their purpose
2. ✅ Set reasonable expiration dates
3. ✅ Rotate tokens regularly
4. ✅ Store tokens in environment variables or secure vaults
5. ✅ Revoke unused tokens
6. ❌ Don't commit tokens to version control
7. ❌ Don't share tokens in chat or email
8. ❌ Don't log token values

## 🧪 Testing

### Test Creating a Token

```bash
# Navigate to the page
http://localhost:8001/settings/integrations/tokens

# Create a token named "Test Integration"
# Leave expiration blank for testing
# Copy the token
```

### Test API Authentication

```bash
# Get your token from the UI, then test:
TOKEN="sk_your_copied_token_here"

# Test with Bearer token
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8001/api/external/tasks

# Should return: list of user's tasks or auth error
```

### Expected Responses

**Success (401 for invalid token)**:
```json
{
  "message": "Unauthorized",
  "error": "Invalid or revoked API token"
}
```

**Success (tasks fetched)**:
```json
[
  {
    "id": 1,
    "name": "Task 1",
    "project_id": 123,
    ...
  }
]
```

## 🚀 API Endpoints

All endpoints require Bearer token authentication.

### Available Endpoints

```
GET    /api/external/tasks              - List user's tasks
GET    /api/external/tasks/{taskId}     - Get specific task
```

### Authentication

All API calls must include one of:

1. **Authorization Header** (Recommended):
   ```
   Authorization: Bearer sk_xxxxxxxx...xxxxxxxx
   ```

2. **X-API-Token Header**:
   ```
   X-API-Token: sk_xxxxxxxx...xxxxxxxx
   ```

3. **Query Parameter**:
   ```
   ?api_token=sk_xxxxxxxx...xxxxxxxx
   ```

## 📊 Statistics

The API Tokens page displays:
- **Total Tokens**: All tokens ever created for your account
- **Active Tokens**: Non-expired, not revoked tokens
- **Expired Tokens**: Tokens past their expiration date

## 🔧 For Developers

### Token Format
- Prefix: `sk_`
- Total length: 67 characters
- Example: `sk_abcd1234efgh5678ijkl9012mnop3456qrst7890uvwx1234yz...`

### API Response Format

**Token List Item**:
```json
{
  "id": 1,
  "name": "My Integration",
  "masked_token": "sk_abcd1234...uvwx1234",
  "created_at": "2026-06-02T10:30:00Z",
  "last_used_at": "2026-06-02T14:15:00Z",
  "expires_at": "2026-12-02T10:30:00Z",
  "is_active": true,
  "is_expired": false
}
```

**Create Token Response**:
```json
{
  "token": {
    "id": 1,
    "name": "My Integration",
    "masked_token": "sk_abcd1234...uvwx1234",
    "created_at": "2026-06-02T10:30:00Z",
    "last_used_at": null,
    "expires_at": null,
    "is_active": true,
    "is_expired": false
  },
  "plain_token": "sk_abcd1234efgh5678ijkl9012mnop3456qrst7890uvwx1234yz...",
  "message": "API token created successfully. Copy the token below and keep it safe."
}
```

### Error Responses

**Missing Token** (401):
```json
{
  "message": "Unauthorized",
  "error": "Missing or invalid API token"
}
```

**Invalid Token** (401):
```json
{
  "message": "Unauthorized",
  "error": "Invalid or revoked API token"
}
```

**Expired Token** (403):
```json
{
  "message": "Forbidden",
  "error": "API token has expired"
}
```

## 📋 Checklist for Integration

- [ ] User created an API token
- [ ] Token copied and stored securely
- [ ] Tested token in external application
- [ ] Token appears in last_used_at after use
- [ ] Token expiration date set (if needed)
- [ ] Test revoke function
- [ ] Backup/recovery plan for token loss documented

## ❓ FAQ

**Q: I lost my token, what do I do?**
A: Create a new token. Lost tokens cannot be recovered.

**Q: Can I regenerate an existing token?**
A: Not yet. Revoke the old one and create a new one.

**Q: How often should I rotate tokens?**
A: At least every 90 days for security best practices.

**Q: Can tokens have custom scopes?**
A: Currently, all tokens have full access. Scoped tokens coming soon.

**Q: What happens when a token expires?**
A: API requests using that token will return 403 Forbidden.

**Q: Can I see the token value after creation?**
A: No. It's shown once in a modal. After that, it's impossible to retrieve.

**Q: Is the token stored in plain text?**
A: No. Only the SHA256 hash is stored in the database.

**Q: Can multiple users share one token?**
A: Technically yes, but not recommended. Create separate tokens per user for audit trails.

## 📞 Support

For issues or questions:
1. Check that the token hasn't expired
2. Verify the token is correctly copied (no spaces)
3. Ensure you're using one of the three auth methods
4. Check the browser console for errors
5. Verify the API endpoint is correct

## 🔄 Migration from Previous Systems

If migrating from another system:
1. Create new tokens in this system
2. Update external apps to use new tokens
3. Revoke old tokens in previous system
4. Keep audit logs of token migration dates
