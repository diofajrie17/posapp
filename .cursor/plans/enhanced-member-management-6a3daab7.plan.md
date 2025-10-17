<!-- 6a3daab7-1a4c-480f-9fb9-dab78219ae30 b520ff94-d732-4e90-9aca-890c08ec1e60 -->
# Enhanced Member Management System

## Overview

Transform the basic member system into a comprehensive gym management solution with package management, manual attendance tracking, automated notifications, and reporting.

## Implementation Steps

### 1. Membership Packages Management

**Files to create:**

- `database/migrations/2025_10_17_150000_create_membership_packages_table.php`
- `app/Models/MembershipPackage.php`
- `app/Http/Controllers/MembershipPackageController.php`
- `resources/js/Pages/MembershipPackages/Index.vue`
- `resources/js/Pages/MembershipPackages/Create.vue`
- `resources/js/Pages/MembershipPackages/Edit.vue`

**Package fields:**

- name (e.g., "Monthly", "Annual", "Daily")
- duration_days (30, 365, 1)
- price
- description
- is_active

### 2. Enhanced Member Model & Migration

**Files to modify:**

- `database/migrations/2025_07_30_043203_create_members_table.php` - Add fields
- `app/Models/Member.php` - Add relationships, scopes, and helper methods

**New fields to add:**

- email (nullable)
- gender (enum: male/female)
- membership_package_id (foreign key)
- notes (nullable)

**Member model enhancements:**

- Relationship to MembershipPackage
- Relationship to Attendances
- Scope for active/expired members
- Helper method: `isExpired()`, `daysUntilExpiration()`

### 3. Attendance/Check-in System (Manual Entry)

**Files to create:**

- `database/migrations/2025_10_17_150001_create_attendances_table.php`
- `app/Models/Attendance.php`
- `app/Http/Controllers/AttendanceController.php`
- `resources/js/Pages/Attendance/CheckIn.vue` - Manual entry interface for admin/cashier
- `resources/js/Pages/Attendance/Index.vue` - Attendance history

**Attendance fields:**

- member_id (nullable - null means non-member/daily customer)
- customer_name (for non-members)
- customer_phone (for non-members)
- check_in_time
- check_out_time (nullable)
- type (enum: 'member', 'daily')
- date
- notes

**Features:**

- Admin/Cashier manually records check-ins
- Search members by name or phone number only
- Quick form for daily customer (non-member) registration
- Show member expiration warning when recording check-in
- Optional check-out time recording

### 4. WhatsApp Notifications (Automated)

**Files to modify:**

- `config/whatsapp.php` - Add notifications_enabled flag
- `app/Console/Kernel.php` - Add scheduled task

**Files to create:**

- `app/Console/Commands/SendMembershipExpirationNotifications.php`

**Notification logic:**

- Daily scheduled task (runs every morning)
- Check members expiring in 7 days, 3 days, and 1 day
- Send WhatsApp notification using existing `WhatsAppService`
- Create notification record in `notifications` table
- Toggle in config: `WHATSAPP_NOTIFICATIONS_ENABLED=true/false`

**Message template:**

```
Halo [Name], membership Anda akan berakhir pada [Date]. Silakan perpanjang untuk tetap menikmati fasilitas gym kami. Terima kasih!
```

### 5. Enhanced Member CRUD Pages

**Files to modify:**

- `app/Http/Controllers/MemberController.php` - Enhanced validation and queries
- `resources/js/Pages/Members/Index.vue` - Show package, status, expiration
- `resources/js/Pages/Members/Create.vue` - Complete form with all fields
- `resources/js/Pages/Members/Edit.vue` - Complete form with all fields

**Index page enhancements:**

- Filter by status (active/expired/expiring soon)
- Search by name or phone number
- Show membership package and expiration date
- Status badges (active/expired/expiring soon)

**Form enhancements:**

- Package selection dropdown
- Auto-calculate membership_end based on package duration
- Email and gender fields
- Notes field

### 6. Attendance Reports

**Files to create:**

- `resources/js/Pages/Reports/Attendance.vue`
- `app/Http/Controllers/ReportController.php` (or enhance existing)

**Report features:**

- Date range filter
- Member vs Daily customer breakdown
- Daily/weekly/monthly attendance charts
- Export to CSV/Excel
- Top visiting members
- Peak hours analysis

### 7. Routes & Permissions

**Files to modify:**

- `routes/web.php` - Add new routes
- `database/seeders/RolePermissionSeeder.php` - Add new permissions

**New routes:**

- Membership packages CRUD
- Attendance check-in/check-out
- Attendance reports

**New permissions:**

- `packages.view`, `packages.create`, `packages.update`, `packages.delete`
- `attendance.checkin`, `attendance.view`, `attendance.reports`

### 8. Environment Configuration

**Add to `.env.example` and documentation:**

```
WHATSAPP_NOTIFICATIONS_ENABLED=true
WHATSAPP_EXPIRATION_NOTICE_DAYS=7,3,1
```

## Key Features Summary

1. ✅ Simplified PII management (name, phone, email, gender)
2. ✅ Membership package system with pricing and durations
3. ✅ Manual check-in system for both members and daily customers by admin/cashier
4. ✅ Automated WhatsApp notifications for expiring memberships (toggleable)
5. ✅ Comprehensive attendance reports and analytics
6. ✅ Status tracking (active/expired/expiring soon)
7. ✅ Member search by name and phone number only

## Technical Notes

- Leverage existing `WhatsAppService` and `SendWhatsAppNotification` job
- Use existing `notifications` table for tracking sent messages
- Follow existing design patterns from Products/Purchases modules
- Use existing Button, Card, DataTable, PageHeader components
- Maintain permission-based access control
- Attendance is manually entered by admin/cashier, not self-service

### To-dos

- [ ] Create membership packages system (migration, model, controller, Vue pages)
- [ ] Add PII fields to members table and update Member model with relationships
- [ ] Create attendance tracking system (migration, model, controller, check-in UI)
- [ ] Update member CRUD pages with enhanced forms and package integration
- [ ] Create scheduled command for WhatsApp expiration notifications with toggle
- [ ] Build attendance reports page with date filters and analytics
- [ ] Add routes for new features and update permission seeder