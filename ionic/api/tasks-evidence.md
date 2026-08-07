# POST /api/v1/tasks/{task}/evidence

Upload photo evidence for a task. **Multipart/form-data** — the file is the `evidence` field.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/tasks/{task}/evidence` — `task` = numeric task id
- **Auth**: bearer token
- **Permission**: `4.4`
- **Throttle**: 120 req/min
- **Content-Type**: `multipart/form-data`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `evidence` | file | yes | image (jpeg/png/webp…), **≤ 10240 KB** | The photo |
| `evidence_type` | string | yes | `before` \| `during` \| `after` \| `issue` \| `safety` \| `access_problem` \| `other` | Purpose of the photo |
| `captured_at` | datetime | no | ISO 8601 | When photo was taken |
| `latitude` | float | no | −90..90 | Photo location |
| `longitude` | float | no | −180..180 | Photo location |
| `device_id` | string | no | ≤100 | Device identifier |

## Responses

### 201 — Created

| Field | Type | Description |
|---|---|---|
| `data.id` | int | Evidence id |
| `data.evidence_type` | string | Type sent |
| `data.original_filename` | string | Original file name |
| `data.size_bytes` | int | Stored size |
| `data.processing_status` | string | e.g. `processing` (async processing) |

```json
{
  "data": {
    "id": 903,
    "evidence_type": "after",
    "original_filename": "IMG_0042.jpg",
    "size_bytes": 2457600,
    "processing_status": "processing"
  }
}
```

### 422 — Validation

Oversized file:

```json
{
  "message": "The evidence field must not be greater than 10240 kilobytes.",
  "errors": {
    "evidence": ["The evidence field must not be greater than 10240 kilobytes."]
  }
}
```

Invalid type:

```json
{
  "message": "The selected evidence_type is invalid.",
  "errors": {
    "evidence_type": ["The selected evidence_type is invalid."]
  }
}
```

### 403 / 404 / 401 / 429

Standard envelopes.

## Notes

- Compress/resize client-side before upload — 10 MB cap is strict.
- `processing_status` may be `processing` initially and change later; poll task detail if you need the processed state.
