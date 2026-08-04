<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New task assigned</title>
</head>
<body style="margin: 0; padding: 24px; background: #f2f4f7; font-family: Arial, Helvetica, sans-serif; color: #1d232b;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e6ea; border-radius: 6px; overflow: hidden;">
        <div style="background: #0f2a43; color: #ffffff; padding: 16px 24px; font-size: 15px; letter-spacing: .08em; text-transform: uppercase;">
            CleanWay Ops
        </div>
        <div style="padding: 24px;">
            <h1 style="margin: 0 0 16px; font-size: 20px; color: #111827;">New task assigned</h1>
            <p style="margin: 0 0 16px; font-size: 14px; line-height: 1.6;">
                You have been assigned to a new task.
            </p>

            <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">
                <tr>
                    <td style="padding: 8px 0; color: #6b7280; width: 40%;">Task</td>
                    <td style="padding: 8px 0;"><strong>{{ $task->title }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280;">Reference</td>
                    <td style="padding: 8px 0; font-family: Consolas, monospace;">{{ $task->reference_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280;">Location</td>
                    <td style="padding: 8px 0;">{{ $task->property_name_snapshot ?? 'One-off location' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280;">Address</td>
                    <td style="padding: 8px 0;">{{ $task->address_snapshot ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280;">Scheduled</td>
                    <td style="padding: 8px 0;">{{ $task->scheduled_start_at?->format('D, j M Y H:i') ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280;">Priority</td>
                    <td style="padding: 8px 0;">{{ ucfirst($task->priority) }}</td>
                </tr>
            </table>

            @if($task->description)
                <p style="margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: #374151;">{{ $task->description }}</p>
            @endif

            @if($task->subtasks->isNotEmpty())
                <p style="margin: 0 0 8px; font-size: 13px; font-weight: bold; color: #111827;">Sub tasks</p>
                <ul style="margin: 0 0 20px; padding-left: 18px; font-size: 14px; color: #374151;">
                    @foreach ($task->subtasks as $subtask)
                        <li style="margin-bottom: 4px;">{{ $subtask->title }}</li>
                    @endforeach
                </ul>
            @endif

            <p style="margin: 0; font-size: 13px; color: #6b7280;">
                Open the task in the operations console for details, checklist and photo requirements.
            </p>
        </div>
    </div>
</body>
</html>
