<?php

use Illuminate\Support\Facades\Schedule;
 
// For Testing
Schedule::call(function () {
    // \App\Schedule\AuditSchedule::audit_error();
})->everyMinute();

// For Production
/*
Schedule::call(function () {
    \App\Schedule\AuditSchedule::audit_error();
})->weeklyOn(1, '1:00');
*/