<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

// 1. Daily Cold Outreach Dispatch:
// Dispatches up to 250 emails prioritized by score (>=75 first) with 1-per-company daily cap at 10:00 AM US EST
Schedule::command('outreach:dispatch-daily --limit=250')
    ->timezone('America/New_York')
    ->dailyAt('10:00') // Exactly 10:00 AM US Eastern Time
    ->withoutOverlapping()
    ->runInBackground();

// Staggered Outreach Window (every 10 minutes between 7:00 PM - 10:00 PM BST / 8:00 AM - 11:00 AM EST):
Schedule::command('outreach:send-scheduled --limit=25 --daily-limit=250')
    ->everyTenMinutes()
    ->timezone('Asia/Dhaka')
    ->between('19:00', '22:00')
    ->withoutOverlapping()
    ->runInBackground();

// 2. Automated 2-Way IMAP Inbox Sync (Replies & Bounces):
// Polls IMAP server every 10 minutes to capture customer responses & update dashboard metrics
Schedule::command('email:fetch-replies')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// 3. Automated 3-Day Follow-Up Processor:
// Sweeps for due Step 2 follow-ups daily at 11:00 AM US EST (Capped at 50/day)
Schedule::command('outreach:process-followups --limit=50')
    ->timezone('America/New_York')
    ->dailyAt('11:00') // Exactly 11:00 AM US Eastern Time
    ->withoutOverlapping()
    ->runInBackground();
