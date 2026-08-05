<?php

namespace App\Mail;

use Illuminate\Support\Str;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Dev-only transport: writes every sent message to a folder as a timestamped
 * HTML file instead of dumping it into the shared log file.
 */
class FileMailTransport extends AbstractTransport
{
    public function __construct(private readonly string $path)
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'file';
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();
        $body = $original->getHtmlBody() ?: $message->toString();

        if (! is_dir($this->path)) {
            mkdir($this->path, 0775, true);
        }

        $filename = now()->format('Y-m-d_H-i-s_v').'_'.Str::lower(Str::random(6)).'.html';

        file_put_contents($this->path.'/'.$filename, $body);
    }
}
