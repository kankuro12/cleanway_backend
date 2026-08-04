# AI AGENT BUILD SPECIFICATION  
## Cleaning Workforce Task, Attendance, Property, and GPS Operations Platform

**Document type:** Implementation directive for an AI software-development agent  
**Primary delivery order:** Laravel web application first, Ionic mobile application second  
**Backend and web:** Laravel  
**Mobile frontend:** Ionic for iOS and Android  
**Customer portal:** Explicitly excluded  
**Status:** Authoritative build specification

---

# 0. Agent Operating Rules

Treat this document as the authoritative implementation contract.

1. Implement every requirement marked **MUST**.
2. Do not add customer-facing accounts, portals, booking, approval, notifications, or self-service features.
3. Build and stabilize the Laravel web application before beginning Ionic mobile development.
4. Build the Laravel API alongside the web modules so that the same domain services power both web and mobile.
5. Do not duplicate business logic between controllers, web pages, jobs, and API endpoints.
6. Use Laravel policies and permissions for every protected operation.
7. Add automated tests for every critical workflow.
8. Preserve audit history for task, attendance, property, GPS, assignment, evidence, and approval changes.
9. Never hard-delete operational records that are referenced by completed tasks, attendance, approvals, or audit entries.
10. Use database transactions for multi-record operations.
11. Use background jobs for notifications, image processing, geocoding retries, exports, and other slow operations.
12. Do not silently invent missing business requirements. Record unresolved decisions in `docs/decisions-pending.md`.
13. Keep the codebase modular and production-oriented. Do not deliver mock-only screens.
14. Every completed module must include:
    - database migrations;
    - models and relationships;
    - authorization;
    - validation;
    - domain services or actions;
    - web UI;
    - API endpoints where applicable;
    - tests;
    - audit logging;
    - seed or factory data;
    - documentation.

---

# 1. Product Definition

Build an internal workforce operations platform for a cleaning company.

The platform MUST manage:

- personnel;
- role-based access;
- teams and reporting lines;
- properties;
- property categories;
- property tags;
- task types;
- reusable task checklists;
- one-time and recurring tasks;
- assignment of tasks to individual staff or teams;
- shift scheduling;
- attendance;
- GPS-based check-in and check-out;
- task progress;
- photo evidence;
- completion remarks;
- supervisor or administrator approval;
- notifications;
- reports;
- audit history.

The platform MUST be delivered as:

1. a responsive Laravel web application;
2. a versioned Laravel API;
3. an Ionic mobile application for Android and iOS.

The web application MUST be built first.

---

# 2. Non-Negotiable Product Decisions

## 2.1 Internal Use Only

The application is for internal company personnel.

Allowed users:

- Super Administrator;
- Manager or Supervisor;
- Cleaner or Field Staff.

Do not implement:

- customer login;
- customer portal;
- customer booking;
- customer task tracking;
- customer approval;
- customer notifications;
- customer feedback portal;
- customer invoice access;
- customer self-service.

A property may contain optional contact details for operational use, but the contact is not an application user.

## 2.2 Property Fast Entry

For property creation, only the following fields are required:

- `name`;
- `address`.

Every other property field MUST be nullable or optional unless a later workflow explicitly requires it.

This includes:

- category;
- tags;
- contact name;
- contact phone;
- contact email;
- latitude;
- longitude;
- Google Place ID;
- permitted check-in radius;
- access instructions;
- parking instructions;
- safety instructions;
- assigned manager;
- assigned supervisor;
- assigned cleaners;
- assigned team;
- service frequency;
- images;
- documents;
- internal notes.

The property form MUST allow a user to save a property quickly after entering only a name and address.

## 2.3 Google Address and Coordinate Resolution

Use Google Maps Platform for address assistance and coordinate resolution.

Required behavior:

1. The user enters a property name and address.
2. The web interface offers Google Places address autocomplete.
3. When the user selects a result, request Place Details.
4. Save the best available:
   - formatted address;
   - latitude;
   - longitude;
   - Google Place ID;
   - address components where useful.
5. Prefer rooftop or high-accuracy coordinates when Google provides them.
6. Allow the user to adjust the map pin manually.
7. If Google returns no result, still allow saving the property with only name and address.
8. Mark failed or incomplete geocoding for later retry.
9. Provide an administrator action to retry geocoding.
10. Do not block ordinary property creation because Google services are unavailable.

Google does not provide an operational employee check-in radius. Therefore:

- `permitted_check_in_radius_meters` MUST remain nullable at property level;
- the effective radius MUST use this fallback order:
  1. property-specific radius;
  2. property-category default radius;
  3. organization-wide default radius;
  4. system fallback value.

Store the effective radius used on each GPS event so historical validation does not change when configuration changes later.

## 2.4 Web First

Implement in this order:

1. Laravel foundation;
2. Laravel web administration and manager workflows;
3. Laravel API stabilization;
4. Ionic mobile application;
5. offline synchronization and advanced mobile behavior;
6. production hardening and deployment.

Do not begin full Ionic implementation before the web MVP acceptance criteria pass.

## 2.5 Shared Visual Style

