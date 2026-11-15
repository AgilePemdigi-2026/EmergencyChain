<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmergencyChainNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $url;
    protected string $category;

    public function __construct(
        string $title,
        string $message,
        string $url,
        string $category
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->category = $category;
    }

    /**
     * Notification delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Database notification payload.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'category' => $this->category,
        ];
    }
}
