# GET /reports/shifts

Returns summarized shift KPIs and paginated shift punch logs with geofence compliance flags.

- **Permission**: `permission:7.1` (Reports view)
- **Throttle**: 120 req/min

## Request Query Parameters

| Field | Type | Required | Description |
|---|---|---|---|
| `from` | string (YYYY-MM-DD) | No | Filter shifts on or after date |
| `to` | string (YYYY-MM-DD) | No | Filter shifts on or before date |
| `branch_id` | int | No | Filter personnel by branch ID |
| `user_id` | int | No | Filter by specific personnel user ID |
| `status` | string | No | Filter by shift status (`scheduled`, `confirmed`, `in_progress`, `completed`, `missed`) |
| `page` | int | No | Page number (default: 1) |
| `per_page` | int | No | Records per page (default: 20) |

## Response 200 OK

```json
{
  "metrics": {
    "total_shifts": 45,
    "completed_shifts": 38,
    "total_worked_hours": 312.5,
    "late_count": 3,
    "early_departure_count": 1,
    "on_time_rate": 93.3,
    "geofence_compliance_rate": 97.8
  },
  "data": [
    {
      "id": 12,
      "user": {
        "id": 5,
        "name": "Jane Smith",
        "branch": "Central Office"
      },
      "date": "2026-08-08",
      "scheduled_start_at": "2026-08-08T08:00:00.000000Z",
      "scheduled_end_at": "2026-08-08T16:00:00.000000Z",
      "status": "completed",
      "property": null,
      "clock_in": {
        "timestamp": "2026-08-08T07:58:12.000000Z",
        "inside_geofence": true,
        "distance_meters": 18.5,
        "is_office_punch": true
      },
      "clock_out": {
        "timestamp": "2026-08-08T16:02:40.000000Z",
        "inside_geofence": true,
        "distance_meters": 22.1,
        "is_office_punch": true
      },
      "metrics": {
        "worked_minutes": 484,
        "break_minutes": 30,
        "overtime_minutes": 4,
        "late": false,
        "early_departure": false,
        "missed": false
      }
    }
  ],
  "pagination": {
    "total": 45,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3
  }
}
```
