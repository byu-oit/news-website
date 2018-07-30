Config tools (Config push)
===========
The module let's you track changes in your configuration in production to be
to a seperate git repo. When you have that in place you could compare your local
sync directory with the directory (git repo) that is tracking you changes like:
`meld sync config-extras/mysite-prod-conf` (suing meld to compare).


Requirements
============
* Git installed localy and on production.
* A dedicated configuration repo (not the same as sync)
* A git user with ssh keys

Setup
=====
Create a git repo to store a copy of your configuration. Put it outside your web
root, like:

```
├── web
│   ├── autoload.php
│   ├── core
│   ├── index.php
│   ├── profiles
│   ├── robots.txt
│   ├── sites
config-extras
└── mysite-prod-conf
```

Here I created a folder called config-extras an in that I created a git repo in
the folder mysite-prod-conf.

I also pushed that repo to a git repository, and added a user to it. For security
reason - create a new git user that only has access to this repo. That user
should not have access to your normal site-repo.


Modules
=======
To work with this, you need to enable `config_tools` and the sub modules
`git_config` and `config_files`. Add the needed settings in the setup at:
/admin/config/development/configuration/config-tools

Recommendation
==============
You should not track changes in config in your development environment after
the initial setup (all your config is written to `mysite-prod-conf` after that),
to not do so, disable the config push functionality, preferably in
settings.local.php.
Like:
```
$config['config_tools.settings']['disabled'] = 1;
```
You could also disable config pushing in th Drupal UI, but when you need
something like config_split (recommended) or config_ignore to not have the module
active locally after the first installation.

