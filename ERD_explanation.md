# CardFlow ERD Explanation

This document explains the main database tables and how they connect. It uses simple wording for easy understanding.

## Main tables and what they store

- `users`
  - Stores each person who uses the app.
  - Includes their name, email, password, avatar, and login info.

- `cards`
  - Stores details about each card in the system.
  - Includes title, artist, edition, rarity, value, and release information.

- `user_cards`
  - Stores cards that belong to a user.
  - Connects a user to a card and adds ownership details like condition, purchase price, and notes.

- `marketplace_listings`
  - Stores cards that a user is selling or trading.
  - Connects the listing to the owner, the user card, and the card itself.

- `trade_requests`
  - Stores offers sent for a marketplace listing.
  - Tracks who sent the offer, who receives it, which listing it is for, and what card was offered.

- `trades`
  - Stores completed or pending trade records.
  - Keeps the user, the card involved, partner details, and trade status.

- `conversations`
  - Stores chat threads between two users.
  - Can also connect to a marketplace listing if the chat is about a specific listing.

- `messages`
  - Stores individual messages inside a conversation.
  - Includes sender, receiver, text, read status, and deleted state.

- `wishlist_items`
  - Stores cards that users want to find or buy.
  - Connects a user to a card and tracks priority and target price.

- `activities`
  - Stores user activity events.
  - Tracks actions like events, titles, descriptions, and time.

- `saved_views`
  - Stores user-saved search filters or explorer views.
  - Helps users quickly return to searches they like.

- `user_onboardings`
  - Stores steps completed by a new user.
  - Tracks whether they added a card, added a wishlist item, or browsed the marketplace.

- `card_aliases`
  - Stores alternate names for a card.
  - Helps match cards when they have different titles or nicknames.

- `card_variants`
  - Stores variant versions of a card.
  - Includes variant name, type, image, and community data.

- `artists`
  - Stores music artists or groups.
  - Connects artist records to cards and albums.

- `albums`
  - Stores albums linked to artists.
  - Connects albums to cards.

## How the tables connect

- A user can own many cards (`users` -> `user_cards`).
- A card can belong to many users through ownership (`cards` -> `user_cards`).
- A user can create many marketplace listings (`users` -> `marketplace_listings`).
- Each marketplace listing is tied to one owned card (`user_cards` -> `marketplace_listings`).
- Users can send and receive trade requests for listings (`trade_requests`).
- Conversations are between two users and can optionally be about one listing.
- Each conversation can contain many messages.
- Users can add cards to their wishlist (`wishlist_items`).
- Artists can have many albums and cards.
- Albums can include many cards.

## Simple summary

This ERD shows a trading system with:
- users who own cards,
- cards that can be listed for sale or trade,
- offers for cards (`trade_requests`),
- chats between users (`conversations` + `messages`),
- wishlists for cards they want,
- and supporting tables for activity logs, saved filters, onboarding status, card aliases, variants, artists, and albums.

The main idea is that users, cards, and listings are the core, and everything else supports trading, communication, and catalog details.
