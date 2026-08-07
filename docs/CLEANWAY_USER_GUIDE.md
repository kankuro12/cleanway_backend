# CleanWay Ops — Comprehensive End-User Guide & Operations Manual
**Portal**: [https://cleanway.needtechnosoft.com](https://cleanway.needtechnosoft.com)  
**System Version**: 2.4.0 (Field Operations Edition)  
**Target Roles**: Administrators, Supervisors/Managers, and Field Cleaners  

---

## 1. System Overview & Quick Start

### What is CleanWay Ops?
**CleanWay Ops** is an enterprise-grade field workforce management platform designed specifically for commercial and residential cleaning operations. The platform bridges field cleaners, site managers, and executive administrators with real-time site verification, GPS geofenced attendance, interactive subtask checklists, audited photo evidence, and instant push notifications.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                             CLEANWAY OPS SYSTEM                             │
│                                                                             │
│  OPERATIONS MENU             MANAGEMENT MENU             SYSTEM MENU        │
│  ├─ Dashboard                ├─ Properties               ├─ Staff & Users   │
│  ├─ Tasks                    ├─ Property Categories      ├─ Teams & Branches│
│  ├─ My Tasks (Cleaner)       ├─ Property Tags            ├─ Audit Logs      │
│  ├─ Shift Attendance         ├─ Checklist Templates      └─ App Settings    │
│  └─ Incidents                └─ Quality Approvals                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

### Main Navigation Structure

| Navigation Section | Sub-Menu Item | Visible Roles | Core Function |
| :--- | :--- | :---: | :--- |
| **OPERATIONS** | **Dashboard** | All Roles | Real-time ops metrics, active crew counter, urgent incident alerts, pending approvals. |
| **OPERATIONS** | **Tasks** | Supervisor, Admin | Master task list, calendar view, task scheduling, editing, reassigning, cancelling. |
| **OPERATIONS** | **My Tasks** | Cleaner, All | Personal shift schedule, accepting tasks, checklist execution, before/after photo upload. |
| **OPERATIONS** | **Attendance** | All Roles | Shift clock-in/out console, GPS radius verification, radius override justifications. |
| **OPERATIONS** | **Incidents** | All Roles | Reporting and resolving site access barriers, damaged property, and safety hazards. |
| **MANAGEMENT** | **Properties** | Supervisor, Admin | Building directory, GPS geofence setup, access codes, parking rules, safety instructions. |
| **MANAGEMENT** | **Property Categories** | Supervisor, Admin | Category defaults, standard check-in radii, default checklists, default managers. |
| **MANAGEMENT** | **Property Tags** | Supervisor, Admin | Property tags (*High Security*, *Keycard Required*), badge colors, tag merging. |
| **MANAGEMENT** | **Checklist Templates** | Admin | Interactive subtask builder, mandatory photo rules, pass/fail items. |
| **MANAGEMENT** | **Quality Approvals** | Supervisor, Admin | Quality review drawer, photo evidence inspection, approving or requesting rework. |
| **SYSTEM** | **Staff & Users** | Admin | Staff onboarding, assigning roles (*Admin*, *Supervisor*, *Cleaner*), contact info. |
| **SYSTEM** | **Teams & Branches** | Admin | Operational branch locations and cleaning crew team groupings. |
| **SYSTEM** | **Audit Logs** | Admin | System-wide timestamped activity log and user action history. |
| **SYSTEM** | **App Settings** | Admin | Organization defaults, GPS geofence policies, FCM push notification testing. |

---

## 2. User Roles & System Permissions Matrix

| Operational Action | Field Cleaner (Role 2) | Supervisor (Role 1) | Administrator (Role 0) |
| :--- | :---: | :---: | :---: |
| **Clock-In / Clock-Out with GPS** | ✅ | ✅ | ✅ |
| **View Personal Schedule & Site Briefing** | ✅ | ✅ | ✅ |
| **Execute Checklist & Upload Evidence** | ✅ | ✅ | ✅ |
| **Report On-Site Incidents** | ✅ | ✅ | ✅ |
| **Approve / Reject Completed Tasks** | ❌ | ✅ | ✅ |
| **Approve GPS Out-of-Radius Overrides** | ❌ | ✅ | ✅ |
| **Create & Schedule New Tasks** | ❌ | ✅ | ✅ |
| **Edit / Cancel Scheduled Tasks** | ❌ | ✅ | ✅ |
| **Add / Edit Property Locations** | ❌ | ✅ | ✅ |
| **Manage Property Categories & Tags** | ❌ | ✅ | ✅ |
| **Add / Edit Staff User Accounts** | ❌ | ❌ | ✅ |
| **Build Checklist Templates** | ❌ | ❌ | ✅ |
| **View Audit Logs & Change Settings** | ❌ | ❌ | ✅ |

---

## 3. Operations Module — Task & Schedule Management

### Feature 3.1: Adding a New Cleaning Task

#### When & Why to Use It
Use this feature whenever a new cleaning shift needs to be scheduled for a property—whether for a one-off deep clean, routine office sanitization, or urgent post-event clean-up.

#### Required Prerequisites
1. At least one **Property Location** must exist in the system.
2. At least one **Staff Member (Cleaner)** or **Cleaning Team** must be registered.

#### Exact Data Needed / Input Checklist

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ CREATE NEW TASK FORM                                                        │
│                                                                             │
│ Task Title*                   Target Property Location*                     │
│ [ Daily Evening Office Sanitize ] [ Metro Tower Level 4               ▼ ]   │
│                                                                             │
│ Task Category / Type          Assign Work To*                               │
│ [ Commercial Sanitization  ▼ ] (o) Individual Cleaner   ( ) Cleaning Team   │
│                                                                             │
│ Assigned Cleaner*             Assigned Supervisor                           │
│ [ Sarah Jenkins           ▼ ] [ Mark Davis (Site Mgr)             ▼ ]       │
│                                                                             │
│ Scheduled Start Date & Time*  Scheduled End Deadline*                       │
│ [ 2026-08-07 18:00        📅] [ 2026-08-07 20:00                 📅]       │
│                                                                             │
│ Estimated Duration (Minutes)  Priority Level                                │
│ [ 120                      ]  ( ) Low   (o) Medium   ( ) High  ( ) Critical │
│                                                                             │
│ Attach Checklist Template     Quality Assurance                             │
│ [ Standard Office Checklist▼] [✓] Require Supervisor Quality Approval       │
│                                                                             │
│ Shift Description & Specific Instructions                                   │
│ [ Pay special attention to glass partition doors and sanitize breakroom.   ] │
│                                                                             │
│ [ CANCEL ]                                          [ CREATE & PUBLISH TASK ]│
└─────────────────────────────────────────────────────────────────────────────┘
```

| UI Field Name | Input Type | Required? | Options / Allowed Values | Description & Best Practice |
| :--- | :--- | :---: | :--- | :--- |
| **Task Title** | Text Input | **YES** | Free text (Max 255 chars) | Descriptive headline e.g., *Daily Evening Office Sanitize*. |
| **Target Property Location** | Dropdown | **YES** | List of active properties | Building/site location where work will take place. |
| **Task Category / Type** | Dropdown | Optional | Active Task Types | Classification e.g., *Deep Clean*, *Routine*, *Window Glass*. |
| **Assign Work To** | Selector | **YES** | `Individual Cleaner`<br>`Cleaning Team` | Choose whether assigned to one staff member or an entire crew. |
| **Assigned Cleaner / Team** | Dropdown | **YES** | Active Cleaners or Teams | The specific person or team responsible for cleaning. |
| **Assigned Supervisor** | Dropdown | Optional | Active Supervisors | Manager responsible for inspecting & approving work. |
| **Scheduled Start Date & Time** | DateTime | **YES** | Calendar picker | Date & time cleaner is expected to begin shift. |
| **Scheduled End Deadline** | DateTime | **YES** | Calendar picker | Deadline by which task must be submitted. |
| **Estimated Duration (Minutes)**| Number | Optional | Integer (e.g. `120`) | Target duration in minutes for time tracking. |
| **Priority Level** | Select Pills | **YES** | `Low`, `Medium`, `High`, `Critical` | Task urgency (Default: `Medium`). `Critical` triggers urgent alerts. |
| **Require Supervisor Approval** | Checkbox | Optional | `Checked` / `Unchecked` | When checked, task cannot close until supervisor approves evidence. |
| **Attach Checklist Template** | Dropdown | Optional | Active Templates | Inspection template attached to task for cleaner execution. |
| **Shift Description & Notes** | Textarea | Optional | Text | Specific instructions for this individual shift. |

#### Step-by-Step UI Walkthrough
1. Click **Tasks** on the navigation sidebar.
2. Click the **+ Create Task** button at top-right.
3. In the modal, select the **Target Property Location** and type the **Task Title**.
4. Choose **Assign Work To** (`Individual Cleaner` or `Cleaning Team`) and pick the assignee.
5. Set the **Scheduled Start Date & Time** and **Scheduled End Deadline**.
6. Select an interactive **Checklist Template** (e.g., *Standard Commercial Checklist*).
7. If quality inspection is mandatory, check **Require Supervisor Quality Approval**.
8. Click **Create & Publish Task**.

#### Expected System Outcome
- System generates a unique tracking reference (e.g. `TSK-20260807-0014`).
- Task status is set to `assigned`.
- An instant **FCM Push Notification** is delivered to the assigned cleaner's mobile device.
- Audit log entry `task.created` is recorded.

---

### Feature 3.2: Editing an Existing Task

#### When & Why to Use It
Use this feature to adjust scheduled start times, change assigned staff, update special shift notes, or alter priority before or during shift execution.

#### Rules & Limitations
- **Allowed States**: Tasks in `assigned`, `accepted`, or `in_progress` status can be edited.
- **Locked Fields**: Once a task is `submitted` or `approved`, core assignment fields are locked to preserve audit integrity.

#### Step-by-Step UI Walkthrough
1. Navigate to **Tasks**.
2. Find the task in the list and click the **Actions (...)** button $\rightarrow$ select **Edit Task**.
3. Update desired fields (e.g. change **Assigned Cleaner**, extend **Scheduled End Deadline**, or add **Shift Instructions**).
4. Click **Save Changes**.

#### Expected System Outcome
- Task record is updated.
- If assigned cleaner was changed, the new cleaner receives an FCM push notification (`TASK REASSIGNED`).
- Audit log entry `task.updated` is recorded.

---

### Feature 3.3: Re-Assigning a Task to Another Cleaner or Team

#### Step-by-Step UI Walkthrough
1. Open **Tasks** $\rightarrow$ click the task reference number to view details.
2. Click **Reassign Staff**.
3. Select the new **Cleaner** or **Cleaning Team** from the dropdown.
4. Type an optional reason (e.g., *"Cleaner sick; reassigned to team Alpha"*).
5. Click **Confirm Reassignment**.

---

### Feature 3.4: Cancelling a Task

#### Step-by-Step UI Walkthrough
1. Open **Tasks** $\rightarrow$ click **Actions (...)** $\rightarrow$ select **Cancel Task**.
2. Type the **Cancellation Reason** (e.g., *"Client cancelled shift due to public holiday"*).
3. Click **Confirm Cancellation**.

#### Expected System Outcome
- Task status changes to `cancelled`.
- Task is removed from cleaner's **My Tasks** active shift view.
- Audit log entry `task.cancelled` is recorded.

---

## 4. Field Cleaner Guide — Shift & Task Execution

### Feature 4.1: Shift Clock-In with GPS Radius Verification

#### Purpose
To record official shift start time and verify physical cleaner presence at the property site.

#### Step-by-Step UI Walkthrough
1. Upon arriving at the property, open the app on your smartphone and tap **Attendance** $\rightarrow$ tap **Clock In**.
2. Browser will prompt: *"cleanway.needtechnosoft.com wants to use your device location"* $\rightarrow$ tap **Allow**.
3. The system calculates your GPS distance from property center point:

```
┌──────────────────────────────────────────────────────────┐
│ SHIFT CLOCK-IN CONSOLE                                   │
│ Site: Metro Tower Level 4                                │
│ Permitted Radius: 150 meters                             │
│                                                          │
│ SCENARIO A: WITHIN RADIUS                                │
│ Location: 84 meters away  [ VERIFIED - WITHIN RADIUS ]   │
│ [ CONFIRM CLOCK IN ] ──► Shift started immediately       │
│                                                          │
│ SCENARIO B: OUTSIDE RADIUS                               │
│ Location: 220 meters away [ OUT OF RADIUS WARNING ]     │
│                                                          │
│ Override Reason Justification*                           │
│ [ Parked 200m away in rear alley loading bay.          ] │
│                                                          │
│ [ SUBMIT OVERRIDE REQUEST ] ──► Sent for manager review  │
└──────────────────────────────────────────────────────────┘
```

- **Scenario A (Within Radius)**: Distance (e.g. 84m) $\le$ permitted radius (150m). Tap **Confirm Clock In**. Shift is immediately registered.
- **Scenario B (Outside Radius)**: Distance (e.g. 220m) > permitted radius (150m). The **GPS Override Form** opens:
  - Type justification into **Override Reason Justification** (e.g., *"Loading dock blocked; parked on side street 220m away"*).
  - Tap **Submit Override Request**. Clock-in is saved with `override_status = pending` and your manager receives a push alert.

---

### Feature 4.2: Executing Subtask Checklists & Photo Evidence

#### Step-by-Step UI Walkthrough
1. Tap **My Tasks** $\rightarrow$ tap today's assigned cleaning task.
2. Tap **Accept Task**, then tap **Start Cleaning** (sets task status to `in_progress`).
3. Review site **Access Instructions** (alarm codes, keycard rules), **Parking Notes**, and **Safety PPE Warnings**.
4. Complete checklist subtasks item by item:
   - **Yes / No items**: Tap `Yes` when area cleaning is complete.
   - **Pass / Fail items**: Tap `Pass` after verifying quality. (Tapping `Fail` flags a site issue).
   - **Numeric items**: Type measured reading (e.g. *Water Meter: 1042* or *Chemical PPM: 200*).
   - **Cleaner Notes**: Type any site observations.
5. **Photo Evidence Capture**:
   - For mandatory photo items, tap **Upload Before Photo** (pre-clean photo) or **Upload After Photo** (finished work photo).
   - Capture photo using smartphone camera $\rightarrow$ preview thumbnail image $\rightarrow$ tap **Confirm Photo Upload**.
6. Enter optional **Completion Remarks**.
7. Tap **Submit Task for Supervisor Review**.

#### Expected System Outcome
- Task status changes to `submitted`.
- Your supervisor receives an instant FCM push notification: `TASK SUBMITTED FOR REVIEW: Metro Tower Level 4`.

---

## 5. Supervisor Operations — Quality Approvals & Incidents

### Feature 5.1: Reviewing & Approving Submitted Tasks

#### Purpose
To verify cleaning quality, inspect before/after photo evidence, confirm GPS distance compliance, and formally close tasks.

#### Step-by-Step UI Walkthrough

```
┌──────────────────────────────────────────────────────────┐
│ QUALITY REVIEW DRAWER                                    │
│ Task: TSK-20260807-0014 · Metro Tower Level 4            │
│ Cleaner: Sarah Jenkins | Check-In GPS: 84m (Verified)    │
│                                                          │
│ CHECKLIST RESPONSES                                      │
│ [✓] Sanitise Countertops & Desks          [ PASS ]       │
│ [✓] Restock Washroom Soap                 [  YES ]       │
│ [✓] Water Meter Reading Input             [ 1042 ]       │
│                                                          │
│ PHOTO EVIDENCE GALLERY                                   │
│ ┌───────────────────────┐    ┌───────────────────────┐   │
│ │ [BEFORE PHOTO THUMB]  │    │ [AFTER PHOTO THUMB]   │   │
│ │ Pre-clean Desk Area   │    │ Sanitized Desk Area   │   │
│ └───────────────────────┘    └───────────────────────┘   │
│                                                          │
│ Rework Feedback Notes (Only required if requesting rework)│
│ [                                                      ] │
│                                                          │
│ [ REQUEST REWORK ]               [ APPROVE & COMPLETE ]  │
└──────────────────────────────────────────────────────────┘
```

1. Click **Quality Approvals** on the navigation sidebar.
2. Click a submitted task from the list to open the **Quality Review Drawer**.
3. Review cleaner shift summary: check-in timestamp and verified GPS distance (e.g., `84m`).
4. Review subtask responses (Pass/Fail selections, numeric meter readings).
5. Click **Before Photo** and **After Photo** thumbnails to enlarge and inspect evidence.
6. Take Action:
   - **To Approve**: Click **Approve & Complete Task**. Status changes to `approved`, cleaner receives push notification `TASK APPROVED`, and audit log entry is recorded.
   - **To Request Rework**: Type specific feedback into **Rework Feedback Notes** (e.g. *"Re-mop main lobby entrance glass door"*) and click **Request Rework**. Status changes to `rejected`, cleaner receives push notification `REWORK REQUESTED`, and task returns to cleaner's **My Tasks** view.

---

### Feature 5.2: Approving Out-of-Radius GPS Overrides

#### Step-by-Step UI Walkthrough
1. Click **Attendance** $\rightarrow$ filter by **Pending Overrides**.
2. Click the cleaner's check-in entry to view details:
   - Distance Recorded: e.g. `220 meters` (Permitted: `150 meters`).
   - Cleaner Justification: *"Loading dock blocked; parked on side street 220m away"*.
3. Click **Approve Override** (validates shift check-in) or **Reject Override** (invalidates check-in).

---

### Feature 5.3: Reporting & Handling Site Incidents

#### Cleaner Action (Reporting Incident)
1. On task details screen, tap **Report Site Incident**.
2. Select **Incident Category**: `Access Denied / Key Lockout`, `Property Damage Discovered`, `Safety / Chemical Hazard`, `Missing Supplies / Equipment`, `Other`.
3. Select **Severity Level**: `Low`, `Medium`, `High`, `Critical`.
4. Type detailed **Hazard Description** (e.g. *"Broken window glass discovered in reception area"*).
5. Capture and attach a **Hazard Photo**.
6. Tap **Submit Incident Alert**.

#### Supervisor Action (Resolving Incident)
1. Supervisor receives instant push notification: `URGENT INCIDENT REPORTED: Broken glass at Metro Tower`.
2. Click **Incidents** on navigation sidebar.
3. Open incident details, arrange site repair or access resolution, enter **Resolution Notes**, and click **Mark Incident Resolved**.

---

## 6. Management Module — Properties, Categories & Tags

### Feature 6.1: Adding a New Property Location

#### Purpose
To register a new client site, set its GPS geofence boundary, and provide staff with access security codes and safety warnings.

#### Exact Data Needed / Input Checklist

| UI Form Field Label | Input Type | Required? | User Guidance & Instructions |
| :--- | :--- | :---: | :--- |
| **Property / Building Name** | Text Input | **YES** | Official site title e.g., *Metro Tower Level 4*. |
| **Physical Street Address** | Text Input | **YES** | Full street address. |
| **GPS Latitude** | Number Input | **YES** | Site center latitude coordinate (-90 to 90). |
| **GPS Longitude** | Number Input | **YES** | Site center longitude coordinate (-180 to 180). |
| **Permitted Check-In Radius (Meters)**| Number | Optional | Allowed geofence radius in meters (Default: `150`). |
| **Property Category** | Dropdown | Optional | Category classification e.g. *Commercial Office*, *Medical*. |
| **On-Site Contact Name** | Text Input | Optional | On-site client contact person. |
| **On-Site Contact Phone** | Tel Input | Optional | Direct contact phone number. |
| **Access Instructions & Alarm Codes** | Textarea | Optional | Lockbox codes, keycard rules, alarm codes. |
| **Parking & Loading Bay Instructions** | Textarea | Optional | Cleaner parking spaces, permit rules, loading bay access. |
| **Safety Warnings & PPE Rules** | Textarea | Optional | Gloves, high-vis vests, chemical safety notices. |
| **Special Cleaning Requirements** | Textarea | Optional | Delicate glass care, sensitive equipment requests. |
| **Service Frequency** | Dropdown | Optional | `Daily`, `Weekly`, `Bi-Weekly`, `Custom`. |

#### Step-by-Step UI Walkthrough
1. Click **Properties** on navigation sidebar $\rightarrow$ click **+ Add Property**.
2. Fill in **Property Name**, **Street Address**, **GPS Latitude**, and **GPS Longitude**.
3. Set **Permitted Check-In Radius (Meters)** (e.g. `150`).
4. Type site **Access Instructions** (alarm codes, lockbox combinations) and **Safety Warnings**.
5. Click **Save Property Location**.

---

### Feature 6.2: Property Categories & Tags

- **Property Categories**: Group properties by industry type (*Medical*, *Commercial Office*, *Residential*). Define category defaults for check-in radius, default checklist templates, and default category managers.
- **Property Tags**: Assign color-coded badges to properties (*High Security*, *Keycard Required*, *VIP Client*).
- **Tag Merging**: If duplicate tags exist (e.g. `Bio-Hazard` and `Biohazard`), go to **Property Tags** $\rightarrow$ select **Merge Tags** $\rightarrow$ choose tag to keep and tag to merge. System automatically updates all property references.

---

## 7. System Administration — Onboarding, Checklists & Logs

### Feature 7.1: Adding a New Staff User Account

#### Step-by-Step UI Walkthrough
1. Click **Staff & Users** on navigation sidebar $\rightarrow$ click **+ Add User**.
2. Enter **Full Name**, **Corporate Email**, and **Password**.
3. Select **System Role**:
   - `Field Cleaner`: Access to **Attendance**, **My Tasks**, and **Incidents**.
   - `Supervisor / Manager`: Access to **Tasks**, **Properties**, **Quality Approvals**, **Incidents**.
   - `Administrator`: Full system access.
4. Enter **Mobile Phone Number** and select **Primary Branch** / **Primary Team**.
5. Ensure **Account Status** is toggled to `Active`.
6. Click **Save User Account**.

---

### Feature 7.2: Building Checklist Templates

#### Step-by-Step UI Walkthrough
1. Click **Checklist Templates** on navigation sidebar $\rightarrow$ click **+ Create Template**.
2. Enter **Template Name** (e.g., *Commercial Office Sanitation*).
3. Click **+ Add Section** (e.g., *Reception Area*, *Restrooms*, *Kitchenette*).
4. Inside each section, click **+ Add Subtask Item**:
   - Type **Subtask Label** (e.g. *Sanitize door handles & light switches*).
   - Select **Item Control Type**: `Yes/No`, `Pass/Fail`, `Numeric Measurement`, `Text Notes`, or `Photo Required`.
   - Check **Mandatory Item** if required before submission.
   - Check **Issue Triggering** if failing item should automatically report an incident.
5. Click **Save Checklist Template**.

---

### Feature 7.3: System Audit Logs

1. Click **Audit Logs** under the **SYSTEM** section on the navigation sidebar.
2. Filter audit entries by **User**, **Action Type** (e.g., `task.approved`, `user.created`, `property.updated`), or **Date Range**.
3. Click any audit entry to inspect exact action details, previous values, new values, timestamp, and client IP address.

---

## 8. Troubleshooting & System Diagnostics FAQ

### Q1: Why does worker log show `SendPushNotification ... 3.41ms DONE` and no push is received?
- **Cause**: Server `/var/www/cleanway_backend/.env` has `FIREBASE_ENABLED=false` or `FIREBASE_CREDENTIALS` file path is invalid.
- **Fix**: Update `.env` with `FIREBASE_ENABLED=true` and valid service account JSON path, run `php artisan config:clear`, and restart queue workers (`php artisan queue:restart`).

### Q2: Why is the FCM notification logo missing in Chrome?
- **Cause**: Chrome requires absolute HTTPS URLs (`https://cleanway.needtechnosoft.com/logo.jpg`) in the FCM server payload (`webpush.notification.icon`).
- **Fix**: Resolved in v2.4.0. Press `Ctrl + Shift + R` in Chrome to clear Service Worker cache.

### Q3: Why does shift clock-in say "Outside Permitted Radius"?
- **Cause**: Smartphone GPS distance is greater than the property's **Permitted Check-In Radius (Meters)** (e.g. 150m).
- **Fix**: Move closer to site or fill out the **GPS Override Reason Justification** form to request supervisor approval.

---

## 9. Technical Support & Help Desk Contact

For technical assistance, account recovery, or system onboarding:
- **Operations Help Desk**: `support@needtechnosoft.com`
- **Web App Portal**: [https://cleanway.needtechnosoft.com](https://cleanway.needtechnosoft.com)
- **Documentation Manuals**:
  - Markdown Manual: [`docs/CLEANWAY_USER_GUIDE.md`](file:///e:/laravel%20pojects/nz/app/cleanway_backend/docs/CLEANWAY_USER_GUIDE.md)
  - Interactive HTML Manual: [`docs/cleanway_user_guide.html`](file:///e:/laravel%20pojects/nz/app/cleanway_backend/docs/cleanway_user_guide.html)
