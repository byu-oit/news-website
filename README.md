# BYU News Site

## Setting up your development environment.

1. Obtain production database and files folder.
2. Clone the repo: `git clone git@github.com:byu-oit/news-wbsite`
3. `cd news-website`
4. Update the submodules.
```bash
git submodule init && git submodule update --recursive
```
5. Copy the settings file. `cp web/sites/default/default.settings.php web/sites/default/settings.php`
6. Make a folder for the database. `mkdir mariadb-init`
7. Move your production database export into `mariadb-init`.
8. Extract the files folder into web/sites/default/files/.
9. Update your hosts files to point *news-local.byu.edu* to 127.0.0.1.
10. Bring up the stack by running `make up`.
11. Drop into your container. `docker exec -it news-website_php bash`
12. Run `composer install`.
13. Make settings.php writable by the web server. `sudo chown www-data:www-data web/sites/default/settings.php`
14. Navigate to *news-local.byu.edu:8000* and run through the setup. Database connection information can be found in .env.

## Useful commands

- `make up`: Bring up the stack.
- `make down`: Tear down the stack.
- `make drush "[cmd]"`: Run drush command.
