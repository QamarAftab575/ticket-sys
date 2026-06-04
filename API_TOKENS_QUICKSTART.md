# API Tokens - Quick Start Guide

## 🚀 Get Started in 3 Minutes

### Step 1: Create a Token

1. Log in to your account
2. Go to **Settings** (top right menu)
3. In the left sidebar, under **Integrations**, click **Access Tokens**
4. Fill in the form:
   - **Token Name**: Give it a descriptive name (e.g., "Mobile App", "External Integration")
   - **Expiration** (optional): Set an expiration date if desired
5. Click **Create Token**
6. ⚠️ **Important**: Copy the token from the modal that appears. It will never be shown again!

### Step 2: Use the Token in Your Application

#### Using cURL
```bash
curl -H "Authorization: Bearer sk_live_..." \
     https://yourapp.com/api/external/tasks
```

#### Using Fetch (JavaScript)
```javascript
const token = 'sk_live_...';

fetch('https://yourapp.com/api/external/tasks', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
.then(res => res.json())
.then(data => console.log(data));
```

#### Using Python Requests
```python
import requests

token = 'sk_live_...'
headers = {'Authorization': f'Bearer {token}'}

response = requests.get(
    'https://yourapp.com/api/external/tasks',
    headers=headers
)
print(response.json())
```

#### Using Axios (JavaScript)
```javascript
const token = 'sk_live_...';

axios.get('https://yourapp.com/api/external/tasks', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
.then(res => console.log(res.data));
```

### Step 3: Manage Your Tokens

**View Tokens:**
- All your active tokens are listed on the Access Tokens page
- Shows token name, creation date, last used date, and expiration

**Revoke a Token:**
- Click the **Revoke** button next to any token
- Once revoked, it can no longer be used for API calls
- Confirm the action when prompted

**Revoke All Tokens:**
- Click **Revoke All Tokens** at the bottom of the page
- Useful when you suspect a token has been compromised

## 📚 API Endpoints

### Get All Tasks
```
GET /api/external/tasks
Authorization: Bearer sk_live_...
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "Task Name",
    "status": "in_progress",
    "project_id": 5,
    ...
  }
]
```

### Get Single Task
```
GET /api/external/tasks/{id}
Authorization: Bearer sk_live_...
```

## 🔒 Security Tips

1. **Keep tokens secret** - Treat them like passwords
2. **Don't commit tokens to version control** - Use environment variables
3. **Use separate tokens** - Create different tokens for different applications
4. **Set expiration dates** - Tokens with expiration are safer
5. **Monitor usage** - Check "Last Used" date regularly
6. **Revoke unused tokens** - Clean up tokens you no longer need
7. **Rotate tokens** - Periodically create new ones and revoke old ones

## ❓ FAQs

**Q: Can I see the full token after creation?**
A: No. For security reasons, the full token is only displayed once. If you lose it, you must create a new token.

**Q: Can I change a token's name?**
A: Not yet. You can revoke it and create a new one with a different name.

**Q: How many tokens can I create?**
A: Unlimited. Create as many as you need.

**Q: What happens if my token expires?**
A: API calls with an expired token will return a 403 Forbidden error. You'll need to create a new token.

**Q: Can I see which application is using my token?**
A: Yes, check the "Last Used" date. The more recent it is, the more actively it's being used.

**Q: Are tokens safe?**
A: Yes. They're hashed using SHA256 before storage, so even if the database is compromised, attackers can't use the tokens directly.

**Q: Can I restrict a token to certain IP addresses?**
A: Not yet, but this feature is planned for future releases.

## 🆘 Troubleshooting

**"401 Unauthorized" error:**
- Token is missing, invalid, or revoked
- Check the Authorization header is correct: `Bearer sk_live_...`
- Verify the token hasn't been revoked in the UI

**"403 Forbidden" error:**
- Token has expired
- Create a new token with a later expiration date

**"404 Not Found" error:**
- The API endpoint doesn't exist
- Check the endpoint URL is correct

**Can't access the Access Tokens page:**
- You must be a Workspace Owner or Admin
- Contact your workspace administrator if you need access

## 💡 Example Integrations

### Zapier
```
Webhook URL: https://yourapp.com/api/external/tasks
Headers:
  Authorization: Bearer sk_live_...
```

### Make.com (formerly Integromat)
```
URL: https://yourapp.com/api/external/tasks
Custom headers: 
  Authorization: Bearer sk_live_...
```

### GitHub Actions
```yaml
- name: Call API
  run: |
    curl -H "Authorization: Bearer ${{ secrets.API_TOKEN }}" \
         https://yourapp.com/api/external/tasks
```

### cron Job
```bash
#!/bin/bash
TOKEN="sk_live_..."
curl -H "Authorization: Bearer $TOKEN" \
     https://yourapp.com/api/external/tasks
```

---

**Need help?** Check the full documentation at `API_TOKENS_GUIDE.md` or contact your system administrator.
