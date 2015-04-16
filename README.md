# Les Joies de Supinfo
Welcome to the public repository for Les Joies de Supinfo project!

If you want to contribute, here's how!

## Contribute
Here is a checklist for running a functional project:

* Clone the repository
        `git clone git@github.com:chteuchteu/Les-Joies-de-Supinfo.git`
* Please be sure that `short_open_tag` PHP configuration item is set to "On":
    * **Windows** (Wamp):
    Open `C:\wamp\bin\apache\apache*\bin\php.ini`, find `short_open_tag` property and switch it from `Off` to `On`. Finally, restart Wamp (left click on the icon in the task bar, "Restart all services")
    * **Linux**:
    Open `/etc/php5/apache2/php.ini` file, find and set the `short_open_tag` property, and `(sudo) service apache2 reload`

* You also need to enable `mod_rewrite` apache module: run `(sudo) a2enmod rewrite`, then `(sudo) service apache2 restart`.

* By default, on Linux, apache doesn't read `.htaccess` files. To enable this, edit the `/etc/apache2/sites-available/default` file (may change depending on the vhost you're using),
and replace `AllowOverride None` with `AllowOverride All`. You'll have to reload apache configuration in order to apply changes: `(sudo) service apache2 reload` (a restart is not needed).

* Copy `ljs-config-sample.php` to `ljs-config.php`. There are some configuration items that has to be set in order to make things work:
    * `WEBSITE_URL` should be the project absolute URL prefix. `http://localhost/` should be fine, except if you run it in a subdirectory or if apache listens on a different port.
    * `PRETTY_URL` should be set to `false` if you're running the project on Wamp. There has indeed been some compatibility issues with those on Windows.
    * `MYSQL_HOST`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD` **must** be set according to your local configuration.

    The other configuration keys are optionnal and can be left as-is.

* Execute the `create-tables.sql` script on a new database (the one you set as `MYSQL_DATABASE` configuration value)

* Please be sure that the `/uploads/` directory is writable by the apache user. On Linux, you can `cd`, `chown www-data:www-data ./`, and if necessary `chmod u+w ./uploads/`.

With all these things set, you should be able to contribute! If you have any configuration problem, don't hesitate to contact us or open an issue here on GitHub.
