<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventTicketController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\FerryController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AdminController;

Route::view('/', 'welcome')->name('home');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Rooms
    Route::get('/rooms', [RoomBookingController::class, 'index'])->name('rooms.index');
    Route::post('/rooms/book', [RoomBookingController::class, 'store'])->name('rooms.book');
    Route::delete('/rooms/bookings/{booking}', [RoomBookingController::class, 'destroy'])->name('rooms.cancel');

    // Events
    Route::get('/events', [EventTicketController::class, 'index'])->name('events.index');
    Route::post('/events/{event}/tickets', [EventTicketController::class, 'store'])->name('events.tickets.store');

    // Ferries
    Route::get('/ferry/schedules', [FerryController::class, 'index'])->name('ferry.schedules');
    Route::post('/ferry/schedules/{schedule}/tickets', [FerryController::class, 'store'])->name('ferry.tickets.store');
});

// Dashboard (auth + verified)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
});

// Breeze profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Ferry owner-only management (CRUD)
Route::middleware(['auth', 'can:manage-ferry'])->group(function () {
    Route::get('/ferry/manage', [FerryController::class, 'manage'])->name('ferry.manage');
    Route::get('/ferry/schedules/create', [FerryController::class, 'create'])->name('ferry.schedules.create');
    Route::post('/ferry/schedules', [FerryController::class, 'storeSchedule'])->name('ferry.schedules.store');
    Route::get('/ferry/schedules/{id}/edit', [FerryController::class, 'edit'])->name('ferry.schedules.edit');
    Route::patch('/ferry/schedules/{id}', [FerryController::class, 'update'])->name('ferry.schedules.update');
    Route::delete('/ferry/schedules/{id}', [FerryController::class, 'destroySchedule'])->name('ferry.schedules.destroy');
});

// Hotel owner-only room availability management
Route::middleware(['auth', 'can:manage-rooms'])->group(function () {
    Route::get('/rooms/manage', [RoomController::class, 'manage'])->name('rooms.manage');
    Route::get('/rooms/{room}/availability', [RoomController::class, 'editAvailability'])->name('rooms.availability.edit');
    Route::patch('/rooms/{room}/availability', [RoomController::class, 'updateAvailability'])->name('rooms.availability.update');
});

// Event owner-only management (CRUD) — eventowner@test.com
Route::middleware(['auth', 'can:manage-events'])->group(function () {
    Route::get('/events/manage',        [EventTicketController::class, 'manage'])->name('events.manage');
    Route::get('/events/create',        [EventTicketController::class, 'create'])->name('events.create');
    Route::post('/events',              [EventTicketController::class, 'storeEvent'])->name('events.store');
    Route::get('/events/{id}/edit',     [EventTicketController::class, 'edit'])->name('events.edit');
    Route::patch('/events/{id}',        [EventTicketController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}',       [EventTicketController::class, 'destroyEvent'])->name('events.destroy');
});

// Admin functions
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/admin/bookings', [AdminController::class, 'index'])->name('admin.bookings');

    // Cancel endpoints
    Route::patch('/admin/rooms/bookings/{id}/cancel',  [AdminController::class, 'cancelRoom'])->name('admin.rooms.cancel');
    Route::patch('/admin/events/tickets/{id}/cancel',  [AdminController::class, 'cancelEvent'])->name('admin.events.cancel');
    Route::patch('/admin/ferry/tickets/{id}/cancel',   [AdminController::class, 'cancelFerry'])->name('admin.ferry.cancel');
});

require __DIR__.'/auth.php';
