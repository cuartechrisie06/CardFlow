<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Card Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f3eee7;
            color: #2b2b2b;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
            padding: 20px;
            gap: 24px;
        }

        .sidebar {
            width: 210px;
            background: linear-gradient(to bottom, #a27b66, #c2a38f);
            border-radius: 22px;
            padding: 20px 16px;
            color: #fff;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e7d5c5;
        }

        .brand-title {
            font-size: 14px;
            font-weight: bold;
        }

        .brand-subtitle {
            font-size: 10px;
            opacity: 0.8;
        }

        .menu {
            list-style: none;
        }

        .menu li {
            padding: 12px 14px;
            border-radius: 14px;
            margin-bottom: 6px;
            font-size: 13px;
            color: #f7eee8;
        }

        .menu li.active {
            background: rgba(255, 255, 255, 0.22);
        }

        .collector {
            margin-top: 32px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 12px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .collector-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #d5b9a4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: bold;
        }

        .collector small {
            display: block;
            opacity: 0.8;
            font-size: 9px;
            letter-spacing: 1px;
        }

        .collector span {
            font-size: 12px;
            font-weight: bold;
        }

        .main-content {
            flex: 1;
            background: #f8f3ec;
            border-radius: 6px;
            padding: 26px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 26px;
        }

        .page-title {
            color: #9a634a;
            letter-spacing: 5px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .back-btn {
            border: none;
            background: #fff;
            color: #7b5f4c;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 12px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .details-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 28px;
            display: grid;
            grid-template-columns: 45% 1fr;
            gap: 34px;
            max-width: 960px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.04);
        }

        .image-box {
            background: #063237;
            border-radius: 10px;
            overflow: hidden;
            height: 520px;
        }

        .image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .info-list {
            padding-top: 4px;
        }

        .info-row {
            border-bottom: 1px solid #e8ded5;
            padding: 13px 0;
        }

        .info-label {
            font-size: 9px;
            font-weight: bold;
            color: #a47761;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 13px;
            font-weight: bold;
            color: #2c2c2c;
        }

        .edit-btn {
            position: fixed;
            right: 36px;
            bottom: 36px;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #7a6148;
            color: white;
            border: none;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(0,0,0,0.18);
        }

        @media (max-width: 900px) {
            .app-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .details-card {
                grid-template-columns: 1fr;
            }

            .image-box {
                height: 360px;
            }
        }
    </style>
</head>
<body>

<div class="app-container">

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon"></div>
            <div>
                <div class="brand-title">CardFlow</div>
                <div class="brand-subtitle">Photocard Trading</div>
            </div>
        </div>

        <ul class="menu">
            <li>Dashboard</li>
            <li class="active">My Collection</li>
            <li>Marketplace</li>
            <li>Wishlist</li>
            <li>Messages</li>
            <li>Explore</li>
            <li>Insights</li>
        </ul>

        <div class="collector">
            <div class="collector-avatar">C</div>
            <div>
                <small>COLLECTOR</small>
                <span>Chrisie</span>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Card Details</h1>
            <a href="{{ route('cards.index') }}" class="back-btn">← Back to Collection</a>
        </div>

        <section class="details-card">
            <div class="image-box">
                <img src="{{ asset('images/spotify-card.png') }}" alt="Card Image">
            </div>

            <div class="info-list">
                <div class="info-row">
                    <div class="info-label">Artist / Group</div>
                    <div class="info-value">BTS</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Card Title</div>
                    <div class="info-value">JIMIN</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Album</div>
                    <div class="info-value">2020</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Edition</div>
                    <div class="info-value">V4</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Rarity</div>
                    <div class="info-value">Mint</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Market Value</div>
                    <div class="info-value">PHP 100.00</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Purchase Price</div>
                    <div class="info-value">PHP 100.00</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Estimated Value</div>
                    <div class="info-value">PHP 100.00</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Condition</div>
                    <div class="info-value">Good</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Acquired At</div>
                    <div class="info-value">01/07/2022</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Notes</div>
                    <div class="info-value">Condition details, source, trade notes...</div>
                </div>

                <div class="details-card">
    <div class="image-box">
        <!-- Display card image -->
        <img src="{{ asset('storage/cards/'.$card->thumbnail_style) }}" alt="{{ $card->title }}">
    </div>

    <div class="info-list">
        <div class="info-row">
            <div class="info-label">Artist / Group</div>
            <div class="info-value">{{ $card->artist }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Card Title</div>
            <div class="info-value">{{ $card->title }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Edition</div>
            <div class="info-value">{{ $card->edition }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Album</div>
            <div class="info-value">{{ $card->album }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Rarity</div>
            <div class="info-value">{{ $card->rarity }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Market Value</div>
            <div class="info-value">PHP {{ number_format($card->market_value, 2) }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Released On</div>
            <div class="info-value">{{ $card->released_on->format('F j, Y') }}</div>
        </div>

        <!-- You can display any other attributes you need here -->
    </div>
</div>
            </div>
        </section>
    </main>

</div>

<button class="edit-btn">✎</button>

</body>
</html>