The Laravel web application and Ionic mobile application MUST use the same visual language.

Create a shared design-token document containing:

- brand colors;
- semantic colors;
- typography scale;
- spacing scale;
- border radius;
- shadows;
- icons;
- form styles;
- button variants;
- status colors;
- task priority colors;
- attendance state colors;
- approval state colors.

Store the source tokens in a technology-neutral format such as JSON.

Generate or map those tokens to:

- Laravel web CSS variables;
- Ionic CSS variables.

The platforms do not need identical layouts, but they MUST look like the same product.

---

# 3. Required Technology Architecture

## 3.1 Laravel Backend and Web

Use Laravel for:

- server-rendered or hybrid web UI;
- authentication;
- authorization;
- REST API;
- validation;
- domain logic;
- queues;
- events;
- scheduled commands;
- notifications;
- audit logging;
- reports;
- exports;
- storage integration;
- real-time events.

Recommended Laravel web approach:

- Blade with Livewire and Alpine.js, or another Laravel-native interactive approach;
- Tailwind CSS or a consistent component system;
- Laravel session authentication for web;
- Laravel Sanctum for Ionic API authentication.

Do not place core business rules directly inside Blade components or controllers.

## 3.2 Ionic Mobile

Use Ionic with Capacitor for Android and iOS.

The Ionic application MUST support:

- authenticated API access;
- push notifications;
- camera capture;
- geolocation;
- local secure storage;
- offline task data;
- background synchronization where supported;
- calendar display;
- task status changes;
- attendance check-in and check-out;
- photo evidence upload;
- approval actions for authorized supervisors.

Choose one Ionic framework and use it consistently:

- Ionic Angular;
- Ionic React;
- Ionic Vue.

Document the choice in an architecture decision record.

## 3.3 Database

Use a relational database supported by Laravel.

Preferred:

- MySQL or MariaDB for compatibility with the existing Laravel operating environment.

Requirements:

- foreign keys;
- indexes on all search and relationship fields;
- spatially useful indexes where supported;
- soft deletion for configurable master records;
- immutable audit records;
- UTC timestamps in storage;
- organization timezone for presentation.

## 3.4 Infrastructure Services

Use:

- Redis for queues, cache, rate limits, locks, and optional real-time state;
- S3-compatible object storage for task images and documents;
- Laravel queue workers;
- Laravel scheduler;
- Google Maps Platform for Places and geocoding;
- Firebase Cloud Messaging for Android and cross-platform push coordination;
- Apple Push Notification Service through the selected push integration;
- Laravel Reverb, WebSockets, or a compatible real-time transport for live web updates.

---

# 4. Repository and Module Structure

The repository SHOULD contain:

```text
/
├── app/
│   ├── Actions/
│   ├── Domain/
│   │   ├── Attendance/
│   │   ├── Auth/
│   │   ├── Notifications/
│   │   ├── Personnel/
│   │   ├── Properties/
│   │   ├── Reports/
│   │   ├── Scheduling/
│   │   └── Tasks/
│   ├── Events/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/
│   │   │   └── Web/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/
│   ├── Models/
│   ├── Notifications/
│   ├── Policies/
│   ├── Services/
│   └── Support/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/
│   ├── architecture/
│   ├── api/
│   ├── decisions/
│   ├── design-tokens/
│   └── testing/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
└── mobile/
    └── ionic-app/
```

If the Ionic app is maintained in a separate repository, document:

- API version compatibility;
- shared design-token workflow;
- release coordination;
- environment configuration.

---

# 5. Roles, Permissions, and Scope

## 5.1 Super Administrator

The Super Administrator MUST be able to:

- manage all personnel;
- create and manage managers;
- create and manage cleaners;
- manage roles and permissions;
- manage all properties;
- manage property categories and tags;
- manage all task types and checklists;
- create, assign, edit, cancel, reopen, and approve tasks;
- view all shifts and attendance;
- view all GPS events;
- correct attendance through audited workflows;
- view all reports and exports;
- manage notification rules;
- manage system configuration;
- manage organization defaults;
- manage mobile devices and sessions;
- view audit logs.

## 5.2 Manager or Supervisor

A Manager or Supervisor MUST be able to operate only inside the assigned scope.

Scope may be defined by:

- branch;
- team;
- service area;
- assigned properties;
- assigned cleaners;
- explicit permission.

Allowed operations:

- view assigned cleaners;
- view assigned properties;
- create and assign tasks;
- create and manage shifts;
- review attendance;
- review GPS exceptions;
- review evidence;
- approve, reject, reopen, or request correction;
- generate scoped reports;
- manage property assignments if permission is granted;
- manage property categories or tags only if delegated.

## 5.3 Cleaner or Field Staff

A Cleaner MUST only see:

- personal profile;
- personal shifts;
- assigned tasks;
- property information required for assigned work;
- own attendance;
- own notifications;
- own correction requests;
- own submitted evidence;
- task approval outcomes.

A Cleaner MUST be able to:

- acknowledge or accept an assignment;
- view directions;
- check in;
- start work;
- pause or resume when allowed;
- complete checklist items;
- capture images;
- add remarks;
- report an incident;
- check out;
- submit work for approval;
- request attendance correction.

