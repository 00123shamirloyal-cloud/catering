<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentEntryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/services', [PublicSiteController::class, 'services'])->name('services.index');
Route::get('/services/{slug}', [PublicSiteController::class, 'service'])->name('services.show');
Route::get('/menus', [PublicSiteController::class, 'menus'])->name('menus.index');
Route::get('/packages', [PublicSiteController::class, 'packages'])->name('packages.index');
Route::get('/packages/{slug}', [PublicSiteController::class, 'package'])->name('packages.show');
Route::get('/gallery', [PublicSiteController::class, 'gallery'])->name('gallery.index');
Route::get('/gallery/{slug}', [PublicSiteController::class, 'galleryEntry'])->name('gallery.show');
Route::get('/about', [PublicSiteController::class, 'about'])->name('about');
Route::get('/faqs', [PublicSiteController::class, 'faqs'])->name('faqs');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicSiteController::class, 'storeContact'])->middleware('throttle:8,1')->name('contact.store');
Route::get('/policies/{slug}', [PublicSiteController::class, 'policy'])
    ->where('slug', 'privacy-policy|terms|booking-policy|cancellation-policy')
    ->name('policies.show');
Route::get('/plan-your-event', [PublicSiteController::class, 'createBooking'])->name('booking.create');
Route::post('/plan-your-event', [PublicSiteController::class, 'storeBooking'])->middleware('throttle:8,1')->name('booking.store');
Route::get('/plan-your-event/confirmed/{reference}', [PublicSiteController::class, 'bookingConfirmation'])->name('booking.confirmation');
Route::get('/proposal/{quotation}/{token}', [QuotationController::class, 'showProposal'])->name('proposals.show');
Route::post('/proposal/{quotation}/{token}/accept', [QuotationController::class, 'acceptProposal'])->middleware('throttle:8,1')->name('proposals.accept');
Route::get('/proposal/{quotation}/{token}/pdf', [QuotationController::class, 'downloadProposalPdf'])->name('proposals.pdf');

Route::get('/staff/sign-in', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/staff/sign-in', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::post('/staff/sign-out', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'staff'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::middleware('can:view-reports')->group(function (): void {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('can:manage-staff')->group(function (): void {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    });

    Route::middleware('can:view-crm')->group(function (): void {
        Route::get('/search', SearchController::class)->name('search');
        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
        Route::put('/inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');
        Route::middleware('can:manage-customers')->group(function (): void {
            Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        });
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::put('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
        Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contacts.index');
        Route::put('/contact-messages/{contactMessage}/handled', [ContactMessageController::class, 'markHandled'])->name('contacts.handled');
    });

    Route::get('/calendar', CalendarController::class)->middleware('can:manage-appointments')->name('calendar');
    Route::middleware('can:manage-appointments')->group(function (): void {
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::put('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    });

    Route::middleware('can:manage-events')->group(function (): void {
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::put('/events/{event}/status', [EventController::class, 'updateStatus'])->name('events.status');
        Route::post('/inquiries/{inquiry}/events', [EventController::class, 'createFromInquiry'])->name('inquiries.events.store');
    });

    Route::middleware('can:manage-finance')->group(function (): void {
        Route::get('/inquiries/{inquiry}/quotations/create', [QuotationController::class, 'createForInquiry'])->name('inquiries.quotations.create');
        Route::get('/events/{event}/quotations/create', [QuotationController::class, 'createForEvent'])->name('events.quotations.create');
        Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
        Route::get('/quotations/{quotation}/revise', [QuotationController::class, 'revise'])->name('quotations.revise');
        Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
        Route::post('/quotations/{quotation}/invoice', [InvoiceController::class, 'createFromQuotation'])->name('quotations.invoice');
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    });

    Route::middleware('can:manage-payments')->group(function (): void {
        Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
        Route::post('/invoices/{invoice}/adjustments', [PaymentController::class, 'storeAdjustment'])->name('invoices.adjustments.store');
    });

    Route::middleware('can:manage-website')->group(function (): void {
        Route::get('/website-content', [ContentEntryController::class, 'index'])->name('content.index');
        Route::post('/website-content', [ContentEntryController::class, 'store'])->name('content.store');
        Route::put('/website-content/{contentEntry}', [ContentEntryController::class, 'update'])->name('content.update');
        Route::delete('/website-content/{contentEntry}', [ContentEntryController::class, 'destroy'])->name('content.destroy');
        Route::get('/website-content/{contentEntry}/versions', [ContentEntryController::class, 'versions'])->name('content.versions');
        Route::post('/website-content/{contentEntry}/versions/{version}/restore', [ContentEntryController::class, 'restore'])->name('content.versions.restore');
        Route::get('/website-settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/website-settings', [SiteSettingController::class, 'update'])->name('settings.update');
    });

    Route::post('/website-content/{contentEntry}/publish', [ContentEntryController::class, 'publish'])
        ->middleware('can:publish-website')->name('content.publish');
    Route::post('/website-content/{contentEntry}/unpublish', [ContentEntryController::class, 'unpublish'])
        ->middleware('can:publish-website')->name('content.unpublish');
});
