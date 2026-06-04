# API V1 Quick Start Guide

## Getting Started

This guide will help you quickly test the newly implemented API v1 endpoints.

## Base URL

All API endpoints are prefixed with:
```
http://your-domain.com/api/v1
```

For local development:
```
http://localhost:8000/api/v1
```

## Authentication

### Step 1: Register a New User

**Endpoint**: `POST /api/v1/auth/register`

**Request**:
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123"
  }'
```

**Response**:
```json
{
  "message": "Registration successful",
  "user": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2024-03-15T10:30:00Z",
    "created_at": "2024-03-15T10:30:00Z"
  },
  "token": "your-api-token-here"
}
```

**Save the token** - you'll need it for all subsequent requests!

### Step 2: Login (Alternative to Register)

**Endpoint**: `POST /api/v1/auth/login`

**Request**:
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "Password123"
  }'
```

**Response**:
```json
{
  "message": "Login successful",
  "user": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2024-03-15T10:30:00Z",
    "last_login_at": "2024-03-15T10:30:00Z"
  },
  "token": "your-api-token-here"
}
```

### Using Your Token

For all protected endpoints, include the token in one of three ways:

**Option 1: Bearer Token (Recommended)**
```bash
curl -H "Authorization: Bearer your-api-token-here" \
  http://localhost:8000/api/v1/users/me
```

**Option 2: Custom Header**
```bash
curl -H "X-API-Token: your-api-token-here" \
  http://localhost:8000/api/v1/users/me
```

**Option 3: Query Parameter**
```bash
curl http://localhost:8000/api/v1/users/me?api_token=your-api-token-here
```

## Common Workflows

### Workflow 1: Create Workspace and Project

**Step 1: Get Your Profile**
```bash
curl -X GET http://localhost:8000/api/v1/users/me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Step 2: Create a Workspace**
```bash
curl -X POST http://localhost:8000/api/v1/workspaces \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Workspace",
    "description": "My first workspace",
    "avatar_color": "#3B82F6"
  }'
```

**Response**: Save the `workspace_id` from the response.

**Step 3: Create a Project**
```bash
curl -X POST http://localhost:8000/api/v1/projects \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "organization_id": "WORKSPACE_ID",
    "name": "My First Project",
    "description": "Project description",
    "manager_id": "YOUR_USER_ID",
    "status": "on_track",
    "visibility": "public_to_team"
  }'
```

**Response**: Save the `project_id` from the response.

### Workflow 2: Create and Manage Tasks

**Step 1: Create a Task**
```bash
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My First Task",
    "description": "Task description",
    "priority": "high",
    "status": "not_started",
    "due_date": "2024-12-31"
  }'
```

**Step 2: Get Task Details**
```bash
curl -X GET http://localhost:8000/api/v1/tasks/TASK_ID \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Step 3: Update Task**
```bash
curl -X PUT http://localhost:8000/api/v1/tasks/TASK_ID \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "in_progress",
    "priority": "urgent"
  }'
```

**Step 4: Complete Task**
```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/complete \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Step 5: Add Comment to Task**
```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "This is a comment on the task"
  }'
```

### Workflow 3: Manage Workspace Members

**Step 1: Invite Member**
```bash
curl -X POST http://localhost:8000/api/v1/workspaces/WORKSPACE_ID/members/invite \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "teammate@example.com",
    "role": "member"
  }'
```

**Step 2: List Members**
```bash
curl -X GET http://localhost:8000/api/v1/workspaces/WORKSPACE_ID/members \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Step 3: Update Member Role**
```bash
curl -X PATCH http://localhost:8000/api/v1/workspaces/WORKSPACE_ID/members/USER_ID/role \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "role": "owner"
  }'
```

### Workflow 4: Custom Fields

**Step 1: Create Custom Field**
```bash
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/custom-fields \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Priority Level",
    "type": "dropdown",
    "options": ["Low", "Medium", "High", "Critical"],
    "is_required": false
  }'
```

**Step 2: Set Custom Field Value on Task**
```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/custom-fields/CUSTOM_FIELD_ID/value \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "value": "High"
  }'
```

## Testing with Postman

### Import Collection

1. Open Postman
2. Click "Import"
3. Create a new collection called "Asana API V1"
4. Add environment variables:
   - `base_url`: `http://localhost:8000/api/v1`
   - `token`: `your-api-token-here`
5. Use `{{base_url}}` and `{{token}}` in your requests

### Sample Postman Request

**Get Current User**
- Method: `GET`
- URL: `{{base_url}}/users/me`
- Headers:
  - `Authorization`: `Bearer {{token}}`
  - `Accept`: `application/json`

## Common Error Responses

### 401 Unauthenticated
```json
{
  "message": "Unauthenticated",
  "error": "Missing or invalid API token"
}
```
**Solution**: Check your token and ensure it's included in the request.

### 403 Forbidden
```json
{
  "message": "Forbidden"
}
```
**Solution**: You don't have permission to access this resource.

### 422 Validation Error
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```
**Solution**: Fix the validation errors and try again.

### 429 Too Many Requests
```json
{
  "message": "Too many login attempts. Please try again later."
}
```
**Solution**: Wait 15 minutes before trying again.

## Advanced Features

### Filtering Tasks

```bash
curl -X GET "http://localhost:8000/api/v1/projects/PROJECT_ID/tasks?filters[status][]=completed&filters[priority][]=high" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Sorting Tasks

```bash
curl -X GET "http://localhost:8000/api/v1/projects/PROJECT_ID/tasks?sort[0][field]=due_date&sort[0][direction]=asc" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Search Tasks

```bash
curl -X GET "http://localhost:8000/api/v1/tasks/search?query=urgent&workspace_id=WORKSPACE_ID" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Pagination

For endpoints that return lists, use pagination parameters:
```bash
curl -X GET "http://localhost:8000/api/v1/tasks/TASK_ID/activities?page=2" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response includes pagination metadata:
```json
{
  "data": [...],
  "pagination": {
    "current_page": 2,
    "last_page": 5,
    "per_page": 10,
    "total": 48,
    "has_more": true
  }
}
```

## Testing Checklist

- [ ] Register new user
- [ ] Login with credentials
- [ ] Get current user profile
- [ ] Create workspace
- [ ] Create project
- [ ] Create task
- [ ] Update task
- [ ] Complete task
- [ ] Add comment
- [ ] Upload attachment
- [ ] Create custom field
- [ ] Invite workspace member
- [ ] Add project member
- [ ] Search tasks
- [ ] Logout (revoke token)

## Troubleshooting

### Routes Not Found (404)
- Ensure you've cleared the route cache: `php artisan route:clear`
- Check `bootstrap/app.php` has the route registration

### Authentication Not Working
- Verify `auth.api-token` middleware is registered
- Check token is valid and not expired
- Ensure user exists in database

### Database Errors
- Run migrations: `php artisan migrate`
- Check database connection in `.env`

## Next Steps

1. **Create Postman Collection** - Document all endpoints
2. **Write API Tests** - Use PHPUnit to test all endpoints
3. **Add Rate Limiting** - Protect endpoints from abuse
4. **Create OpenAPI Spec** - Generate Swagger documentation
5. **Monitor Performance** - Add logging and analytics

## Support

For issues or questions:
- Check the main implementation summary: `API_V1_IMPLEMENTATION_SUMMARY.md`
- Review the analysis report: `API_INTEGRATION_ANALYSIS_REPORT.md`
- Check Laravel logs: `storage/logs/laravel.log`

---

**Happy Testing! 🚀**
