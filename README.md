# Les Joies de SUPINFO

Welcome to the public repository for Les Joies de SUPINFO project!

The project is currently hosted at [joies-de-supinfo.fr](https://www.joies-de-supinfo.fr/).

It is built with Symfony 7.4 (LTS). At first, we wrote it in vanilla PHP:
you can check the original sources (in the `vanilla-php` branch), but it is neither used nor maintained anymore.

If you want to contribute, here's how!

## Contribute

Requirements: PHP 8.2+, [Composer](https://getcomposer.org/), [nvm](https://github.com/nvm-sh/nvm) and a MySQL database.

```bash
git clone git@github.com:your_username/Les-Joies-de-SUPINFO.git
cd Les-Joies-de-SUPINFO

# PHP dependencies
composer install

# Front-end assets (Node LTS version is pinned in .nvmrc)
nvm install
npm install
npm run dev     # or `npm run watch`, or `npm run build` for production

# Database
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

Override the values from `.env` in a `.env.local` file:

| Variable                  | Description                                                                 |
|---------------------------|-----------------------------------------------------------------------------|
| `DATABASE_URL`            | Database connection string                                                  |
| `MAILER_DSN`              | Mailer transport, used to notify submitters                                 |
| `MAILER_FROM`             | Sender address of these e-mails                                             |
| `MESSENGER_TRANSPORT_DSN` | Queue for asynchronous e-mails                                              |
| `GIPHY_API_KEY`           | [Giphy](https://developers.giphy.com/) API key, used by the submit page     |
| `ADMIN_PASSWORD`          | Hashed password of the `SupAdmin` user, see `php bin/console security:hash-password` |
| `ADMIN_API_KEY`           | Key used by the admin pages to call `/admin/api`                            |
| `ANALYTICS_TRACKING_CODE` | Analytics tracking code                                                     |

Then start a local server (for instance with the [Symfony CLI](https://symfony.com/download): `symfony serve`).

With all these things set, you should be able to contribute! If you have any problem, don't hesitate to contact us or
open an issue here on GitHub.

## Technical information
### Cron configuration
To publish gifs without a manual action, a cron is configured on the server to automatically publish accepted gifs.
Cron jobs are set to execute a custom command, `ljds:gifs:publish`:

```crontab
# Joies de SUPINFO
# Week days
45 15 * * 1-5 /var/www/joies-de-supinfo/bin/console ljds:gifs:publish

# Weekend
00 14 * * 6-7 /var/www/joies-de-supinfo/bin/console ljds:gifs:publish
```

### E-mails
E-mails are sent asynchronously through Symfony Messenger, so a worker must be running:

```bash
php bin/console messenger:consume async
```

You can check your mailer configuration with `php bin/console ljds:mail:test you@example.com`.

### Sitemap
A sitemap of published gifs and main pages is available at `/sitemap.xml`.

## API
You can get the latest published gifs or a random one by sending a GET request to the following URLs:

* random: https://www.joies-de-supinfo.fr/api/gif/random
* latest 20: https://www.joies-de-supinfo.fr/api/gif/list?count=20 (max. 75)

You'll receive a JSON-encoded response such as this one (`list` returns an array of these):

```json
{
    "caption": "Quand ton CM passe dans l'open space",
    "type": "gif",
    "file": "https://media0.giphy.com/media/phaN2NxXBzHMs/giphy.gif",
    "permalink": "https://www.joies-de-supinfo.fr/gif/quand-ton-cm-passe-dans-lopen-space",
    "publishDate": "2016-03-14 15:45"
}
```

Depending on the `type` attribute (either `gif`, `webm` or `mp4`), you may want to handle it differently. Please read
[gif.html.twig](templates/Snippets/gif.html.twig) to see how we handle this.

## Permissions
Gifs downloaded from the admin are stored in `public/gifs`, which must be writable by the web server:

```bash
cd /path/to/project
HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX public/gifs
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX public/gifs
```
