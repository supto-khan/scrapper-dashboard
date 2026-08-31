 <?php

use App\Http\Controllers\EngineStreamController;
use App\Livewire\CompanyDetail;
use App\Livewire\InboxDashboard;
use App\Livewire\OpportunityDashboard;
use App\Livewire\OutreachLogs;
use App\Livewire\UpworkJobs;
use Illuminate\Support\Facades\Route;

Route::get('/', OpportunityDashboard::class)->name('dashboard');
Route::get('/inbox', InboxDashboard::class)->name('inbox');
Route::get('/companies/{id}', CompanyDetail::class)->name('company.detail');
Route::get('/upwork-jobs', UpworkJobs::class)->name('upwork.jobs');
Route::get('/outreach-logs', OutreachLogs::class)->name('outreach.logs');
Route::get('/track/open/{id}', [\App\Http\Controllers\OutreachTrackerController::class, 'trackOpen'])->name('track.open');
Route::get('/track/click/{id}', [\App\Http\Controllers\OutreachTrackerController::class, 'trackClick'])->name('track.click');
Route::get('/engine/stream/{script}', [EngineStreamController::class, 'stream'])->name('engine.stream');
