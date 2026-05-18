<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Card;
use App\Models\Conversation;
use App\Models\MarketplaceListing;
use App\Models\Message;
use App\Models\User;
use App\Models\UserCard;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $kyle = $this->seedUser([
            'name' => 'Kyle Umbay',
            'username' => 'klayy',
            'email' => 'kyle@cardflow.test',
            'bio' => 'ENHYPEN collector focused on Jungwon, Sunoo, and limited album cards.',
            'location' => 'Manila, Philippines',
        ]);

        $noreen = $this->seedUser([
            'name' => 'Noreen Cuarte',
            'username' => 'sieshells',
            'email' => 'noreen@cardflow.test',
            'bio' => 'Trading aespa, LE SSERAFIM, and seasonal photocards.',
            'location' => 'Cebu, Philippines',
        ]);

        $this->resetDemoData($kyle, $noreen);

        $kyleCards = [
            $this->seedOwnedCard($kyle, [
                'artist' => 'ENHYPEN',
                'title' => 'Jungwon - Kalpa Ver.',
                'member_name' => 'Jungwon',
                'edition' => 'Album photocard',
                'album' => 'Romance: Untold',
                'rarity' => 'Mint',
                'market_value' => 1650,
                'purchase_price' => 1250,
                'estimated_value' => 1700,
                'photo' => 'user-cards/demo-kyle-jungwon.svg',
                'accent' => '#8B4513',
                'listed' => false,
            ]),
            $this->seedOwnedCard($kyle, [
                'artist' => 'ENHYPEN',
                'title' => 'Sunoo - Charybdis Holo',
                'member_name' => 'Sunoo',
                'edition' => 'Lucky draw',
                'album' => 'Dimension: Dilemma',
                'rarity' => 'Rare',
                'market_value' => 2100,
                'purchase_price' => 1600,
                'estimated_value' => 2200,
                'photo' => 'user-cards/demo-kyle-sunoo.svg',
                'accent' => '#2d6a4f',
                'listed' => true,
                'for_trade' => true,
                'listing_price' => 2200,
            ]),
        ];

        $noreenCards = [
            $this->seedOwnedCard($noreen, [
                'artist' => 'aespa',
                'title' => 'Winter - 2024 Seasons Greetings',
                'member_name' => 'Winter',
                'edition' => 'Seasonal release',
                'album' => '2024 Seasons Greetings',
                'rarity' => 'Rare',
                'market_value' => 1800,
                'purchase_price' => 1350,
                'estimated_value' => 1900,
                'photo' => 'user-cards/demo-noreen-winter.svg',
                'accent' => '#6f4e7c',
                'listed' => true,
                'for_trade' => true,
                'listing_price' => 1900,
            ]),
            $this->seedOwnedCard($noreen, [
                'artist' => 'LE SSERAFIM',
                'title' => 'Chaewon - Easy Broadcast',
                'member_name' => 'Chaewon',
                'edition' => 'Broadcast card',
                'album' => 'Easy',
                'rarity' => 'Mint',
                'market_value' => 2450,
                'purchase_price' => 1900,
                'estimated_value' => 2500,
                'photo' => 'user-cards/demo-noreen-chaewon.svg',
                'accent' => '#c8956c',
                'listed' => false,
            ]),
        ];

        $kyleListing = $this->seedListing($kyleCards[1], 'demo-proof-kyle-sunoo.svg');
        $noreenListing = $this->seedListing($noreenCards[0], 'demo-proof-noreen-winter.svg');

        WishlistItem::query()->updateOrCreate(
            ['user_id' => $kyle->id, 'card_id' => $noreenCards[0]->card_id],
            [
                'priority' => 'high',
                'target_price' => 1750,
                'matched_at' => now()->subMinutes(20),
            ]
        );

        WishlistItem::query()->updateOrCreate(
            ['user_id' => $noreen->id, 'card_id' => $kyleCards[0]->card_id],
            [
                'priority' => 'medium',
                'target_price' => 1500,
                'matched_at' => now()->subHour(),
            ]
        );

        $conversation = Conversation::query()->updateOrCreate(
            [
                'user_one_id' => min($kyle->id, $noreen->id),
                'user_two_id' => max($kyle->id, $noreen->id),
            ],
            ['marketplace_listing_id' => $noreenListing->id]
        );

        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $kyle->id,
            'receiver_id' => $noreen->id,
            'body' => 'Hi Noreen, is your Winter Seasons Greetings card still open for trade?',
            'message_type' => 'text',
            'created_at' => now()->subMinutes(35),
            'updated_at' => now()->subMinutes(35),
            'read_at' => now()->subMinutes(25),
        ]);

        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $noreen->id,
            'receiver_id' => $kyle->id,
            'body' => 'Yes, still available. I can check your ENHYPEN trades.',
            'message_type' => 'text',
            'created_at' => now()->subMinutes(28),
            'updated_at' => now()->subMinutes(28),
        ]);

        $this->seedActivities($kyle, [
            ['collection', 'Added Jungwon - Kalpa Ver.', 'Your ENHYPEN collection was updated.', now()->subHours(3)],
            ['listing_created', 'Listed Sunoo - Charybdis Holo', 'Your card is now visible in the marketplace.', now()->subHours(2)],
            ['wishlist_match', 'Wishlist match found', 'Winter - 2024 Seasons Greetings is available from @sieshells.', now()->subMinutes(20)],
        ]);

        $this->seedActivities($noreen, [
            ['collection', 'Added Winter - 2024 Seasons Greetings', 'Your aespa collection was updated.', now()->subHours(4)],
            ['listing_created', 'Listed Winter - 2024 Seasons Greetings', 'Your card is now visible in the marketplace.', now()->subHours(2)],
            ['message', '@klayy sent you a message', 'Kyle asked about your Winter listing.', now()->subMinutes(35)],
        ]);

        $kyleListing->touch();
        $noreenListing->touch();
    }

    private function seedUser(array $data): User
    {
        return User::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'username' => $data['username'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'bio' => $data['bio'],
                'location' => $data['location'],
                'is_admin' => false,
                'onboarding_completed' => true,
                'onboarding_step' => 5,
            ]
        );
    }

    private function resetDemoData(User $kyle, User $noreen): void
    {
        $users = [$kyle->id, $noreen->id];

        Conversation::query()
            ->where(function ($query) use ($kyle, $noreen) {
                $query->where('user_one_id', min($kyle->id, $noreen->id))
                    ->where('user_two_id', max($kyle->id, $noreen->id));
            })
            ->get()
            ->each(function (Conversation $conversation) {
                $conversation->messages()->forceDelete();
                $conversation->delete();
            });

        MarketplaceListing::query()->whereIn('user_id', $users)->delete();
        WishlistItem::query()->whereIn('user_id', $users)->delete();
        Activity::query()->whereIn('user_id', $users)->delete();
        UserCard::query()->whereIn('user_id', $users)->delete();
    }

    private function seedOwnedCard(User $user, array $data): UserCard
    {
        $this->putDemoCardImage($data['photo'], $data['title'], $data['artist'], $data['accent']);

        $card = Card::query()->updateOrCreate(
            [
                'artist' => $data['artist'],
                'title' => $data['title'],
            ],
            [
                'slug' => Str::slug($data['artist'].' '.$data['title']),
                'member_name' => $data['member_name'],
                'edition' => $data['edition'],
                'album' => $data['album'],
                'rarity' => $data['rarity'],
                'variant_type' => 'Official',
                'finish' => 'Glossy',
                'market_value' => $data['market_value'],
                'photo' => $data['photo'],
                'thumbnail_style' => 'market-thumb-one',
                'trend_score' => 82,
                'released_on' => now()->subMonths(6)->toDateString(),
            ]
        );

        $isListed = (bool) ($data['listed'] ?? false);

        return UserCard::query()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'condition' => 'Mint',
            'purchase_price' => $data['purchase_price'],
            'estimated_value' => $data['estimated_value'],
            'acquired_at' => now()->subDays(random_int(8, 40)),
            'is_listed' => $isListed,
            'marketplace_status' => $isListed ? 'active' : 'draft',
            'is_public' => true,
            'is_for_trade' => (bool) ($data['for_trade'] ?? false),
            'is_for_sale' => ! (bool) ($data['for_trade'] ?? false) && $isListed,
            'listing_price' => $data['listing_price'] ?? null,
            'photo_path' => $data['photo'],
            'notes' => 'Demo account seed card.',
        ]);
    }

    private function seedListing(UserCard $userCard, string $proofFile): MarketplaceListing
    {
        $this->putDemoProofImage($proofFile, $userCard->user->username, $userCard->card->title);

        return MarketplaceListing::query()->updateOrCreate(
            [
                'user_id' => $userCard->user_id,
                'user_card_id' => $userCard->id,
            ],
            [
                'card_id' => $userCard->card_id,
                'status' => 'active',
                'is_visible' => true,
                'proof_photo' => $proofFile,
                'proof_verified' => true,
                'proof_status' => 'verified',
                'proof_score' => 100,
            ]
        );
    }

    private function seedActivities(User $user, array $activities): void
    {
        foreach ($activities as [$type, $title, $body, $happenedAt]) {
            Activity::query()->create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'happened_at' => $happenedAt,
                'meta' => ['source' => 'UserSeeder'],
            ]);
        }
    }

    private function putDemoCardImage(string $path, string $title, string $artist, string $accent): void
    {
        Storage::disk('public')->put($path, $this->svgCard($title, $artist, $accent));
    }

    private function putDemoProofImage(string $filename, string $username, string $title): void
    {
        Storage::disk('public')->put('proofs/'.$filename, $this->svgProof($username, $title));
    }

    private function svgCard(string $title, string $artist, string $accent): string
    {
        $safeTitle = e($title);
        $safeArtist = e($artist);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="800" viewBox="0 0 600 800">
  <rect width="600" height="800" rx="36" fill="#fdf6f0"/>
  <rect x="34" y="34" width="532" height="732" rx="28" fill="#ffffff" stroke="#e8d5c0" stroke-width="4"/>
  <rect x="70" y="70" width="460" height="490" rx="24" fill="{$accent}" opacity="0.18"/>
  <circle cx="300" cy="270" r="104" fill="{$accent}" opacity="0.35"/>
  <rect x="165" y="410" width="270" height="42" rx="21" fill="{$accent}" opacity="0.55"/>
  <text x="300" y="620" text-anchor="middle" font-family="Georgia, serif" font-size="32" font-weight="700" fill="#3d2b1f">{$safeTitle}</text>
  <text x="300" y="664" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" font-weight="700" fill="#8B4513" letter-spacing="4">{$safeArtist}</text>
  <text x="300" y="712" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="#b09070">CardFlow demo photocard</text>
</svg>
SVG;
    }

    private function svgProof(string $username, string $title): string
    {
        $safeUsername = e('@'.$username);
        $safeTitle = e($title);
        $date = now()->format('Y-m-d');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="900" height="650" viewBox="0 0 900 650">
  <rect width="900" height="650" fill="#f5ede4"/>
  <rect x="76" y="70" width="330" height="480" rx="28" fill="#fdf6f0" stroke="#d4b896" stroke-width="5"/>
  <rect x="118" y="116" width="246" height="288" rx="20" fill="#8B4513" opacity="0.18"/>
  <text x="241" y="460" text-anchor="middle" font-family="Georgia, serif" font-size="26" font-weight="700" fill="#3d2b1f">{$safeTitle}</text>
  <rect x="470" y="180" width="330" height="230" rx="18" fill="#ffffff" stroke="#d4b896" stroke-width="4"/>
  <text x="635" y="252" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" font-weight="700" fill="#3d2b1f">CardFlow Proof</text>
  <text x="635" y="310" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" fill="#8B4513">{$safeUsername}</text>
  <text x="635" y="366" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" fill="#8B6F5E">{$date}</text>
  <text x="450" y="595" font-family="Arial, sans-serif" font-size="22" fill="#8B6F5E">Demo proof image generated by UserSeeder</text>
</svg>
SVG;
    }
}
