<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TaskAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Task $task) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New task assigned: '.$this->task->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-assigned',
        );
    }
}
