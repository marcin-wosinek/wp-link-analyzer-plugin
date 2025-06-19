Repository template for our packages

# Usage
When creating a new repository for a package or a plugin, select this
repository as the template. It will initialize the new repository with all the
structure & files contained in the template.

## Starting

For local testing, you can use the docker compose set up:

```
docker compose up
```

This will provide following HTTP access:
* http://localhost:8080 — WordPress instance where you can activate the plugin,
* http://localhost:8081 — PHPMyAdmin,

To use wp-cli in this configuration, you can use:
```
docker compose run --rm <command to run>
```

For example:
```
docker compose run --rm wpcli wp core version
```

# Get started
- Have a mysql DB ready and a user — for example, `docker compose up`
- Have `svn` installed.
- Run `composer install`
- Run `bash bin/install-wp-tests.sh exampledb exampleuser examplepass localhost latest true`
- Run `composer run-tests`
- Run `composer phpcs`
- You can install the plugin on your website.

# Content
* `bin/install-wp-tests.sh`: installer for WordPress tests suite
* `.editorconfig`: config file for your IDE to follow our coding standards
* `.gitattributes`: list of directories & files excluded from export
* `.gitignore`: list of directories & files excluded from versioning
* `.travis.yml`: Travis-CI configuration file
* `composer.json`: Base composer file to customize for the project
* `LICENSE`: License file using GPLv3
* `phpcs.xml`: Base PHP Code Sniffer configuration file to customize for the project
* `README.md`: The readme displayed on Github, to customize for the project