A Cleaner MUST NOT:

- approve own work;
- edit completed audit history;
- view unrelated personnel;
- view unrelated properties;
- change organization configuration;
- alter verified GPS records.

---

# 6. Personnel and Teams

Implement personnel records with nullable optional fields where possible.

Minimum user fields:

- name;
- email or phone-based login identifier;
- password or identity-provider reference;
- role;
- status.

Recommended optional fields:

- employee number;
- phone;
- email;
- profile image;
- emergency contact;
- branch;
- team;
- assigned manager;
- employment type;
- start date;
- end date;
- skills;
- certifications;
- default working hours;
- service areas;
- notification preferences.

User status values:

- invited;
- active;
- inactive;
- suspended;
- on_leave;
- archived.

Implement:

- teams;
- team membership;
- manager-to-cleaner relationships;
- branch membership;
- temporary assignment effective dates;
- personnel activity history.

---

# 7. Property Module

## 7.1 Property Data Contract

Create a `properties` table with fields equivalent to:

```text
id
uuid
name                                  NOT NULL
address                               NOT NULL
formatted_address                     NULL
google_place_id                       NULL
latitude                              NULL
longitude                             NULL
geocode_accuracy                      NULL
geocode_status                        NULL
geocoded_at                           NULL
location_source                       NULL
permitted_check_in_radius_meters      NULL
property_category_id                  NULL
contact_name                          NULL
contact_phone                         NULL
contact_email                         NULL
postal_code                           NULL
access_instructions                   NULL
parking_instructions                  NULL
safety_instructions                   NULL
special_cleaning_requirements         NULL
service_frequency                     NULL
active                                DEFAULT TRUE
internal_notes                        NULL
created_by
updated_by
created_at
updated_at
deleted_at
```

Use suitable decimal precision for latitude and longitude.

Recommended geocode status values:

- pending;
- resolved;
- manually_adjusted;
- failed;
- not_requested.

Recommended location source values:

- google_places;
- google_geocoding;
- manual_pin;
- imported;
- unknown.

## 7.2 Fast Create Workflow

The fast-create property form MUST:

1. display `name`;
2. display `address`;
3. offer Google Places autocomplete;
4. hide optional details behind an expandable section;
5. permit immediate save after name and address;
6. enqueue geocoding if coordinates are not already resolved;
7. allow category, tags, radius, and assignments to be added later.

When creating a task, provide:

- select existing property;
- create and save new property;
- use a one-time unsaved location for this task.

For a one-time task location:

- store the task location snapshot directly on the task;
- do not require a permanent property record;
- allow conversion into a saved property later.

## 7.3 Google Places Workflow

Implement a backend-controlled Google integration.

Do not expose unrestricted Google API keys.

Web flow:

1. request autocomplete suggestions;
2. select a suggestion;
3. retrieve Place Details;
4. populate address and coordinates;
5. show map preview;
6. allow pin adjustment;
7. save the source and place ID.

Server-side fallback:

1. combine property name and address;
2. submit a geocoding request;
3. score returned results;
4. select the best valid result;
5. save result and accuracy;
6. log the response summary;
7. retry failures using queue backoff.

Do not repeatedly geocode unchanged addresses.

Trigger re-geocoding when:

- address changes materially;
- user requests retry;
- coordinates are missing;
- geocode status is failed;
- import data lacks coordinates.

## 7.4 Categories

Create dynamic property categories.

Category fields:

- name;
- slug;
- description;
- default check-in radius;
- default task type;
- default checklist;
- default manager;
- default team;
- default safety instructions;
- active;
- sort order.

A property may have zero or one primary category.

Category assignment MUST be editable later.

Changing category MUST NOT rewrite historical task snapshots.

## 7.5 Tags

Create reusable many-to-many property tags.

Tag fields:

- name;
- slug;
- description;
- active;
- optional display color;
- sort order.

Requirements:

- assign zero or more tags to a property;
- add tags during property creation;
- add tags later;
- remove tags later;
- bulk assign tags;
- bulk remove tags;
- merge duplicate tags;
- archive tags;
- search by one or more tags;
- filter by category and tags together.

Tag changes MUST NOT modify historical task records.

## 7.6 Property Assignment

A property may be assigned later to:

- manager;
- supervisor;
- cleaner;
- multiple cleaners;
- team;
- branch;
- service area.

Create a property assignment history table with:

- property ID;
- assignable type;
- assignable ID;
- assignment role;
- start date;
- end date;
- primary flag;
- assigned by;
- reason;
- created timestamp.

Support:

- permanent assignments;
- temporary assignments;
- date-limited assignments;
- primary and backup assignments;
- preferred cleaner assignments.

Do not require assignments during property creation.

## 7.7 Property Search

Implement indexed search and combined filtering.

Search fields:

- property name;
- address;
- formatted address;
- Google Place ID;
- contact name;
- contact phone;
- category;
- tags;
- assigned manager;
- assigned cleaner;
- team;
- branch;
- active status;
- last service date;
- next service date;
- GPS availability;
- geocode status.

Provide:

