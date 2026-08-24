<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BillingWebhookController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContractCommentController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractWorkflowController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\NegotiationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicVerificationController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::view('/privacy', 'privacy')->name('privacy');
Route::post('/contact', [ContactController::class, 'submit'])->middleware('throttle:5,1')->name('contact.submit');
Route::post('/billing/webhook', [BillingWebhookController::class, 'handle'])->name('billing.webhook');
Route::get('/verify/{reference}', [PublicVerificationController::class, 'show'])->name('verify.public');
Route::get('/ref/{code}', [ReferralController::class, 'show'])->name('referral.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pricing', [BillingController::class, 'pricing'])->name('billing.pricing');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::get('/referidos', [ReferralController::class, 'index'])->name('referrals.index');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
});

// Public contract helper endpoints (accessible to creators & counterparties)
Route::match(['get', 'post'], '/contracts/tax-id-check', [ContractController::class, 'checkTaxId'])->middleware('throttle:60,1')->name('contracts.tax-id-check');
Route::post('/contracts/scan-id', [ContractController::class, 'scanIdCard'])->middleware('throttle:30,1')->name('contracts.scan-id');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ContractController::class, 'index'])->name('dashboard');

    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/create', [ContractController::class, 'create'])->name('create');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('/export', [ContractController::class, 'exportAll'])->name('export');

        Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
        Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('/{contract}', [ContractController::class, 'update'])->name('update');
        Route::get('/{contract}/preview', [ContractController::class, 'preview'])->name('preview');
        Route::get('/{contract}/download', [ContractController::class, 'download'])->name('download');
        Route::get('/{contract}/evidence', [ContractController::class, 'evidence'])->name('evidence');
        Route::post('/{contract}/verify', [ContractController::class, 'verify'])->name('verify');
        Route::get('/{contract}/signing-link', [ContractController::class, 'signingLink'])->name('signing-link');
        Route::get('/{contract}/review-link', [ContractController::class, 'reviewLink'])->name('review-link');
        Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');

        Route::post('/{contract}/send-review', [ContractWorkflowController::class, 'sendReview'])->name('send-review');
        Route::post('/{contract}/accept-final', [ContractWorkflowController::class, 'acceptFinal'])->name('accept-final');
        Route::post('/{contract}/send-signature', [ContractWorkflowController::class, 'sendSignature'])->name('send-signature');
        Route::post('/{contract}/cancel', [ContractWorkflowController::class, 'cancel'])->name('cancel');

        Route::post('/{contract}/comments', [ContractCommentController::class, 'store'])->name('comments.store');
        Route::post('/{contract}/proposals', [NegotiationController::class, 'store'])->name('proposals.store');
        Route::post('/{contract}/proposals/{proposal}/approve', [NegotiationController::class, 'approve'])->name('proposals.approve');
        Route::post('/{contract}/proposals/{proposal}/reject', [NegotiationController::class, 'reject'])->name('proposals.reject');

        Route::scopeBindings()->group(function () {
            Route::get('/{contract}/documents', [DocumentController::class, 'index'])->name('documents');
            Route::post('/{contract}/documents', [DocumentController::class, 'upload'])->name('documents.upload');
            Route::post('/{contract}/documents/{document}/validate', [DocumentController::class, 'validateDocument'])->name('documents.validate');
            Route::get('/{contract}/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
            Route::delete('/{contract}/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
        });
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/demo-plan', [ProfileController::class, 'switchPlanDemo'])->name('profile.demo-plan');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/gdpr-export', [ProfileController::class, 'exportGdprData'])->name('profile.gdpr.export');
    Route::post('/profile/gdpr-request', [ProfileController::class, 'requestGdprRight'])->name('profile.gdpr.request');
});

// Public endpoints (no account required for counterparty)
Route::get('/review/{token}', [ReviewController::class, 'show'])->name('review.show');
Route::post('/review/{token}/party', [ReviewController::class, 'updateParty'])->name('review.party.update');
Route::post('/review/{token}/accept', [ReviewController::class, 'accept'])->middleware('throttle:15,1')->name('review.accept');
Route::post('/review/{token}/propose', [ReviewController::class, 'propose'])->middleware('throttle:20,1')->name('review.propose');
Route::post('/review/{token}/comments', [ContractCommentController::class, 'storePublic'])->middleware('throttle:30,1')->name('review.comments.store');
Route::get('/review/{token}/download', [ReviewController::class, 'download'])->name('review.download');

Route::get('/sign/{token}', [SignatureController::class, 'show'])->name('sign.show');
Route::post('/sign/{token}', [SignatureController::class, 'store'])->middleware('throttle:15,1')->name('sign.store');
Route::post('/sign/{token}/otp', [SignatureController::class, 'requestOtp'])->middleware('throttle:6,1')->name('sign.otp');
Route::get('/sign/{token}/download', [SignatureController::class, 'download'])->name('sign.download');

require __DIR__.'/auth.php';
