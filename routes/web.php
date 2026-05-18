<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\CatalogController as AdminCatalogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModerationController as AdminModerationController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\TradeController as AdminTradeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CollectionCardController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExplorerController;
use App\Http\Controllers\KpopController;
use App\Http\Controllers\MarkConversationReadController;
use App\Http\Controllers\MarketplaceCardController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\OpenMarketplaceConversationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProofVerificationController;
use App\Http\Controllers\PublicCollectionController;
use App\Http\Controllers\SendMessageController;
use App\Http\Controllers\StartConversationController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TradeRequestController;
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

Route::get('/check-username', function (\Illuminate\Http\Request $request) {
    $username = trim((string) $request->query('username'));

    return response()->json([
        'available' => $username !== ''
            && ! \App\Models\User::where('username', $username)->exists(),
    ]);
})->name('username.check');

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::prefix('welcome')
        ->name('onboarding.')
        ->group(function () {
            Route::get('/', [OnboardingController::class, 'start'])->name('start');
            Route::get('/step/{step}', [OnboardingController::class, 'step'])->name('step');
            Route::post('/complete', [OnboardingController::class, 'complete'])->name('complete');
            Route::post('/skip', [OnboardingController::class, 'skip'])->name('skip');
        });
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
    Route::patch('/collection/{userCard}/traded', [CollectionCardController::class, 'markAsTraded'])->name('collection.traded');
    Route::patch('/collection/{userCard}/sold', [CollectionCardController::class, 'markAsSold'])->name('collection.sold');
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
    Route::post('/marketplace/listings/{marketplaceListing}/verify-proof', [MarketplaceController::class, 'verifyProof'])->name('listings.verify-proof');
    Route::delete('/marketplace/listings/{marketplaceListing}', [MarketplaceController::class, 'destroy'])->name('marketplace.destroy');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{wishlistItem}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('/messages', MessagesController::class)->name('messages.index');
    Route::get('/messages/create', [StartConversationController::class, 'create'])->name('messages.create');
    Route::post('/messages/start', [StartConversationController::class, 'store'])->name('messages.start');
    Route::post('/messages', [SendMessageController::class, 'store'])->name('messages.store');
    Route::delete('/messages/{message}', [SendMessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/messages/{conversation}/read', [MarkConversationReadController::class, 'store'])->name('messages.read');
    Route::get('/explorer', [ExplorerController::class, 'index'])->name('explorer.index');
    Route::get('/api/kpop', [KpopController::class, 'index'])->name('api.kpop');
    Route::post('/explorer/save-view', [ExplorerController::class, 'storeSavedView'])->name('explorer.saved-views.store');
    Route::get('/explorer/catalogs/{catalog}', [ExplorerController::class, 'show'])->name('explorer.catalogs.show');
    Route::post('/messages/listings/{marketplaceListing}', [OpenMarketplaceConversationController::class, 'store'])->name('messages.listings.store');
    Route::post('/trade-requests', [TradeRequestController::class, 'store'])->name('trade-requests.store');
    Route::patch('/trade-requests/{tradeRequest}/accept', [TradeRequestController::class, 'accept'])->name('trade-requests.accept');
    Route::patch('/trade-requests/{tradeRequest}/decline', [TradeRequestController::class, 'decline'])->name('trade-requests.decline');
    Route::patch('/trade-requests/{tradeRequest}/complete', [TradeRequestController::class, 'complete'])->name('trade-requests.complete');
    Route::patch('/trade-requests/{tradeRequest}/cancel', [TradeRequestController::class, 'cancel'])->name('trade-requests.cancel');
    Route::get('/stats/export/pdf', [StatsController::class, 'exportPdf'])->name('stats.export.pdf');
    Route::get('/stats/export/csv', [StatsController::class, 'exportCsv'])->name('stats.export.csv');
    Route::post('/stats/share', [StatsController::class, 'shareSnapshot'])->name('stats.share');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('index');
        Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
        Route::post('/users/{user}/note', [AdminUserController::class, 'saveNote'])->name('users.note');
        Route::patch('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/listings', [AdminController::class, 'listings'])->name('listings');
        Route::delete('/listings/{marketplaceListing}', [AdminController::class, 'deleteListing'])->name('listings.delete');
        Route::patch('/listings/{marketplaceListing}/verify-proof', [AdminController::class, 'verifyProof'])->name('listings.verify-proof');
        Route::get('/trades', [AdminTradeController::class, 'index'])->name('trades');
        Route::get('/moderation', [AdminModerationController::class, 'index'])->name('moderation');
        Route::get('/moderation/proof', [AdminModerationController::class, 'proof'])->name('moderation.proof');
        Route::post('/moderation/proof/{listing}/verify', [AdminModerationController::class, 'verifyProof'])->name('moderation.proof.verify');
        Route::post('/moderation/proof/{listing}/request', [AdminModerationController::class, 'requestProof'])->name('moderation.proof.request');
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::resource('/catalog', AdminCatalogController::class)
            ->parameters(['catalog' => 'card'])
            ->names('catalog')
            ->except(['show']);
        Route::get('/reports', fn () => redirect()->route('admin.moderation'))->name('reports');
    });
