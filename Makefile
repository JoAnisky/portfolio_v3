.PHONY: up down bash logs restart

# Lancer le projet Symfony en dev
up:
	docker compose --env-file .docker/.env.docker.dev up -d --build

# Stopper et nettoyer en dev
down:
	docker compose --env-file .docker/.env.docker.dev down -v

# Entrer dans le conteneur
bash:
	docker exec -it portfoliov3-api bash

# Afficher les logs du conteneur app en dev
logs:
	docker compose --env-file .docker/.env.docker.dev logs -f portfoliov3-api

restart: down up
