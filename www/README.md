# Knives and Sprockets Resteraunt Site

This is an example wordpress site designed to allow the simplest way to get a restaraunt up and running.

## The docker way

This is mainly meant for developers contributing.

From a terminal:

```sh
docker-oompose build
docker-compose up -d
docker-compose exec wp composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer
```

Then load the browser up to <http://localhost:8000> and run through wordpress install. Then activate the woo-commerce plugin and the restaraunt plugin.

## TODO

* move composer.json to the root
* Figure out how to install the plugins during the docker build
* COnfigure the plugins from a script.
