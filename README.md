# Quick CV

## Get Started
- Set up environmental variables in `.env` file. Look at `.env.example` for reference.
- Build the container.
```sh
docker compose build app
```
- Migrate the database if you haven't already.
```sh
docker compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction
```
- Run the container
```sh
docker compose up app
```