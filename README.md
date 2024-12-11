# Chronos Backend

Chronos is a modern, feature-rich news aggregator designed to provide users with a personalized and seamless browsing
experience. Powered by advanced technologies like Laravel, Meilisearch, and React, it curates articles from diverse
sources, including trusted outlets like The Guardian, New York Times, and NewsAPI, all tailored to the user’s
preferences. With its clean, mobile-responsive design, Chronos allows users to filter news by keywords, categories,
sources, and dates while delivering a fast, real-time search experience. It also empowers users to customize their feeds
by selecting preferred sources, authors, and topics, making it the perfect platform to stay informed in an ever-evolving
world.

## How to run

1. `composer install --ignore-platform-reqs`
2. `./vendor/bin/sail up --build -d`
3. `./vendor/bin/sail artisan migrate`
4. `./vendor/bin/sail artisan articles:fetch`

## Test

`./vendor/bin/sail artisan test` (83.3% coverage)

## Technologies

- Laravel - Full Stack Framework
- Redis - In-Memory store
- Meilisearch - Full Text Search
- Sanctum - Authentication library
- Eloquent - Database ORM and access layer
- PHPUnit - Testing framework
- Sail - Containerization
