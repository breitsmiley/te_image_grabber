# Get Started

## Hosts
| Domain                  | Description         |
| -------------           |:------------------:|
| localhost               | image grubber    |

## Directories
| Directory                 | Description         |
| -------------             |:------------------:|
| apps/te                   | Symfony |
| docker                    | docker assets |

## First time run
1. Start containers
```bash
docker-compose up -d
```
2. Goto php container => install php dependencies
```bash
docker-compose exec te-php sh
composer install
exit
```

3. Install NPM dependencies and building assets 
```bash
docker-compose exec te-node sh
npm install
npm run dev
exit
```

## DEV run
1. Start containers
```bash
docker-compose up -d
```
2. Run webpack watcher
```bash
docker-compose exec te-node sh
npm run watch
```

## Good commands
find ./public/image_storage/ ! -name '.gitignore' -type f -exec rm -f {} +

