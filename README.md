# Les Joies de SUPINFO
Welcome to the public repository for Les Joies de SUPINFO project!

The project is currently hosted at [joies-de-supinfo.s-quent.in](http://joies-de-supinfo.s-quent.in/).

This project is based on Symfony2. But at first, we wrote it in vanilla PHP :
you can check the original sources (in the `vanilla-php` branch), but it is neither used nor maintained anymore.

If you want to contribute, here's how!

## Contribute
Here is a checklist for running a functional project:

* Clone the repository
        `git clone git@github.com:chteuchteu/Les-Joies-de-Supinfo.git`
* Setup your Symfony environment using this [awesome official documentation doc](http://symfony.com/doc/current/book/installation.html)

With all these things set, you should be able to contribute! If you have any configuration problem, don't hesitate to contact us or open an issue here on GitHub.

## Technical information
### Cron configuration
To publish gifs without a manual action, a cron is configured on the server to automatically publish accepted gifs.
Cron jobs are set to execute a script, calling the `/cron/publishCron` route (`AdminController`):

joies-de-supinfo_cron.sh :

    #!/bin/bash
    
    wget http://joies-de-supinfo.s-quent.in/cron/publishCron -q --post-data "admin_api_key=(put the admin api key here)" -O /dev/null

cron jobs :

	# Weekdays (twice a day)
	# Morning
	0 11 * * 1-5 /root/joies-de-supinfo_cron.sh
	# Afternoon
	0 18 * * 1-5 /root/joies-de-supinfo_cron.sh
	
	# Week-end (once a day)
	0 16 * * 6-7 /root/joies-de-supinfo_cron.sh

## API
You can either get the last published gif or a random one by querying the following URLS. In both case, you'll
receive a single JSON-encoded gif:

	{
		caption: "Quand ton CM passe dans l'open space ",
		file: "http://media0.giphy.com/media/phaN2NxXBzHMs/giphy.gif",
		permalink: "http://joies-de-supinfo.s-quent.in/gif/quand-ton-cm-passe-dans-lopen-space-",
		type: "gif"
	}

Depending on the `type` attribute (either `gif` or `mp4`), you may want to treat it differently. Please read
[gif.html.twig](src/LjdsBundle/Resources/views/Gifs/gif.html.twig) to see how we handle this.
