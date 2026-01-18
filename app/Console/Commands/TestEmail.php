<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'test:email {email=test@example.com}';
    protected $description = 'Send a test email to verify email configuration';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Sending test email to: ' . $email);
        
        try {
            Mail::raw('This is a test email from InternalOS automation system.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email from InternalOS');
            });
            
            $this->info('✅ Email sent successfully!');
            $this->info('Check your Mailtrap inbox at: https://mailtrap.io');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->error('Check your .env file mail configuration');
            
            return 1;
        }
    }
}
