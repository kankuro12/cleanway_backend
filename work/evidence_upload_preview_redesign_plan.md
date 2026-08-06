# Multi-Image Preview & Role-Based Deletion Specification

> **Intelligence Source**: `UI/UX Pro Max` High-Reliability Media Engine  
> **Primary Goal**: Fix multi-image rendering bugs, display draft previews above the upload button, hide upload buttons when no draft images exist, allow removing individual draft images before uploading, and restrict post-upload evidence deletion to Admin & Supervisor roles.  
> **Target Platform**: Responsive Web & Mobile (Laravel Blade + Axios + Bootstrap 5 + Dispatch Navy System)  

---

## 1. Key Requirements & Architecture

| Requirement | Implementation Strategy |
|---|---|
| **Multi-Image Fast & Reliable Preview** | Use synchronous `URL.createObjectURL(file)` instead of async `FileReader` to prevent race conditions when selecting multiple high-res photos. |
| **Preview Grid Position** | Placed **ABOVE** the upload button container in `evidence-upload.blade.php`. |
| **Upload Button Auto-Hiding** | `.ev-upload` is hidden (`d-none`) by default. Becomes visible only when `pendingFiles[type].length > 0`. |
| **Draft Image Removal** | Each draft preview thumbnail includes a top-right floating `✖` button to drop individual files from `pendingFiles[type]` before upload. |
| **Post-Upload Deletion Permission** | Delete button rendered on uploaded photos **ONLY** when `in_array(auth()->user()->role, ['admin', 'supervisor'], true)`. Controller enforces role check via HTTP 403 response. |

---

## 2. Component Workflow

```
┌────────────────────────────────────────────────────────┐
│ 1. SELECT PHOTOS ([ Choose photos ] / [ Take photo ])  │
├────────────────────────────────────────────────────────┤
│ 2. DRAFT PREVIEWS (ABOVE UPLOAD BUTTON)                │
│    [ 📷 Image 1 (✖) ]  [ 📷 Image 2 (✖) ]  ...         │
│    (Clicking ✖ removes specific draft image)          │
├────────────────────────────────────────────────────────┤
│ 3. UPLOAD BUTTON (Visible ONLY when drafts exist)       │
│    [ ☁️ Upload Photos (2) ]                            │
├────────────────────────────────────────────────────────┤
│ 4. UPLOADED EVIDENCE GALLERY                           │
│    [ 📷 Photo A (✖ Admin/Sup Only) ]                   │
└────────────────────────────────────────────────────────┘
```

---

## 3. Implementation Workflow

1. **Routes**: Register `DELETE /tasks/{task}/evidence/{evidence}` (`tasks.evidence.delete`).
2. **Controller**: Add `deleteEvidence()` method in `TaskController.php` with `admin` / `supervisor` role check.
3. **Blade Template**: Update `resources/views/partials/evidence-upload.blade.php` to position preview grid above upload button, add draft remove badges, and add conditional delete buttons for admin/supervisor on uploaded evidence cards.
4. **JavaScript**: Update `resources/views/partials/evidence-upload-js.blade.php` to handle multi-file `URL.createObjectURL`, draft removal, upload button visibility toggling, and AJAX deletion of uploaded evidence.

