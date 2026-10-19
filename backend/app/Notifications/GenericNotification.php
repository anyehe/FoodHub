<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class GenericNotification extends Notification {
 use Queueable;
 public function __construct(public string $title, public string $message, public array $extra=[]) {}
 public function via($notifiable): array { return ['database','mail']; }
 public function toDatabase($notifiable): array { return array_merge(['title'=>$this->title,'message'=>$this->message],$this->extra); }
 public function toMail($notifiable): MailMessage { return (new MailMessage)->subject($this->title)->line($this->message); }
}
