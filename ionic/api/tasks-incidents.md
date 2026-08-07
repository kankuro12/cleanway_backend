# POST /api/v1/tasks/{task}/incidents

Raise an incident against a task (permission 4.4 path; incident feature 8.2).

## Request

- **Method**: `POST`
- **Path**: `/api/v1/tasks/{task}/incidents` — `task` = numeric task id
- **Auth**: bearer token
- **Permission**: `4.4`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `category` | string | yes | incident category enum | What happened |
| `severity` | string | yes | `low` \| `medium` \| `high` \| `critical` | Severity |
| `description` | string | yes | ≤5000 | Details |
| `latitude` | float | no | −90..90 | Location |
| `longitude` | float | no | −180..180 | Location |

### Request example

```json
{
  "category": "property_damage",
  "severity": "high",
  "description": "Broken glass panel in lobby found during clean.",
  "latitude": -36.8485,
  "longitude": 174.7624
}
```

## Responses

### 201 — Created

| Field | Type | Description |
|---|---|---|
| `data.id` | int | Incident id |
| `data.uuid` | string | Incident UUID |
| `data.category` | string | Category sent |
| `data.severity` | string | Severity sent |
| `data.status` | string | `open` (initial) |

```json
{
  "data": {
    "id": 301,
    "uuid": "f6a5b4c3-...",
    "category": "property_damage",
    "severity": "high",
    "status": "open"
  }
}
```

### 422 — Validation

```json
{
  "message": "The selected category is invalid.",
  "errors": {
    "category": ["The selected category is invalid."]
  }
}
```

### 403 / 404 / 401 / 429

Standard envelopes.

## Notes

- No photo field exists on this endpoint — incident photos are not supported by the API today (planned backend addition; see `ionic/plan.md`).
- Task status is **not** changed by raising an incident.
