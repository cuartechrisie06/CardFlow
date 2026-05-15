<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Card;
use App\Models\CardAlias;
use App\Models\CardVariant;
use App\Models\MarketplaceListing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExplorerCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $artists = Card::query()
            ->select('artist')
            ->distinct()
            ->orderBy('artist')
            ->pluck('artist');

        $artistMap = [];

        foreach ($artists as $artistName) {
            $artist = Artist::query()->updateOrCreate(
                ['name' => $artistName],
                [
                    'slug' => Str::slug($artistName),
                    'name_original' => $artistName,
                    'aliases' => array_values(array_filter([
                        $artistName,
                        Str::upper($artistName),
                    ])),
                    'is_active' => true,
                ]
            );

            $artistMap[$artistName] = $artist;
        }

        Card::query()->orderBy('id')->chunkById(50, function ($cards) use ($artistMap) {
            foreach ($cards as $card) {
                $artist = $artistMap[$card->artist] ?? null;

                if (! $artist) {
                    continue;
                }

                $album = null;

                if ($card->album) {
                    $album = Album::query()->updateOrCreate(
                        [
                            'artist_id' => $artist->id,
                            'title' => $card->album,
                        ],
                        [
                            'era' => $card->edition ?: null,
                        ]
                    );
                }

                $memberName = Str::contains($card->title, ' - ')
                    ? trim(Str::before($card->title, ' - '))
                    : null;

                $card->update([
                    'artist_id' => $artist->id,
                    'album_id' => $album?->id,
                    'slug' => Str::slug($card->artist.'-'.$card->title),
                    'member_name' => $memberName,
                    'variant_type' => $card->edition ?: $card->rarity,
                    'finish' => null,
                    'catalog_code' => 'CF-'.$card->id,
                ]);

                CardAlias::query()->updateOrCreate(
                    [
                        'card_id' => $card->id,
                        'alias' => $card->title,
                    ],
                    [
                        'alias_type' => 'title',
                    ]
                );

                if ($card->edition) {
                    CardAlias::query()->updateOrCreate(
                        [
                            'card_id' => $card->id,
                            'alias' => $card->edition,
                        ],
                        [
                            'alias_type' => 'edition',
                        ]
                    );
                }

                $ownedCount = $card->userCards()->count();
                $listedCount = MarketplaceListing::query()
                    ->activeVisible()
                    ->where('card_id', $card->id)
                    ->count();

                $tradeAverage = (float) $card->userCards()
                    ->selectRaw('AVG(COALESCE(listing_price, estimated_value, 0)) as avg_value')
                    ->value('avg_value');

                CardVariant::query()->updateOrCreate(
                    [
                        'card_id' => $card->id,
                        'variant_name' => $card->edition ?: $card->title,
                    ],
                    [
                        'variant_type' => $card->rarity ?: $card->edition,
                        'image_url' => $card->official_image_url,
                        'community_owned_count' => $ownedCount,
                        'community_listed_count' => $listedCount,
                        'average_trade_value' => $tradeAverage,
                        'average_sale_price' => $tradeAverage,
                        'last_synced_at' => now(),
                    ]
                );
            }
        });
    }
}
