# Docker compose configuration for WordPress development

Steps to use:

  * Go into a directory where your `wp-content` lives (or will live).
  * Clone this repo as a sub-directory, named `docker-compose`.
  * Copy the `docker-compose.yaml` one level up.
  * Edit the new `docker-compose.yaml`.
  * Run `docker-compose up` to start it up.

## Stack

  * Theme - your WP theme's repo + composer.json (all optional)
  * WordPress - v4.7 on php7.0 fpm (`wp-content` as volume)
  * MariaDB - v10.1 (db files as volume)
  * Nginx - v1.11 (with custom config)

**NOTE**: You can remove the `aws-theme` container from your yaml file (and the dependency within the `wordpress` container).

## Example

```
$ mkdir test
$ cd test

$ git clone https://github.com/avioli/docker-compose-wp-dev.git docker-compose
$ cp ./docker-compose/docker-compose.yaml .
$ # edit docker-compose.yaml to your desire

$ mkdir db
$ scp ssh://.../db-dump.sql ./db/

$ # you can skip this if you have setup your theme in the yaml file above
$ mkdir wp-content
$ git clone git://.../my-theme wp-content/themes/my-theme

$ docker-compose up
```

Wait about 15-20 seconds and load
[http://localhost:8080](http://localhost:8080) (or whatever you set it up as).

**NOTE**: The very first time you run `docker-compose up` process will download
quite a few layers which will take longer than 15-20 seconds :).

**NOTE**: If you have a huge `.sql` to import, the wordpress container will
timeout, but the db container will import it eventually. When it finishes,
`ctrl-c` the running docker-compose and start it again with
`docker-compose start` (or `up` as before).

**NOTE**: As a result you will end up with the following extra dirs:

  * `wp-content` - the default WordPress plugins and themes will be available
    - plus your theme and any extra plugins, if you've used the `aws-theme` container + `composer.json`
  * `var` - holds your mariaDB files (destroy the db container via `docker-compose down <name_of_dir>_db_1` and remove the `var` dir to re-create upon next launch)
  * `vendor` - only if you've used `composer.json` and the `aws-theme` container

## Shut down

Press `ctrl-c` to shut down the containers.
If that fails and they don't shut down, then issue:

```
$ docker-compose stop
```

## Start up

```
$ docker-compose start
```

This command won't rebuild the containers! If you **do** want to rebuild the containers you should use:

```
$ docker-compose up
$ #or docker-compose up <name_of_dir>_<name_of_container>_1
```

## Destoying the containers

```
$ docker-compose down
```

This will shut down the containers and remove them.

**NOTE**: Any persisted data in the volumes, eg. `wp-content`, `var`, `vendor`, will remain intact.

## Cleanup

To cleanup any dangling volumes docker has left, issue this commnad:

```
$ docker volume ls -qf dangling=true | xargs docker volume rm
```
