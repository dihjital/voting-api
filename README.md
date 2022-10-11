# Voting API

## How to build the server

**Step 1.** - Build

```docker-compose build```

**Step 2.** - Run

```docker-compose up```

**Step 3.** - Migrate DB

```docker-compose run --rm lumen php artisan migrate```

In case you cannot run ```migration```:

```docker-compose down```

```docker-compose run --rm lumen php artisan cache:clean```

```docker-compose run --rm lumen php artisan migrate```

```docker-compose up```

## Access the server
``` curl -v http://localhost:8000/questions```
