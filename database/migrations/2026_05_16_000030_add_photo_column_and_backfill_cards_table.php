<?php

use App\Models\Card;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cards', 'photo')) {
            Schema::table('cards', function (Blueprint $table) {
                $table->string('photo')->nullable()->after('official_image_url');
            });
        }

        Card::query()
            ->with(['userCards' => function ($query) {
                $query->whereNotNull('photo_path')
                    ->orderByDesc('updated_at')
                    ->select('id', 'card_id', 'photo_path', 'updated_at');
            }])
            ->orderBy('id')
            ->chunkById(100, function ($cards): void {
                foreach ($cards as $card) {
                    $rawPhoto = (string) ($card->getRawOriginal('photo') ?? '');
                    $needsFix = trim($rawPhoto) === '' || $rawPhoto === 'photo';

                    if (! $needsFix) {
                        continue;
                    }

                    $fallback = $card->userCards->first()?->photo_path;

                    if ($fallback) {
                        $card->forceFill(['photo' => $fallback])->saveQuietly();
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('cards', 'photo')) {
            Schema::table('cards', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }
    }
};
