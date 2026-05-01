Setup Initial Docker
cp .env.example .env
docker compose up --build -d
docker compose exec app php artisan key:generate
Tests
docker compose exec app composer test
Commande équivalente détaillée:
docker compose exec app php artisan config:clear
docker compose exec app composer test:lint
docker compose exec app php artisan test
Test ciblé:
docker compose exec app php artisan test tests/Feature/Features/Onboarding/Http/RegisterEndpointTest.php
Test ciblé par nom:
docker compose exec app php artisan test --filter it_registers_a_user
Base De Données
Migration fresh normale:
docker compose exec app php artisan migrate:fresh --force
Vérifier que le schema dump est utilisé:
docker compose exec app php artisan migrate:fresh --force
Docker Local
Démarrer toute la stack:
docker compose up --build -d
Démarrer seulement les services utiles au backend:
docker compose up -d postgres redis minio app nginx
Voir les logs:
docker compose logs -f app nginx postgres redis
Entrer dans le conteneur app:
docker compose exec app sh
Stopper:
docker compose down
Supprimer les volumes locaux:
docker compose down -v
À faire une fois si ton volume Postgres existait avant l'ajout de la DB de test:
docker compose down -v && docker compose up --build -d
API Locale
curl <http://localhost:8001/up>
curl <http://localhost:8001/api/health>
Lint / Format
Check format:
docker compose exec app composer test:lint
Corriger format:
docker compose exec app composer lint
Avant Push
git status --short
docker compose exec app composer test
docker compose exec app php artisan migrate:fresh --force
Fix Pour Ton Warning .env
À faire une fois à la racine backend:
cp .env.example .env
Puis:
docker compose exec app php artisan key:generate
