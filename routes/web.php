<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CollectionCardController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExplorerController;
use App\Http\Controllers\MarkConversationReadController;
use App\Http\Controllers\MarketplaceCardController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\OpenMarketplaceConversationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProofVerificationController;
use App\Http\Controllers\PublicCollectionController;
use App\Http\Controllers\SendMessageController;
use App\Http\Controllers\StartConversationController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/', 'auth.login');
    Route::view('/login', 'auth.login')->name('login');
    Route::get('/forgot-password', [ComingSoonController::class, 'forgotPassword'])->name('password.request');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register.create');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::get('/collectors/{user:username}', [ProfileController::class, 'showcase'])
    ->name('profile.showcase');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/stats', StatsController::class)->name('stats.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings/account', [ProfileController::class, 'settings'])->name('settings.account');
    Route::get('/profile/{user:username}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/collection', CollectionController::class)->name('collection.index');
    Route::get('/collection/create', [CollectionCardController::class, 'create'])->name('collection.create');
    Route::post('/collection', [CollectionCardController::class, 'store'])->name('collection.store');
    Route::get('/collection/{userCard}', [CollectionCardController::class, 'show'])->name('collection.show');
    Route::get('/collection/{userCard}/edit', [CollectionCardController::class, 'edit'])->name('collection.edit');
    Route::put('/collection/{userCard}', [CollectionCardController::class, 'update'])->name('collection.update');
    Route::delete('/collection/{userCard}', [CollectionCardController::class, 'destroy'])->name('collection.destroy');
    Route::get('/cards/{id}', [CollectionCardController::class, 'showCard'])->name('cards.show');
    Route::post('/cards/{card}/upload-proof', [CardController::class, 'uploadProof'])->name('cards.uploadProof');
    Route::patch('/user-cards/{userCard}/approve-proof', [ProofVerificationController::class, 'approve'])->name('user-cards.approve-proof')->middleware('auth');
    Route::get('/marketplace', MarketplaceController::class)->name('marketplace.index');
    Route::get('/marketplace/create', [MarketplaceController::class, 'create'])->name('marketplace.create');
    Route::post('/marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
    Route::get('/marketplace/users/{user:username}', [PublicCollectionController::class, 'show'])->name('marketplace.user');
    Route::get('/marketplace/cards/{marketplaceListing}', [MarketplaceCardController::class, 'show'])->name('marketplace.cards.show');
    Route::get('/marketplace/listings/{marketplaceListing}/edit', [MarketplaceController::class, 'edit'])->name('marketplace.edit');
    Route::put('/marketplace/listings/{marketplaceListing}', [MarketplaceController::class, 'update'])->name('marketplace.update');
    Route::patch('/marketplace/listings/{marketplaceListing}/sold', [MarketplaceController::class, 'markAsSold'])->name('marketplace.sold');
    Route::patch('/marketplace/listings/{marketplaceListing}/archive', [MarketplaceController::class, 'archive'])->name('marketplace.archive');
    Route::delete('/marketplace/listings/{marketplaceListing}', [MarketplaceController::class, 'destroy'])->name('marketplace.destroy');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{wishlistItem}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('/messages', MessagesController::class)->name('messages.index');
    Route::get('/messages/create', [StartConversationController::class, 'create'])->name('messages.create');
    Route::post('/messages/start', [StartConversationController::class, 'store'])->name('messages.start');
    Route::post('/messages', [SendMessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/{conversation}/read', [MarkConversationReadController::class, 'store'])->name('messages.read');
    Route::get('/explorer', [ExplorerController::class, 'index'])->name('explorer.index');
    Route::post('/explorer/save-view', [ExplorerController::class, 'storeSavedView'])->name('explorer.saved-views.store');
    Route::get('/explorer/catalogs/{catalog}', [ExplorerController::class, 'show'])->name('explorer.catalogs.show');
    Route::post('/messages/listings/{marketplaceListing}', [OpenMarketplaceConversationController::class, 'store'])->name('messages.listings.store');
    Route::get('/stats/export/pdf', [StatsController::class, 'exportPdf'])->name('stats.export.pdf');
    Route::get('/stats/export/csv', [StatsController::class, 'exportCsv'])->name('stats.export.csv');
    Route::post('/stats/share', [StatsController::class, 'shareSnapshot'])->name('stats.share');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
