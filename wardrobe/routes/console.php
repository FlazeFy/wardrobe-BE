<?php

use Illuminate\Support\Facades\Schedule;
 
// For Testing
Schedule::call(function () {
    // \App\Schedule\AuditSchedule::audit_error();
    // \App\Schedule\AuditSchedule::audit_apps();
    // \App\Schedule\CleanSchedule::clean_history();
    // \App\Schedule\CleanSchedule::clean_deleted_clothes();
    // \App\Schedule\ReminderSchedule::remind_predeleted_clothes();
})->everyMinute();

// For Production
/*
Schedule::call(function () {
    \App\Schedule\AuditSchedule::audit_error();
    \App\Schedule\AuditSchedule::audit_apps();
})->weeklyOn(1, '1:00');

Schedule::call(function () {
    \App\Schedule\CleanSchedule::clean_history();
    \App\Schedule\CleanSchedule::clean_deleted_clothes();
})->dailyAt('2:00');

Schedule::call(function () {
    \App\Schedule\ReminderSchedule::remind_predeleted_clothes();
})->dailyAt('3:00');
*/