- autocomplete;
- recent properties;
- saved filters;
- map view;
- nearby-property search;
- unassigned-property filter;
- missing-coordinate filter;
- bulk selection;
- export.

---

# 8. Task Type and Checklist Module

Task types MUST be dynamic and configurable without code changes.

Task type fields:

- name;
- slug;
- description;
- default estimated duration;
- default priority;
- default instructions;
- default checklist;
- before-photo requirement;
- after-photo requirement;
- minimum photo count;
- approval required;
- allowed assignee types;
- active;
- sort order.

Checklist templates MUST support:

- sections;
- ordered items;
- required items;
- yes/no items;
- pass/fail items;
- text input;
- numeric input;
- photo-required item;
- issue-triggering item;
- completion rules.

When a task is created, snapshot the task type and checklist configuration so later template changes do not alter existing tasks.

---

# 9. Task Module

## 9.1 Task Fields

Create tasks with fields equivalent to:

```text
id
uuid
reference_number
title
description
task_type_id
property_id                       NULL for one-time location
property_name_snapshot
address_snapshot
latitude_snapshot
longitude_snapshot
check_in_radius_snapshot
assigned_manager_id               NULL
scheduled_start_at
scheduled_end_at
estimated_duration_minutes        NULL
priority
status
recurrence_rule                   NULL
approval_required
accepted_at                       NULL
started_at                        NULL
completed_at                      NULL
submitted_at                      NULL
approved_at                       NULL
rejected_at                       NULL
cancelled_at                      NULL
created_by
updated_by
created_at
updated_at
deleted_at
```

A task may be assigned to:

- one cleaner;
- multiple cleaners;
- one team;
- a supervisor;
- another authorized staff type.

Use a dedicated task assignment table.

## 9.2 Task Status State Machine

Implement the following state flow:

```text
draft
  -> scheduled
  -> assigned
  -> accepted
  -> in_progress
  -> completed
  -> submitted_for_approval
  -> approved
```

Additional valid states:

```text
unassigned
declined
paused
delayed
unable_to_access
correction_requested
rejected
reopened
cancelled
```

Do not permit arbitrary status updates.

Implement explicit transition rules.

Every transition MUST create a task status history record with:

- previous status;
- new status;
- user;
- timestamp;
- remarks;
- device;
- latitude and longitude when relevant;
- source platform.

## 9.3 Task Creation

Task creation MUST support:

- select task type;
- select existing property;
- quick-create property;
- one-time location;
- description;
- reference images;
- date and time;
- expected duration;
- individual or team assignment;
- recurrence;
- notification rules;
- checklist overrides;
- photo requirements;
- approval requirement.

Validate:

- scheduling conflicts;
- employee availability;
- overlapping assignments;
- leave;
- required skills;
- travel-time warnings when enabled.

Warnings may be overridden by authorized users with a recorded reason.

## 9.4 Recurring Tasks

Support recurring task templates.

Store:

- recurrence rule;
- start date;
- optional end date;
- time;
- assigned property;
- default assignee;
- task type;
- checklist;
- notification timing.

Generate task instances ahead of time using a scheduled command.

Do not modify completed instances when the recurring template changes.

---

# 10. Calendar and Scheduling

Web calendar views:

- daily;
- weekly;
- monthly;
- personnel;
- team;
- property;
- manager.

Required features:

- drag-and-drop rescheduling;
- assignment from calendar;
- conflict warnings;
- filter by status, property, category, tags, employee, and task type;
- recurring task display;
- unscheduled task queue;
- leave and absence overlay;
- shift overlay.

Mobile calendar:

- personal day view;
- personal week view;
- upcoming task list;
- open task details;
- add task to device calendar where authorized.

External calendar synchronization is optional and must not delay the core implementation.

---

# 11. Shift and Attendance Module

## 11.1 Shifts

Shift fields:

- user;
- date;
- scheduled start;
- scheduled end;
- property or service area, nullable;
- manager;
- status;
- notes.

Shift states:

- scheduled;
- confirmed;
- in_progress;
- completed;
- missed;
- cancelled;
- absent.

## 11.2 Attendance Events

Attendance event types:

- clock_in;
- break_start;
- break_end;
- clock_out;
- manual_correction;
- supervisor_override.

Record:

- user;
- shift;
- event type;
- server timestamp;
- device timestamp;
- latitude;
- longitude;
- GPS accuracy;
- effective radius;
- property;
- distance from property;
- inside geofence;
- device ID;
- source;
- offline flag;
- synced timestamp;
- remarks.

## 11.3 Attendance Rules

Implement:

- late arrival detection;
- early departure detection;
- missed shift detection;
- overtime;
- total work duration;
- break duration;
- attendance correction request;
- supervisor approval or rejection;
- immutable original event preservation.

Manual corrections MUST create adjustment records rather than rewriting the original verified event.

---

# 12. GPS, Geofence, and Location Verification

## 12.1 Check-In Validation

For GPS check-in:

1. request current device location;
2. capture accuracy;
3. reject or warn when accuracy exceeds configured threshold;
4. calculate distance from task or property coordinates;
5. resolve effective permitted radius;
6. store distance and radius;
7. mark event inside or outside geofence;
8. follow organization policy:
   - accept;
   - accept with exception;
   - require manager override;
   - reject.

