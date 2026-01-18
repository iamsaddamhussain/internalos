# Testing Automations with Mailtrap

## 🎯 Quick Setup Guide

### Step 1: Get Mailtrap Credentials

1. Go to [mailtrap.io](https://mailtrap.io) and sign up (free account)
2. Navigate to **Email Testing** → **Inboxes**
3. Click on your inbox (or create a new one)
4. Copy the **SMTP Settings**:
   - Host: `sandbox.smtp.mailtrap.io`
   - Port: `2525`
   - Username: (copy from Mailtrap)
   - Password: (copy from Mailtrap)

### Step 2: Configure Your .env File

Update your `.env` file with Mailtrap credentials:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username_here
MAIL_PASSWORD=your_mailtrap_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@internalos.test"
MAIL_FROM_NAME="InternalOS"
```

### Step 3: Clear Config Cache

```bash
php artisan config:clear
```

### Step 4: Create an Email Automation

1. Go to your workspace
2. Navigate to **Automations**
3. Click **Create Automation**
4. Configure:

**Basic Info:**
- Name: "Email Test Automation"
- Description: "Testing email notifications"
- Active: ✅ **CHECK THIS!**

**Trigger:**
- Type: `Record Created` (easiest to test)

**Actions:**
- Click "Add Action"
- Type: **Send Email** (not "Send Notification")
- Target: `creator` (will email the person who created the record)
- Title: `New Record Created!`
- Message:
  ```
  A new record has been created.
  
  Record details:
  - Title: {{field_1234567890}} (use your actual field ID)
  
  Please review it.
  ```

### Step 5: Test It!

**Option A: Create a New Record**
1. Go to the collection that has this automation
2. Click "Add Record"
3. Fill in the form
4. Click "Create"
5. **Check Mailtrap inbox** - email should appear within seconds!

**Option B: Run Manually (for date-based)**
```bash
php artisan automations:run
```

### Step 6: Check Results

**In Mailtrap:**
- Go to your inbox at [mailtrap.io](https://mailtrap.io)
- You should see the email with:
  - Subject line (your title)
  - Formatted HTML email
  - Record details
  - "View Record" button

**In Application:**
- Go to Automations → Your Automation
- Scroll to **Execution Logs**
- Check for success/failure messages

---

## 🎨 Email vs In-App Notifications

### In-App Notification (Bell Icon)
- **Action Type:** "Send Notification"
- **Where it goes:** Notification center in the app
- **Use for:** Quick alerts, internal team communication

### Email Notification
- **Action Type:** "Send Email"
- **Where it goes:** User's email inbox
- **Use for:** Important alerts, external communication, reminders

### Both!
You can have **multiple actions** in one automation:
1. Action 1: Send Notification (in-app)
2. Action 2: Send Email

---

## 🧪 Testing Checklist

- [ ] Mailtrap account created
- [ ] SMTP credentials added to .env
- [ ] Config cache cleared
- [ ] Automation created with **"Send Email"** action
- [ ] Automation is **ACTIVE** (toggle checked)
- [ ] Test record created
- [ ] Email received in Mailtrap
- [ ] Email looks good (formatting, content)
- [ ] Template variables replaced correctly
- [ ] "View Record" button works

---

## 🐛 Troubleshooting

### Email not received?

**1. Check automation is active**
```bash
php artisan tinker --execute="echo App\Models\Automation::first()->is_active ? 'ACTIVE' : 'INACTIVE';"
```

**2. Check execution logs**
- Go to your automation detail page
- Scroll to bottom
- Look for errors

**3. Check Laravel logs**
```bash
tail -f storage/logs/laravel.log
```

**4. Test email config**
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```
Check Mailtrap for this test email.

**5. Verify .env loaded**
```bash
php artisan config:clear
php artisan config:cache
```

### Template variables not working?

Make sure you're using the **field ID**, not the label:
- ❌ Wrong: `{{Name}}`
- ✅ Right: `{{field_1767595772891}}`

To find field IDs:
```bash
php artisan tinker --execute="echo json_encode(App\Models\Collection::first()->schema['fields'], JSON_PRETTY_PRINT);"
```

### Email looks broken?

The email template is at:
`resources/views/emails/automation-notification.blade.php`

You can customize it!

---

## 🚀 Production Use

Once tested, switch to a real email provider:

**Option 1: Gmail SMTP**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

**Option 2: SendGrid**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

**Option 3: AWS SES**
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
```

---

## 💡 Pro Tips

1. **Use both notification types** - In-app for quick alerts, email for important ones
2. **Keep email body short** - Include link to view full details
3. **Test with Mailtrap first** - Never spam real users during testing
4. **Use meaningful subjects** - "Task Due Tomorrow" not "Automation Alert"
5. **Include context** - Who, what, when, why in the email body
6. **Add unsubscribe option** - For production (future enhancement)

---

## 📊 Example: Complete Email Automation

**Scenario:** Email team lead when project status changes to "Blocked"

**Automation Setup:**
```
Name: Project Blocked Alert
Trigger: Status Changed
Condition: status = "Blocked"
Action: Send Email
  Target: field:team_lead
  Title: 🚨 Project Blocked: {{project_name}}
  Body: 
    The project "{{project_name}}" has been marked as BLOCKED.
    
    Due Date: {{due_date}}
    Reason: {{blocker_reason}}
    
    Please take immediate action to unblock this project.
```

**Result:** Team lead gets email → Opens in inbox → Clicks "View Record" → Takes action

---

## 🎉 You're Ready!

Your automation system can now:
- ✅ Send in-app notifications
- ✅ Send email notifications  
- ✅ Replace template variables
- ✅ Track execution logs
- ✅ Support multiple targets

Go build some amazing workflows! 🚀
