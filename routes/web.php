<?php

use App\Filament\Pages\Report;
use Illuminate\Support\Facades\Route;
use Filament\Http\Livewire\Auth\Login;
use App\Http\Controllers\ContactController;
use App\Livewire\DroneStatistik;
use App\Livewire\BatteryStatistik;

Route::get('/', function () {
    return redirect('/admin');
});
Route::post('/send-email', [ContactController::class, 'sendEmail'])->name('sendEmail');

Route::get('/report', Report::class)->name('filament.report');
Route::post('/report/download', [Report::class, 'downloadReport'])->name('filament.report.download');
Route::post('/filament/report/inventory/download', [Report::class, 'downloadInventoryReport'])->name('filament.report.inventory.download');
Route::get('/drone-statistik/{drone_id}', [DroneStatistik::class, 'showDroneStatistik'])->name('drone.statistik');
Route::get('/battery-statistik/{battery_id}', [BatteryStatistik::class, 'showBatteryStatistik'])->name('battery.statistik');