Use a reliable geodesic distance calculation.

## 12.2 Missing Coordinates

When a property lacks coordinates:

- do not crash;
- display that GPS verification is unavailable;
- attempt queued geocoding;
- allow manager-approved exception if policy permits;
- record the exception;
- prevent false claims that the location was verified.

## 12.3 Location Tracking Scope

Default behavior:

- record discrete GPS events at check-in, task start, task completion, and check-out;
- optionally record periodic location only during an active shift or task when organization policy enables it;
- never collect background location outside active work windows;
- display clear consent and permission state;
- retain only the required location history.

## 12.4 Spoofing and Integrity Signals

Record available integrity indicators:

- mock-location indicator where available;
- rooted or jailbroken device risk where available;
- impossible travel;
- device time difference;
- low GPS accuracy;
- repeated out-of-radius attempts;
- offline submission delay;
- manual pin usage.

Do not automatically accuse a user. Create a reviewable exception.

---

# 13. Task Evidence and Approval

## 13.1 Evidence Capture

Evidence types:

- before;
- during;
- after;
- issue;
- safety;
- access_problem;
- other.

For each image store:

- task;
- uploader;
- evidence type;
- file path;
- original filename;
- MIME type;
- size;
- width;
- height;
- captured timestamp;
- uploaded timestamp;
- latitude;
- longitude;
- device ID;
- source;
- checksum;
- processing status.

Requirements:

- camera capture in Ionic;
- gallery upload only when policy permits;
- image compression;
- thumbnail generation;
- secure storage;
- signed or controlled URLs;
- optional timestamp and task-reference watermark;
- offline pending upload.

## 13.2 Completion

To complete a task, enforce configured requirements:

- all required checklist items answered;
- minimum before photos;
- minimum after photos;
- required remarks;
- GPS check-out when required;
- incident resolution or acknowledgment when required.

Completion flow:

```text
in_progress
-> completed
-> submitted_for_approval
```

## 13.3 Approval

Authorized Manager, Supervisor, or Super Administrator may:

- approve;
- reject;
- request correction;
- reopen;
- add internal remarks;
- add a quality score.

Approval records MUST contain:

- task;
- action;
- reviewer;
- timestamp;
- remarks;
- reason code;
- requested corrections;
- previous approval state.

A cleaner cannot approve their own task.

---

# 14. Notifications

Support:

- in-app notifications;
- web real-time notifications;
- mobile push notifications;
- email notifications;
- optional SMS later.

Events:

- task assigned;
- task reassigned;
- task accepted;
- task declined;
- shift upcoming;
- task upcoming;
- schedule changed;
- task cancelled;
- check-in missed;
- late arrival;
- task overdue;
- evidence submitted;
- correction requested;
- task approved;
- task rejected;
- incident raised;
- attendance correction decision.

Implement:

- notification preference by user;
- mandatory operational notifications;
- quiet hours;
- retry;
- delivery log;
- read state;
- idempotency;
- notification templates.

Use queued delivery.

---

# 15. Incident and Safety Module

Incident categories:

- property access problem;
- missing key;
- incorrect access code;
- damaged equipment;
- property damage;
- safety hazard;
- missing supplies;
- aggressive or unsafe situation;
- task cannot be completed;
- other.

Incident fields:

- task;
- property;
- reporter;
- category;
- severity;
- description;
- images;
- GPS;
- status;
- assigned reviewer;
- resolution;
- timestamps.

Incident states:

- open;
- acknowledged;
- investigating;
- resolved;
- closed.

---

# 16. Search, Dashboards, and Reports

## 16.1 Super Administrator Dashboard

Display:

- active personnel;
- managers;
- cleaners;
- tasks today;
- active tasks;
- overdue tasks;
- pending approvals;
- late attendance;
- absences;
- GPS exceptions;
- open incidents;
- properties without coordinates;
- properties without assignments;
- geocoding failures.

## 16.2 Manager Dashboard

Display only assigned scope:

- team attendance;
- current active tasks;
- tasks awaiting approval;
- late or absent cleaners;
- overdue tasks;
- open incidents;
- location exceptions;
- upcoming property work.

## 16.3 Cleaner Dashboard

Display:

- current shift;
- current task;
- next task;
- check-in or check-out action;
- pending uploads;
- correction requests;
- notifications;
- recent attendance.

## 16.4 Reports

Implement reports for:

- attendance;
- work hours;
- overtime;
- missed shifts;
- task completion;
- task duration;
- task approval;
- rejected or reopened tasks;
- property service history;
- task type performance;
- cleaner performance;
- manager performance;
- GPS exceptions;
- category distribution;
- tag distribution;
- properties without assignments;
- properties without coordinates;
- incidents.

Filters:

- date range;
- employee;
- manager;
- team;
- branch;
- property;
- category;
- tags;
- task type;
- status;
- priority.

Exports:

- CSV;
- Excel;
- PDF where required.

Generate large exports in background jobs.

---

# 17. Laravel Web UI Requirements

