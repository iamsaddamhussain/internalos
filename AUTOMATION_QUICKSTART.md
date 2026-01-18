# 🚀 Quick Start Guide: Automation System

## ⚡ Get Started in 5 Minutes

### 1. Run Migrations

```bash
php artisan migrate
```

This creates 6 new tables for the automation system.

### 2. Setup Cron Job (Production Only)

Add to your server crontab:

```bash
* * * * * cd /var/www/your-project && php artisan schedule:run >> /dev/null 2>&1
```

For local development, skip this step.

### 3. Access Automations

1. Login to your InternalOS workspace
2. Click **"Automations"** in the navigation menu
3. You'll see the automation management page

### 4. Create Your First Automation

**Example: Project Deadline Reminder**

1. Click **"Create Automation"** (visible to Owners/Admins only)
2. Select a collection (e.g., "Projects")
3. Fill in the form:

   **Basic Info:**
   - Name: `Project Deadline Reminder`
   - Description: `Notify assignee 2 days before deadline`
   - Active: ✅ Checked

   **Trigger:**
   - Type: `Date Reached`
   - Date Field: `end_date`
   - Days Offset: `-2` (negative means "before")

   **Condition (Optional):**
   - Field: `status`
   - Operator: `!=` (Not Equals)
   - Value: `Completed`

   **Action:**
   - Type: `Send Notification`
   - Target: `field:assignee`
   - Title: `Project Deadline Approaching`
   - Message: `Your project deadline is in 2 days!`

4. Click **"Create Automation"**

### 5. Test It Manually

Run this command to execute all automations immediately:

```bash
php artisan automations:run
```

Check the automation's detail page to see execution logs!

---

## 🎯 Common Use Cases

### Use Case 1: New Record Alert

**When:** Record is created
**Who:** Notify assignee
**How:**
- Trigger: `Record Created`
- Condition: None
- Action: Notify `field:assignee`

### Use Case 2: Status Change Alert

**When:** Status changes to "Approved"
**Who:** Notify creator
**How:**
- Trigger: `Status Changed`
- Condition: `status = Approved`
- Action: Notify `creator`

### Use Case 3: Multiple Notifications

**When:** 1 day before deadline
**Who:** Notify assignee AND manager
**How:**
- Trigger: `Date Reached` → `end_date` → `-1` day
- Condition: `status != Completed`
- Action: Notify `field:assignee,field:manager`

---

## 📱 Check Notifications

Users can see their notifications:

1. Click the **notification bell** icon (if added to header)
2. Or visit `/notifications`
3. Mark as read or delete

---

## 🔧 Troubleshooting

**Automation not running?**

1. Check if it's **Active** (toggle in the list)
2. Run manually: `php artisan automations:run`
3. Check logs in the automation's detail page
4. Verify cron job is running: `tail -f /var/log/cron.log`

**No notifications appearing?**

1. Check if action target is correct (e.g., `field:assignee` must exist)
2. Verify user field contains valid user ID
3. Check automation logs for errors

**Date trigger not working?**

1. Ensure date field exists in collection
2. Records must have a valid date value
3. Offset is correct: `-2` = 2 days BEFORE, `2` = 2 days AFTER

---

## 🎨 Next Steps

- [ ] Add notification bell to header
- [ ] Create automations for your workflows
- [ ] Monitor execution logs
- [ ] Share with your team!

---

## 📚 Full Documentation

See [AUTOMATION_SYSTEM.md](AUTOMATION_SYSTEM.md) for complete technical documentation.

---

**That's it! You're ready to automate!** 🎉
