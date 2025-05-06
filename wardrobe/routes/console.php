<?php

use Illuminate\Support\Facades\Schedule;
 
// For Testing
Schedule::call(function () {
    // \App\Schedule\AuditSchedule::audit_error();
    // \App\Schedule\AuditSchedule::audit_apps();
    // \App\Schedule\CleanSchedule::clean_history();
})->everyMinute();

// For Production
/*
Schedule::call(function () {
    \App\Schedule\AuditSchedule::audit_error();
    \App\Schedule\AuditSchedule::audit_apps();
})->weeklyOn(1, '1:00');

Schedule::call(function () {
    \App\Schedule\CleanSchedule::clean_history();
})->dailyAt('2:00');
*/