Build the web application before Ionic.

Required web modules:

1. authentication;
2. dashboard;
3. personnel;
4. managers and cleaners;
5. teams;
6. properties;
7. property categories;
8. property tags;
9. property assignments;
10. task types;
11. checklist templates;
12. task creation;
13. task list;
14. task detail;
15. calendar;
16. shifts;
17. attendance;
18. approval queue;
19. GPS exceptions;
20. incidents;
21. notifications;
22. reports;
23. audit log;
24. settings.

## 17.1 Property Web Form

Default visible fields:

- name;
- address autocomplete;
- map preview when available;
- save button.

Collapsed optional section:

- category;
- tags;
- contact details;
- check-in radius;
- access instructions;
- parking instructions;
- safety instructions;
- manager;
- team;
- cleaners;
- notes.

The user must be able to save without expanding the optional section.

## 17.2 Task Web Form

Use a step-based or clearly sectioned form:

1. task type;
2. property or one-time location;
3. schedule;
4. assignee;
5. instructions and checklist;
6. evidence rules;
7. review and create.

Autosave drafts where practical.

---

# 18. Laravel API Requirements

Use versioned routes:

```text
/api/v1
```

Use Laravel API Resources for responses.

Use consistent envelopes:

```json
{
  "data": {},
  "meta": {},
  "links": {}
}
```

Validation errors:

```json
{
  "message": "Validation failed.",
  "errors": {
    "field": ["Error message."]
  }
}
```

Core endpoint groups:

```text
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/me
GET    /api/v1/me/shifts
GET    /api/v1/me/tasks
GET    /api/v1/tasks/{task}
POST   /api/v1/tasks/{task}/accept
POST   /api/v1/tasks/{task}/decline
POST   /api/v1/tasks/{task}/check-in
POST   /api/v1/tasks/{task}/start
POST   /api/v1/tasks/{task}/pause
POST   /api/v1/tasks/{task}/resume
POST   /api/v1/tasks/{task}/complete
POST   /api/v1/tasks/{task}/submit
POST   /api/v1/tasks/{task}/evidence
POST   /api/v1/tasks/{task}/incidents
POST   /api/v1/tasks/{task}/check-out
GET    /api/v1/notifications
POST   /api/v1/notifications/{notification}/read
POST   /api/v1/attendance/clock-in
POST   /api/v1/attendance/break-start
POST   /api/v1/attendance/break-end
POST   /api/v1/attendance/clock-out
POST   /api/v1/attendance/correction-requests
GET    /api/v1/properties/{property}
GET    /api/v1/property-search
POST   /api/v1/device/register
POST   /api/v1/sync
```

Supervisor endpoints:

```text
GET    /api/v1/supervisor/approval-queue
POST   /api/v1/tasks/{task}/approve
POST   /api/v1/tasks/{task}/reject
POST   /api/v1/tasks/{task}/request-correction
POST   /api/v1/tasks/{task}/reopen
GET    /api/v1/supervisor/team-status
```

API requirements:

- Sanctum token authentication;
- token revocation;
- device registration;
- rate limiting;
- policy authorization;
- request idempotency for offline submissions;
- pagination;
- filtering;
- sparse mobile payloads where useful;
- API version documentation;
- OpenAPI specification.

---

# 19. Ionic Mobile Requirements

Begin after Laravel web MVP is accepted.

Required screens:

- login;
- device registration;
- dashboard;
- personal task list;
- task detail;
- property directions;
- check-in;
- active task;
- checklist;
- camera evidence;
- remarks;
- incident report;
- completion;
- check-out;
- submission status;
- notifications;
- personal attendance;
- correction request;
- supervisor approval queue for authorized users;
- settings and permissions.

## 19.1 Mobile Permission Handling

Handle:

- location allowed;
- location denied;
- precise location disabled;
- camera allowed;
- camera denied;
- notification allowed;
- notification denied;
- background location disabled;
- offline mode.

Never show a false successful GPS verification when permission is unavailable.

## 19.2 Offline Mode

Cache:

- authenticated user;
- assigned shifts;
- assigned tasks;
- required property details;
- task checklist;
- task type instructions;
- notification summary.

Queue offline actions:

- check-in attempt;
- task start;
- checklist updates;
- remarks;
- image capture;
- incident report;
- completion;
- check-out;
- submission.

Each queued action MUST include:

- client-generated UUID;
- device timestamp;
- local sequence;
- task ID;
- user ID;
- device ID;
- payload checksum;
- synchronization state.

Server synchronization MUST be idempotent.

Conflict rules:

- server-approved or manager-edited task state has priority;
- completed evidence is never silently discarded;
- conflicting actions are surfaced for review;
- duplicate uploads are detected by UUID or checksum.

---

# 20. Shared Visual Design System

Create:

```text
docs/design-tokens/tokens.json
```

Include:

```json
{
  "color": {
    "primary": "",
    "secondary": "",
    "success": "",
    "warning": "",
    "danger": "",
    "info": "",
    "surface": "",
    "background": "",
    "text": "",
    "muted": ""
  },
  "spacing": {},
  "radius": {},
  "shadow": {},
  "typography": {},
  "status": {
    "task": {},
    "attendance": {},
    "approval": {}
  }
}
```

