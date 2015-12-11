# Les Joies de SUPINFO
Welcome to the public repository for Les Joies de SUPINFO project!

The project is currently hosted at [joies-de-supinfo.s-quent.in](http://joies-de-supinfo.s-quent.in/).

This project is based on Symfony2. But at first, we wrote it in vanilla PHP :
you can check the original sources (in the `vanilla-php` branch), but it is neither used nor maintained anymore.

If you want to contribute, here's how!

## Contribute
Here is a checklist for running a functional project:

* Fork the repository
* Clone it `git clone git@github.com:your_username/Les-Joies-de-Supinfo.git`
* Setup your Symfony environment using this [awesome official documentation doc](http://symfony.com/doc/current/book/installation.html)

With all these things set, you should be able to contribute! If you have any problem, don't hesitate to contact us or
open an issue here on GitHub.

## Technical information
### Cron configuration
To publish gifs without a manual action, a cron is configured on the server to automatically publish accepted gifs.
Cron jobs are set to execute a custom command, `ljds:publish`:

	# Weekdays (twice a day)
	# Morning
	0 11 * * 1-5 /var/www/joies-de-supinfo/app/console ljds:publish
	0 11 * * 1-5 /var/www/joies-de-supinfo/app/console ljds:publish
	# Afternoon
	0 17 * * 1-5 /var/www/joies-de-supinfo/app/console ljds:publish
	
	# Week-end (once a day)
	0 16 * * 6-7 /var/www/joies-de-supinfo/app/console ljds:publish

## API
You can either get the last published gif or a random one by dropping a GET request on the following URLS:

* last: http://joies-de-supinfo.s-quent.in/api/last
* random: http://joies-de-supinfo.s-quent.in/api/random

In both case, you'll receive a JSON-encoded response such as this one :

	{
		caption: "Quand ton CM passe dans l'open space ",
		file: "http://media0.giphy.com/media/phaN2NxXBzHMs/giphy.gif",
		permalink: "http://joies-de-supinfo.s-quent.in/gif/quand-ton-cm-passe-dans-lopen-space-",
		type: "gif"
	}

Depending on the `type` attribute (either `gif`, `webm` or `mp4`), you may want to handle it differently. Please read
[gif.html.twig](src/LjdsBundle/Resources/views/Snippets/gif.html.twig) to see how we handle this.

## Permissions

	cd /path/to/project
	HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
	sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX web/gifs
	sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX web/gifs
