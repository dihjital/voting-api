# Voting API

## How to build the server

**Step 1.** - Build

```docker-compose build```

**Step 2.** - Run

```docker-compose up```

**Step 3.** - Migrate DB

```docker-compose run --rm lumen php artisan migrate```

## Access the server
``` curl -v http://localhost:8000/questions```
