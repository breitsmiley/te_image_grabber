# Get Started

### Run dev in dev mode (USUAL RUN)
```bash
docker-compose up -d te-nginx


docker-compose up -d te-node
docker-compose exec te-node sh
npm install
npm run watch
```

### te-php - Useful commands 
```bash
// 0. Live logs
//----------------------------
docker-compose logs -f te-php

// 1. GO into container
//----------------------------
docker-compose exec te-php sh


// 2. Update Dependencies
// ------------------------------
composer install

// 3. Migrations + fixtures
// ------------------------------
php bin/console doctrine:database:drop --force \
&& php bin/console doctrine:database:create \
&& php bin/console doctrine:migrations:migrate

// 4. Cleaning Chache 
// ------------------------------
php bin/console doctrine:cache:clear-query \
&& php bin/console doctrine:cache:clear-result \
&& php bin/console doctrine:cache:clear-metadata \
&& php bin/console cache:clear --no-warmup \
&& php bin/console cache:warmup \
&& php bin/console assets:install \
&& echo "DONE";
```

### te-node - Useful commands 
```bash
// 0. Live logs
//----------------------------
docker-compose logs -f te-node

// 1. GO into container
//----------------------------
docker-compose exec te-node sh

// 2. Update Dependencies
// ------------------------------
npm install

// 3. Encore Webpack https://symfony.com/doc/master/frontend/encore/simple-example.html
// ------------------------------
npm run dev
npm run watch
npm run build
```









