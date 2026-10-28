<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DummyPaymentController;
use App\Http\Controllers\Portal;
use App\Http\Controllers\Public;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/

Route::get('/', Public\HomeController::class)->name('home');
Route::view('/about', 'public.about')->name('about');

Route::get('/courses', [Public\CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{slug}', [Public\CourseController::class, 'show'])->name('courses.show');

Route::get('/enquire-now', [Public\AdmissionEnquiryController::class, 'create'])->name('admissions.create');
Route::get('/admissions', fn (Request $request) => redirect()->route('admissions.create', $request->query(), 301));
Route::post('/enquire-now', [Public\AdmissionEnquiryController::class, 'store'])
    ->middleware('throttle:public-forms')
    ->name('admissions.store');

Route::get('/contact', [Public\ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [Public\ContactController::class, 'store'])
    ->middleware('throttle:public-forms')
    ->name('contact.store');

Route::get('/results', [Public\ResultController::class, 'index'])->name('results.index');
Route::post('/results', [Public\ResultController::class, 'lookup'])
    ->middleware('throttle:result-lookup')
    ->name('results.lookup');
Route::get('/results/document/{enrollment}', [Public\ResultController::class, 'document'])
    ->middleware('signed')
    ->name('results.document');

Route::middleware('throttle:verify-certificate')->group(function () {
    Route::get('/verify-certificate', [Public\CertificateVerificationController::class, 'index'])->name('certificates.verify');
    Route::get('/verify-certificate/{identifier}', [Public\CertificateVerificationController::class, 'show'])
        ->where('identifier', '[A-Za-z0-9\-]{1,64}')
        ->name('certificates.verify.show');
});

/*
|--------------------------------------------------------------------------
| Authenticated landing
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', fn () => redirect(request()->user()->homeRoute()))
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Checkout and payments (students)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'student', 'verified'])->group(function () {
    Route::get('/courses/{course:slug}/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/courses/{course:slug}/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:checkout')
        ->name('checkout.store');
    Route::get('/orders/{order:order_number}', [CheckoutController::class, 'show'])->name('checkout.show');

    Route::get('/payments/dummy/{order:order_number}', [DummyPaymentController::class, 'show'])
        ->middleware('signed')
        ->name('payments.dummy.show');
    Route::post('/payments/dummy/{order:order_number}', [DummyPaymentController::class, 'complete'])
        ->middleware('signed')
        ->name('payments.dummy.complete');
});

/*
|--------------------------------------------------------------------------
| Student portal
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'student'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', Portal\DashboardController::class)->name('dashboard');
    Route::get('/enrollments/{enrollment:enrollment_number}', [Portal\EnrollmentController::class, 'show'])->name('enrollments.show');
    Route::get('/certificates/{certificate:certificate_number}/download', [Portal\CertificateController::class, 'download'])->name('certificates.download');
    Route::view('/password', 'portal.password')->name('password');
});

/*
|--------------------------------------------------------------------------
| Admin back office
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'backoffice'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::get('search', Admin\SearchController::class)->middleware('throttle:admin-search')->name('search');

    Route::resource('categories', Admin\CourseCategoryController::class)->except('show');

    Route::resource('courses', Admin\CourseController::class);
    Route::patch('courses/{course}/status', [Admin\CourseController::class, 'updateStatus'])->name('courses.status');
    Route::post('courses/{course}/modules', [Admin\CourseModuleController::class, 'store'])->name('courses.modules.store');
    Route::put('modules/{module}', [Admin\CourseModuleController::class, 'update'])->name('modules.update');
    Route::delete('modules/{module}', [Admin\CourseModuleController::class, 'destroy'])->name('modules.destroy');
    Route::get('courses/{course}/marks', [Admin\MarkController::class, 'edit'])->name('courses.marks.edit');
    Route::put('courses/{course}/marks', [Admin\MarkController::class, 'update'])->name('courses.marks.update');

    Route::resource('students', Admin\StudentController::class);
    Route::post('students/{student}/invite', [Admin\StudentController::class, 'invite'])->name('students.invite');

    Route::resource('enrollments', Admin\EnrollmentController::class)->except(['edit', 'destroy']);
    Route::post('enrollments/{enrollment}/result-document', [Admin\EnrollmentResultDocumentController::class, 'store'])->name('enrollments.result-document.store');
    Route::get('enrollments/{enrollment}/result-document', [Admin\EnrollmentResultDocumentController::class, 'show'])->name('enrollments.result-document.show');
    Route::delete('enrollments/{enrollment}/result-document', [Admin\EnrollmentResultDocumentController::class, 'destroy'])->name('enrollments.result-document.destroy');

    Route::get('certificates', [Admin\CertificateController::class, 'index'])->name('certificates.index');
    Route::get('certificates/{certificate}', [Admin\CertificateController::class, 'show'])->name('certificates.show');
    Route::get('certificates/{certificate}/pdf', [Admin\CertificateController::class, 'pdf'])->name('certificates.pdf');
    Route::get('certificates/{certificate}/print', [Admin\CertificateController::class, 'print'])->name('certificates.print');
    Route::post('enrollments/{enrollment}/certificate', [Admin\CertificateController::class, 'store'])
        ->middleware('can:manage-certificates')
        ->name('certificates.store');
    Route::post('certificates/{certificate}/revoke', [Admin\CertificateController::class, 'revoke'])
        ->middleware('can:manage-certificates')
        ->name('certificates.revoke');

    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');

    Route::get('enquiries', [Admin\AdmissionEnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('enquiries/{enquiry}', [Admin\AdmissionEnquiryController::class, 'show'])->name('enquiries.show');
    Route::patch('enquiries/{enquiry}', [Admin\AdmissionEnquiryController::class, 'update'])->name('enquiries.update');
    Route::post('enquiries/{enquiry}/reply', [Admin\AdmissionEnquiryController::class, 'reply'])->middleware('throttle:20,1')->name('enquiries.reply');

    Route::get('messages', [Admin\ContactMessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [Admin\ContactMessageController::class, 'show'])->name('messages.show');
    Route::patch('messages/{message}', [Admin\ContactMessageController::class, 'update'])->name('messages.update');
    Route::post('messages/{message}/reply', [Admin\ContactMessageController::class, 'reply'])->middleware('throttle:20,1')->name('messages.reply');

    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', Admin\UserController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:manage-settings')->group(function () {
        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::put('settings/mail', [Admin\MailSettingsController::class, 'update'])->name('settings.mail.update');
        Route::post('settings/mail/test', [Admin\MailSettingsController::class, 'test'])->middleware('throttle:5,1')->name('settings.mail.test');
    });

    Route::get('activity', [Admin\ActivityLogController::class, 'index'])
        ->middleware('can:view-activity-log')
        ->name('activity.index');
});