Requirements:

- map tokens to Laravel CSS;
- map tokens to Ionic CSS variables;
- use the same icon family where licensing permits;
- maintain consistent status terminology;
- maintain consistent color meaning;
- use responsive layouts instead of copying desktop screens directly into mobile.

---

# 21. Audit, Security, and Privacy

## 21.1 Audit

Audit at minimum:

- login and logout;
- user changes;
- permission changes;
- property creation and update;
- category and tag changes;
- property assignments;
- task creation;
- task assignment;
- status changes;
- schedule changes;
- attendance events;
- attendance corrections;
- GPS exceptions;
- evidence upload;
- approval actions;
- incident changes;
- exports;
- configuration changes.

Audit record fields:

- actor;
- action;
- entity type;
- entity ID;
- before values;
- after values;
- IP address;
- device;
- source;
- request ID;
- timestamp.

## 21.2 Security

Implement:

- password hashing;
- secure reset flow;
- optional multi-factor authentication;
- session management;
- token revocation;
- role and policy checks;
- rate limiting;
- CSRF protection for web;
- secure CORS configuration;
- encrypted transport;
- restricted file access;
- signed image URLs;
- upload validation;
- malware scanning where infrastructure supports it;
- secrets in environment configuration;
- backup and restore testing.

## 21.3 Privacy

Requirements:

- collect location only for defined work purposes;
- provide permission explanation;
- do not collect unnecessary background location;
- restrict property access codes;
- define data retention;
- support authorized deletion or anonymization where legally required;
- avoid exposing full GPS history to unauthorized roles.

---

# 22. Core Database Tables

At minimum, implement:

```text
users
roles
permissions
role_user or model_has_roles
branches
teams
team_members
manager_assignments

properties
property_categories
property_tags
property_property_tag
property_assignments
property_documents
property_images
property_geocode_attempts

task_types
checklist_templates
checklist_sections
checklist_items
tasks
task_assignments
task_checklist_snapshots
task_checklist_responses
task_status_histories
task_recurrences
task_evidence
task_approvals

shifts
attendance_events
attendance_correction_requests
gps_exceptions

incidents
incident_evidence
notifications
notification_deliveries
device_registrations
offline_sync_batches
offline_sync_items

audit_logs
system_settings
organization_settings
saved_filters
export_jobs
```

Add indexes for:

- property name;
- property address;
- Google Place ID;
- category;
- tag joins;
- task status;
- scheduled dates;
- assignee;
- property;
- shift date;
- attendance user and date;
- approval status;
- notification recipient;
- audit entity and timestamp.

---

# 23. Domain Services and Events

Recommended domain actions:

```text
CreateProperty
ResolvePropertyCoordinates
RetryPropertyGeocode
AssignPropertyPersonnel
CreateTask
AssignTask
RescheduleTask
TransitionTaskStatus
GenerateRecurringTasks
CheckInToTask
CheckOutFromTask
RecordAttendanceEvent
SubmitAttendanceCorrection
UploadTaskEvidence
CompleteTask
SubmitTaskForApproval
ApproveTask
RejectTask
RequestTaskCorrection
ReopenTask
RaiseIncident
ResolveIncident
SyncOfflineActions
```

Recommended events:

```text
PropertyCreated
PropertyCoordinatesResolved
PropertyAssignmentChanged
TaskCreated
TaskAssigned
TaskAccepted
TaskStarted
TaskCompleted
TaskSubmittedForApproval
TaskApproved
TaskRejected
TaskCorrectionRequested
ShiftStarted
AttendanceExceptionDetected
GpsExceptionDetected
IncidentRaised
```

Use listeners and jobs for:

- notifications;
- real-time updates;
- report projections;
- image processing;
- search indexing;
- audit enrichment.

---

# 24. Testing Requirements

## 24.1 Unit Tests

Test:

- status transitions;
- effective radius fallback;
- GPS distance calculation;
- permission rules;
- recurrence generation;
- attendance duration;
- overtime;
- approval rules;
- offline idempotency;
- geocode result selection.

## 24.2 Feature Tests

Test:

- role-based access;
- property fast creation with only name and address;
- property creation when Google is unavailable;
- address autocomplete result storage;
- manual pin adjustment;
- category and tag assignment later;
- property search;
- task creation;
- assignment;
- calendar conflict warning;
- attendance check-in;
- out-of-radius exception;
- task completion;
- photo requirements;
- approval;
- correction request;
- reports;
- exports.

## 24.3 Mobile Tests

Test:

- login;
- task synchronization;
- offline task completion;
- photo queue;
- permission denial;
- low-accuracy GPS;
- duplicate sync prevention;
- push notification navigation;
- Android and iOS parity.

## 24.4 End-to-End Critical Scenario

Automate this scenario:

