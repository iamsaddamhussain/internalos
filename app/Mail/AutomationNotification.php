<?php

namespace App\Mail;

use App\Models\Record;
use App\Models\Automation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutomationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $notificationTitle;
    public string $notificationBody;
    public Record $record;
    public Automation $automation;
    public array $recordData;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, string $body, Record $record, Automation $automation)
    {
        $this->notificationTitle = $title;
        $this->notificationBody = $body;
        $this->record = $record;
        $this->automation = $automation;
        
        // Prepare formatted record data for email
        $this->recordData = $this->formatRecordData($record);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notificationTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.automation-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Format record data for display in email.
     */
    protected function formatRecordData(Record $record): array
    {
        $formatted = [];
        
        foreach ($record->collection->schema['fields'] ?? [] as $field) {
            $value = $record->data[$field['id']] ?? null;
            
            if ($value !== null) {
                // Format based on field type
                if ($field['type'] === 'date' && $value) {
                    $value = \Carbon\Carbon::parse($value)->format('M d, Y');
                } elseif ($field['type'] === 'checkbox') {
                    $value = $value ? 'Yes' : 'No';
                }
                
                $formatted[] = [
                    'label' => $field['label'],
                    'value' => $value,
                ];
            }
        }
        
        return $formatted;
    }
}
