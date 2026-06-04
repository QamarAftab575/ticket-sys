# API V1 Endpoint Reference

Complete reference of all 71 REST API endpoints.

## Table of Contents

1. [Authentication Endpoints](#authentication-endpoints)
2. [User Endpoints](#user-endpoints)
3. [Workspace Endpoints](#workspace-endpoints)
4. [Project Endpoints](#project-endpoints)
5. [Task Endpoints](#task-endpoints)
6. [Custom Field Endpoints](#custom-field-endpoints)
7. [Comment Endpoints](#comment-endpoints)
8. [Attachment Endpoints](#attachment-endpoints)

---

## Authentication Endpoints

### Register
- **POST** `/api/v1/auth/register`
- **Auth**: None
- **Body**: `{ name, email, password }`
- **Returns**: User object + API token

### Login
- **POST** `/api/v1/auth/login`
- **Auth**: None
- **Body**: `{ email, password }`
- **Returns**: User object + API token

### Logout
- **POST** `/api/v1/auth/logout`
- **Auth**: Required
- **Returns**: Success message

### Forgot Password
- **POST** `/api/v1/auth/forgot-password`
- **Auth**: None
- **Body**: `{ email }`
- **Returns**: Success message

### Reset Password
- **POST** `/api/v1/auth/reset-password`
- **Auth**: None
- **Body**: `{ token, password, password_confirmation }`
- **Returns**: Success message

### Refresh Token
- **POST** `/api/v1/auth/refresh-token`
- **Auth**: Required
- **Returns**: New API token

---

## User Endpoints

### Get Current User
- **GET** `/api/v1/users/me`
- **Auth**: Required
- **Returns**: Current user details

### Update Profile
- **PUT** `/api/v1/users/me`
- **Auth**: Required
- **Body**: `{ name?, email?, avatar?, timezone? }`
- **Returns**: Updated user details

### Get User by ID
- **GET** `/api/v1/users/{user}`
- **Auth**: Required
- **Returns**: User details

### Search Users
- **GET** `/api/v1/users/search`
- **Auth**: Required
- **Query**: `query, limit?`
- **Returns**: Array of users

---

## Workspace Endpoints

### List Workspaces
- **GET** `/api/v1/workspaces`
- **Auth**: Required
- **Returns**: Array of workspaces

### Create Workspace
- **POST** `/api/v1/workspaces`
- **Auth**: Required
- **Body**: `{ name, description?, types?, avatar_color? }`
- **Returns**: Created workspace

### Get Workspace
- **GET** `/api/v1/workspaces/{organization}`
- **Auth**: Required
- **Returns**: Workspace details

### Update Workspace
- **PUT** `/api/v1/workspaces/{organization}`
- **Auth**: Required
- **Body**: `{ name?, description?, types?, avatar_color? }`
- **Returns**: Updated workspace

### Delete Workspace
- **DELETE** `/api/v1/workspaces/{organization}`
- **Auth**: Required
- **Returns**: 204 No Content

### List Members
- **GET** `/api/v1/workspaces/{organization}/members`
- **Auth**: Required
- **Returns**: Array of members

### Invite Member
- **POST** `/api/v1/workspaces/{organization}/members/invite`
- **Auth**: Required
- **Body**: `{ email, role }`
- **Returns**: Invitation details

### Pending Invitations
- **GET** `/api/v1/workspaces/{organization}/members/pending`
- **Auth**: Required
- **Returns**: Array of pending invitations

### Remove Member
- **DELETE** `/api/v1/workspaces/{organization}/members/{user}`
- **Auth**: Required
- **Returns**: Success message

### Update Member Role
- **PATCH** `/api/v1/workspaces/{organization}/members/{user}/role`
- **Auth**: Required
- **Body**: `{ role }`
- **Returns**: Success message

### List Users
- **GET** `/api/v1/workspaces/{organization}/users`
- **Auth**: Required
- **Returns**: Array of users

---

## Project Endpoints

### List Projects
- **GET** `/api/v1/projects`
- **Auth**: Required
- **Query**: `workspace_id?, archived?`
- **Returns**: Array of projects

### Create Project
- **POST** `/api/v1/projects`
- **Auth**: Required
- **Body**: `{ organization_id, name, description?, manager_id, status?, visibility?, privacy?, start_date?, target_date?, color?, icon?, owner_id?, member_ids? }`
- **Returns**: Created project

### Get Project
- **GET** `/api/v1/projects/{project}`
- **Auth**: Required
- **Returns**: Project details

### Update Project
- **PUT** `/api/v1/projects/{project}`
- **Auth**: Required
- **Body**: `{ name?, description?, status?, visibility?, start_date?, target_date?, color?, icon? }`
- **Returns**: Updated project

### Delete Project
- **DELETE** `/api/v1/projects/{project}`
- **Auth**: Required
- **Returns**: 204 No Content

### Archive Project
- **POST** `/api/v1/projects/{project}/archive`
- **Auth**: Required
- **Returns**: Archived project

### Unarchive Project
- **POST** `/api/v1/projects/{project}/unarchive`
- **Auth**: Required
- **Returns**: Unarchived project

### Duplicate Project
- **POST** `/api/v1/projects/{project}/duplicate`
- **Auth**: Required
- **Body**: `{ copy_tasks?, copy_members? }`
- **Returns**: Duplicated project

### List Members
- **GET** `/api/v1/projects/{project}/members`
- **Auth**: Required
- **Returns**: Array of members

### Add Member
- **POST** `/api/v1/projects/{project}/members`
- **Auth**: Required
- **Body**: `{ user_id, role }`
- **Returns**: Success message

### Remove Member
- **DELETE** `/api/v1/projects/{project}/members/{user}`
- **Auth**: Required
- **Returns**: Success message

### Update Member Role
- **PATCH** `/api/v1/projects/{project}/members/{user}/role`
- **Auth**: Required
- **Body**: `{ role }`
- **Returns**: Success message

### List Users
- **GET** `/api/v1/projects/{project}/users`
- **Auth**: Required
- **Returns**: Array of users

### Get Activity
- **GET** `/api/v1/projects/{project}/activity`
- **Auth**: Required
- **Returns**: Paginated activity log

### List Sections
- **GET** `/api/v1/projects/{project}/sections`
- **Auth**: Required
- **Returns**: Array of sections

### List Project Tasks
- **GET** `/api/v1/projects/{project}/tasks`
- **Auth**: Required
- **Query**: `filters?, sort?`
- **Returns**: Array of tasks

### Create Project Task
- **POST** `/api/v1/projects/{project}/tasks`
- **Auth**: Required
- **Body**: `{ name, description?, section_id?, assignee_id?, priority?, status?, start_date?, due_date?, tag_ids? }`
- **Returns**: Created task

### List Custom Fields
- **GET** `/api/v1/projects/{project}/custom-fields`
- **Auth**: Required
- **Returns**: Array of custom fields

### Create Custom Field
- **POST** `/api/v1/projects/{project}/custom-fields`
- **Auth**: Required
- **Body**: `{ name, type, description?, options?, is_required?, is_active? }`
- **Returns**: Created custom field

---

## Task Endpoints

### Search Tasks
- **GET** `/api/v1/tasks/search`
- **Auth**: Required
- **Query**: `query, project_id?, workspace_id?, limit?`
- **Returns**: Array of tasks

### Get Task
- **GET** `/api/v1/tasks/{task}`
- **Auth**: Required
- **Returns**: Task details with relationships

### Update Task
- **PUT** `/api/v1/tasks/{task}`
- **Auth**: Required
- **Body**: `{ name?, description?, section_id?, assignee_id?, priority?, status?, start_date?, due_date?, tag_ids? }`
- **Returns**: Updated task

### Delete Task
- **DELETE** `/api/v1/tasks/{task}`
- **Auth**: Required
- **Returns**: 204 No Content

### Complete Task
- **POST** `/api/v1/tasks/{task}/complete`
- **Auth**: Required
- **Returns**: Completed task

### Reopen Task
- **POST** `/api/v1/tasks/{task}/reopen`
- **Auth**: Required
- **Returns**: Reopened task

### Duplicate Task
- **POST** `/api/v1/tasks/{task}/duplicate`
- **Auth**: Required
- **Returns**: Duplicated task

### Move Task
- **POST** `/api/v1/tasks/{task}/move`
- **Auth**: Required
- **Body**: `{ section_id?, position?, my_tasks_section_id?, my_tasks_position? }`
- **Returns**: Moved task

### List Subtasks
- **GET** `/api/v1/tasks/{task}/subtasks`
- **Auth**: Required
- **Returns**: Array of subtasks

### Create Subtask
- **POST** `/api/v1/tasks/{task}/subtasks`
- **Auth**: Required
- **Body**: `{ name, assignee_id?, start_date?, due_date?, status? }`
- **Returns**: Created subtask

### Add Dependency
- **POST** `/api/v1/tasks/{task}/dependencies`
- **Auth**: Required
- **Body**: `{ depends_on_task_id, type }`
- **Returns**: Task with dependencies

### Remove Dependency
- **DELETE** `/api/v1/tasks/{task}/dependencies/{dependsOnTask}`
- **Auth**: Required
- **Returns**: Task with updated dependencies

### Get Activities
- **GET** `/api/v1/tasks/{task}/activities`
- **Auth**: Required
- **Returns**: Paginated activities

### List Comments
- **GET** `/api/v1/tasks/{task}/comments`
- **Auth**: Required
- **Returns**: Array of comments

### Create Comment
- **POST** `/api/v1/tasks/{task}/comments`
- **Auth**: Required
- **Body**: `{ content }`
- **Returns**: Created comment

### List Attachments
- **GET** `/api/v1/tasks/{task}/attachments`
- **Auth**: Required
- **Returns**: Array of attachments

### Upload Attachment
- **POST** `/api/v1/tasks/{task}/attachments`
- **Auth**: Required
- **Body**: `multipart/form-data { file }`
- **Returns**: Uploaded attachment

### Set Custom Field Value
- **POST** `/api/v1/tasks/{task}/custom-fields/{customField}/value`
- **Auth**: Required
- **Body**: `{ value }`
- **Returns**: Success message

---

## Custom Field Endpoints

### Get Custom Field
- **GET** `/api/v1/custom-fields/{customField}`
- **Auth**: Required
- **Returns**: Custom field details

### Update Custom Field
- **PUT** `/api/v1/custom-fields/{customField}`
- **Auth**: Required
- **Body**: `{ name?, type?, description?, options?, is_required?, is_active? }`
- **Returns**: Updated custom field

### Delete Custom Field
- **DELETE** `/api/v1/custom-fields/{customField}`
- **Auth**: Required
- **Returns**: 204 No Content

### Toggle Active Status
- **POST** `/api/v1/custom-fields/{customField}/toggle-active`
- **Auth**: Required
- **Returns**: Updated custom field

---

## Comment Endpoints

### Get Comment
- **GET** `/api/v1/comments/{comment}`
- **Auth**: Required
- **Returns**: Comment details

### Update Comment
- **PUT** `/api/v1/comments/{comment}`
- **Auth**: Required (must be author)
- **Body**: `{ content }`
- **Returns**: Updated comment

### Delete Comment
- **DELETE** `/api/v1/comments/{comment}`
- **Auth**: Required (must be author or task owner)
- **Returns**: 204 No Content

---

## Attachment Endpoints

### Get Attachment
- **GET** `/api/v1/attachments/{attachment}`
- **Auth**: Required
- **Returns**: Attachment details

### Download Attachment
- **GET** `/api/v1/attachments/{attachment}/download`
- **Auth**: Required
- **Returns**: File download

### Delete Attachment
- **DELETE** `/api/v1/attachments/{attachment}`
- **Auth**: Required (must be uploader or task owner)
- **Returns**: 204 No Content

---

## Common Parameters

### Query Parameters
- `page` - Page number for pagination (default: 1)
- `per_page` - Items per page (default: varies by endpoint)
- `filters` - Array of filters (varies by endpoint)
- `sort` - Array of sort criteria (varies by endpoint)

### Response Format

**Success Response**:
```json
{
  "data": { ... } or [ ... ],
  "message": "Success message"
}
```

**Error Response**:
```json
{
  "message": "Error message",
  "errors": {
    "field": ["Error details"]
  }
}
```

**Paginated Response**:
```json
{
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 48,
    "has_more": true
  }
}
```

---

## Status Codes

- `200` - OK (successful GET/PUT)
- `201` - Created (successful POST)
- `204` - No Content (successful DELETE)
- `400` - Bad Request
- `401` - Unauthenticated
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity (validation error)
- `429` - Too Many Requests
- `500` - Internal Server Error

---

**Total: 71 Endpoints**