1. Super Administrator creates a Manager.
2. Super Administrator creates a Cleaner.
3. Manager creates a property using only name and address.
4. Google coordinates resolve automatically.
5. Manager later assigns a category and tags.
6. Manager later assigns the property to the Cleaner.
7. Manager creates and schedules a task.
8. Cleaner receives the task.
9. Cleaner checks in within the radius.
10. Cleaner starts the task.
11. Cleaner completes checklist items.
12. Cleaner captures before and after images.
13. Cleaner adds remarks.
14. Cleaner checks out.
15. Cleaner submits for approval.
16. Manager reviews and approves.
17. Reports and audit logs reflect the full workflow.

Also test the same flow when:

- Google geocoding fails;
- the device is offline;
- GPS is outside the radius;
- a required image is missing;
- the Manager requests correction.

---

# 25. Web-First Implementation Sequence

## Phase 0: Foundation

Implement:

- repository setup;
- environment configuration;
- authentication;
- roles and permissions;
- audit framework;
- queue;
- scheduler;
- storage;
- testing framework;
- design tokens;
- coding standards;
- CI pipeline.

Exit criteria:

- login works;
- roles are enforced;
- queues run;
- storage works;
- tests run in CI;
- audit base is active.

## Phase 1: Web Property and Personnel Core

Implement:

- personnel;
- teams;
- managers and cleaners;
- property fast create;
- Google Places;
- geocoding;
- property map;
- categories;
- tags;
- property assignment;
- property search.

Exit criteria:

- property saves with only name and address;
- coordinates populate when Google succeeds;
- property still saves when Google fails;
- categories and tags can be added later;
- assignments can be added later;
- search and filters work.

## Phase 2: Web Tasks and Scheduling

Implement:

- task types;
- checklists;
- task creation;
- assignments;
- calendar;
- recurrence;
- task state machine;
- notifications.

Exit criteria:

- manager can schedule and assign tasks;
- conflicts are detected;
- task history is auditable;
- notifications are queued.

## Phase 3: Web Attendance, GPS, and Approval

Implement:

- shifts;
- attendance;
- GPS validation;
- radius fallback;
- evidence;
- completion;
- approval queue;
- rejection;
- correction;
- incidents.

Exit criteria:

- complete end-to-end workflow passes;
- invalid state changes are rejected;
- GPS exceptions are reviewable;
- evidence requirements are enforced.

## Phase 4: Web Reports and Hardening

Implement:

- dashboards;
- reports;
- exports;
- audit viewer;
- settings;
- performance optimization;
- security review;
- backup verification.

Exit criteria:

- web MVP accepted;
- API contracts stable;
- OpenAPI documentation complete.

## Phase 5: Ionic Mobile

Implement:

- authentication;
- task list;
- task execution;
- GPS;
- camera;
- attendance;
- evidence;
- notifications;
- supervisor approvals;
- shared style tokens.

Exit criteria:

- Android and iOS critical flow passes;
- visual design matches web product;
- permissions are handled safely.

## Phase 6: Offline and Production Readiness

Implement:

- offline cache;
- offline action queue;
- conflict handling;
- background synchronization;
- push hardening;
- monitoring;
- release builds;
- deployment documentation.

Exit criteria:

- offline workflow passes;
- duplicate submissions are prevented;
- release builds are signed and deployable.

---

# 26. Definition of Done

A feature is complete only when:

- migration exists;
- model relationships exist;
- authorization policy exists;
- validation exists;
- service or action exists;
- web UI exists where required;
- API exists where required;
- tests pass;
- audit entry exists;
- errors are handled;
- loading and empty states exist;
- mobile impact is documented;
- API documentation is updated;
- no unresolved high-severity security issue remains.

---

# 27. Explicit Out of Scope

Do not build in the initial system:

- customer portal;
- customer mobile application;
- customer booking;
- customer approval;
- customer payment;
- invoicing;
- payroll processing;
- route optimization;
- equipment inventory;
- supply stock management;
- public marketplace;
- external contractor marketplace;
- AI image inspection;
- facial recognition attendance.

These may be separate future modules and must not block the core system.

---

# 28. Required Agent Deliverables

The AI development agent MUST produce:

1. source code;
2. migrations;
3. seeders and factories;
4. automated tests;
5. OpenAPI documentation;
6. role-permission matrix;
7. database relationship diagram;
8. task status transition diagram;
9. attendance and GPS validation documentation;
10. Google Maps setup guide;
11. environment variable template;
12. queue and scheduler deployment guide;
13. object storage setup guide;
14. web deployment guide;
15. Ionic Android build guide;
16. Ionic iOS build guide;
17. backup and restore guide;
18. production readiness checklist;
19. unresolved decision log;
20. change log.

---

# 29. Final Build Directive

Build a production-oriented internal cleaning workforce platform.

The first usable release MUST be the Laravel web application.

The Laravel codebase MUST contain both:

- the web administration and operational interface;
- the versioned API consumed by Ionic.

The Ionic application MUST be implemented after the web workflows and API contracts are stable.

Property entry MUST remain fast:

- only name and address are mandatory;
- Google must automatically resolve the best available coordinates;
- all additional property details must remain optional;
- permitted check-in radius must be nullable and resolved through configuration fallback;
- categories, tags, and personnel assignments must be assignable later;
- property search must remain efficient as data volume grows.

Do not implement any customer-facing feature.
