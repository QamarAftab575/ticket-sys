# Postman Collection Guide

## Quick Import

### Method 1: Import OpenAPI Spec (Recommended)

1. Start your Laravel application
2. Export the OpenAPI spec:
   ```bash
   curl http://localhost:8000/api/docs/openapi.json -o openapi.json
   ```
3. Open Postman
4. Click **Import** → **Upload Files**
5. Select `openapi.json`
6. Click **Import**

All 75+ endpoints will be automatically imported with:
- Complete request examples
- Response schemas
- Authentication configuration
- Validation rules

### Method 2: Import from URL

1. Open Postman
2. Click **Import** → **Link**
3. Enter: `http://localhost:8000/api/docs/openapi.json`
4. Click **Continue** → **Import**

## Environment Setup

### Step 1: Create Environment

1. Click **Environments** (left sidebar)
2. Click **+** to create new environment
3. Name it: **API V1 Local**

### Step 2: Add Variables

Add these variables:

| Variable | Initial Value | Current Value |
|----------|--------------|---------------|
| `base_url` | `http://localhost:8000/api/v1` | `http://localhost:8000/api/v1` |
| `token` | | (will be set after login) |
| `workspace_id` | | (will be set after creating workspace) |
| `project_id` | | (will be set after creating project) |
| `task_id` | | (will be set after creating task) |

### Step 3: Set Authorization

For all endpoints that require authentication:

1. Select the request
2. Go to **Authorization** tab
3. Type: **Bearer Token**
4. Token: `{{token}}`

## Complete Workflow Example

### 1. Login

**Request**: `POST {{base_url}}/auth/login`

**Body**:
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Tests** (Add in Tests tab):
```javascript
// Save token to environment
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("token", jsonData.token);
    console.log("Token saved:", jsonData.token);
}
```

### 2. Get Current User

**Request**: `GET {{base_url}}/users/me`

**Authorization**: Bearer Token `{{token}}`

**Tests**:
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("User data exists", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('id');
    pm.expect(jsonData.data).to.have.property('email');
});
```

### 3. Create Workspace

**Request**: `POST {{base_url}}/workspaces`

**Authorization**: Bearer Token `{{token}}`

**Body**:
```json
{
  "name": "Engineering Team",
  "description": "Main engineering workspace",
  "avatar_color": "#3B82F6"
}
```

**Tests**:
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    pm.environment.set("workspace_id", jsonData.data.id);
    console.log("Workspace ID saved:", jsonData.data.id);
}
```

### 4. Create Project

**Request**: `POST {{base_url}}/projects`

**Authorization**: Bearer Token `{{token}}`

**Body**:
```json
{
  "organization_id": "{{workspace_id}}",
  "name": "Website Redesign",
  "description": "Complete redesign of company website",
  "manager_id": "USER_ID_HERE",
  "status": "on_track",
  "visibility": "public_to_team"
}
```

**Tests**:
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    pm.environment.set("project_id", jsonData.data.id);
    console.log("Project ID saved:", jsonData.data.id);
}
```

### 5. Create Task

**Request**: `POST {{base_url}}/projects/{{project_id}}/tasks`

**Authorization**: Bearer Token `{{token}}`

**Body**:
```json
{
  "name": "Design homepage mockup",
  "description": "Create initial mockup for homepage redesign",
  "priority": "high",
  "status": "not_started",
  "due_date": "2024-12-31"
}
```

**Tests**:
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    pm.environment.set("task_id", jsonData.data.id);
    console.log("Task ID saved:", jsonData.data.id);
}
```

## Pre-request Scripts

### Global Pre-request Script

Add this to **Collection** → **Pre-request Script**:

```javascript
// Log request details
console.log("Request:", pm.request.method, pm.request.url.toString());

// Check if token exists for protected endpoints
if (!pm.environment.get("token") && 
    pm.request.url.toString().includes("/api/v1/") &&
    !pm.request.url.toString().includes("/auth/login")) {
    console.warn("Warning: No token set. This request may fail.");
}
```

### Request-specific Pre-request

For endpoints that need dynamic IDs:

```javascript
// Ensure workspace_id is set
if (!pm.environment.get("workspace_id")) {
    throw new Error("workspace_id not set. Please run 'Create Workspace' first.");
}
```

## Test Scripts

### Global Test Script

Add this to **Collection** → **Tests**:

```javascript
// Test response time
pm.test("Response time is less than 2000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});

// Test content type
pm.test("Content-Type is application/json", function () {
    pm.response.to.have.header("Content-Type");
    pm.expect(pm.response.headers.get("Content-Type")).to.include("application/json");
});

// Log response for debugging
if (pm.response.code >= 400) {
    console.error("Error Response:", pm.response.json());
}
```

