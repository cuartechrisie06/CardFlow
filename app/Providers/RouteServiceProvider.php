public function boot()
{
    parent::boot();

    // Route model binding for UserCard
    Route::model('userCard', \App\Models\UserCard::class);
}