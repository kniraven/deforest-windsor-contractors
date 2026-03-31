<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\ListingChangeRequestController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ListingController::class, 'index'])->name('listings.index');

Route::get('/dashboard', function () {
    return redirect()->route('admin.listings.index');
})->middleware(['auth'])->name('dashboard');

Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');

Route::get('/listings/{listing}/request', [ListingChangeRequestController::class, 'create'])
    ->name('listings.requests.create');

Route::post('/listings/{listing}/request', [ListingChangeRequestController::class, 'store'])
    ->name('listings.requests.store');

Route::get('/submit-listing', [ListingSubmissionController::class, 'create'])->name('listings.submit.create');
Route::post('/submit-listing', [ListingSubmissionController::class, 'store'])->name('listings.submit.store');

Route::get('/admin/logs', [AdminActivityLogController::class, 'index'])
    ->name('admin.logs.index')
    ->middleware(['auth', 'admin']);

Route::resource('admin/listings', AdminListingController::class)
    ->except(['show'])
    ->names('admin.listings')
    ->middleware(['auth', 'admin']);