### Common Test Patterns

**Test Successful Creation**:
```javascript
pm.test("Resource created", function () {
    pm.response.to.have.status(201);
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('message');
    pm.expect(jsonData).to.have.property('data');
    pm.expect(jsonData.data).to.have.property('id');
});
```

**Test Validation Error**:
```javascript
pm.test("Validation error caught", function () {
    pm.response.to.have.status(422);
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('errors');
});
```

**Test Authorization**:
```javascript
pm.test("Unauthorized without token", function () {
    pm.response.to.have.status(401);
});
```

## Collection Runner

### Run Complete Workflow

1. Select collection
2. Click **Run**
3. Select environment: **API V1 Local**
4. Order requests:
   1. Login
   2. Get Current User
   3. Create Workspace
   4. Create Project
   5. Create Task
   6. etc.
5. Click **Run API V1**

### Expected Results

All requests should pass with proper test scripts:
- ✅ Login successful (token saved)
- ✅ User retrieved
- ✅ Workspace created (ID saved)
- ✅ Project created (ID saved)
- ✅ Task created (ID saved)

## Troubleshooting

### Issue: 401 Unauthorized

**Cause**: Token not set or expired

**Solution**:
1. Run **Login** request
2. Check Tests tab to ensure token is saved
3. Verify token in environment: `{{token}}`
4. Retry failed request

### Issue: 404 Not Found

**Cause**: Resource ID not set in environment

**Solution**:
1. Check environment variables
2. Ensure IDs are saved via test scripts
3. Run prerequisite requests first

### Issue: 422 Validation Error

**Cause**: Invalid request payload

**Solution**:
1. Check API documentation for required fields
2. Verify data types match requirements
3. Check validation rules in error response
4. Update request body accordingly

### Issue: 403 Forbidden

**Cause**: Insufficient permissions

**Solution**:
1. Ensure you're a member of the workspace/project
2. Check your role (viewer, editor, admin)
3. Use correct user for the operation
4. Review authorization requirements in docs

## Advanced Features

### Data-Driven Testing

Create a CSV file with test data:

**users.csv**:
```csv
name,email,password
John Doe,john@example.com,Password123
Jane Smith,jane@example.com,Password456
```

1. In Collection Runner, click **Select File**
2. Upload CSV
3. Use variables in requests: `{{name}}`, `{{email}}`

### Mock Server

1. Select collection
2. Click **...** → **Mock Collection**
3. Name: **API V1 Mock**
4. Save each response as example
5. Share mock server URL with team

### API Monitoring

1. Click **Monitors** (left sidebar)
2. Click **+** → **Create Monitor**
3. Select collection: **API V1**
4. Schedule: Every hour
5. Environment: **API V1 Local**
6. Click **Create Monitor**

Postman will run your tests hourly and alert on failures.

## Best Practices

### 1. Organize Folders

```
API V1 Collection
├── Authentication
│   ├── Login
│   ├── Logout
│   └── Refresh Token
├── Workspaces
│   ├── List Workspaces
│   ├── Create Workspace
│   └── ...
├── Projects
│   └── ...
└── Tasks
    └── ...
```

### 2. Use Descriptive Names

- ❌ Bad: `Request 1`, `Test`
- ✅ Good: `Create Workspace`, `Get Project Tasks`

### 3. Add Request Descriptions

In each request, add description:
```markdown
## Create Workspace

Creates a new workspace. The authenticated user is automatically added as owner.

### Request Body
- `name` (required): Workspace name
- `description` (optional): Workspace description
- `avatar_color` (optional): Hex color code

### Authorization
Requires: Authenticated user
```

### 4. Save Examples

For each request:
1. Make successful request
2. Click **Save Response**
3. Name: **Success (201)**
4. Repeat for error cases

### 5. Version Control

Export collection regularly:
1. Select collection
2. Click **...** → **Export**
3. Format: Collection v2.1
4. Save to Git repository

## Sample Postman Collection Structure

```json
{
  "info": {
    "name": "Asana-like API V1",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "auth": {
    "type": "bearer",
    "bearer": [
      {
        "key": "token",
        "value": "{{token}}",
        "type": "string"
      }
    ]
  },
  "item": [
    {
      "name": "Authentication",
      "item": [...]
    },
    {
      "name": "Workspaces",
      "item": [...]
    }
  ]
}
```

## Additional Resources

- **API Documentation**: http://localhost:8000/api/docs
- **Postman Learning Center**: https://learning.postman.com/
- **API Testing Guide**: https://www.postman.com/api-testing/

---

**Ready to test your API! 🚀**
