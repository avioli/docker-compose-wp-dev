# Docker compose configuration for WordPress development

Steps to use:

  * Go into a directory where your `wp-content` lives (or will live).
  * Clone this repo as a sub-directory, named `docker-compose`.
  * Copy the `docker-compose.yaml` one level up.
  * Edit the new `docker-compose.yaml`.
  * Run `docker-compose up` to start it up.

## Example

```
$ mkdir test
$ cd test

$ mkdir wp-content
$ git clone git://.../my-theme wp-content/themes/my-theme

$ git clone https://github.com/avioli/docker-compose-wp-dev.git docker-compose
$ cp ./docker-compose/docker-compose.yaml .
$ # edit docker-compose.yaml to your desire

$ mkdir db
$ scp ssh://.../db-dump.sql ./db/

$ docker-compose up
```

Wait about 15-20 seconds and load
[http://localhost:8080](http://localhost:8080) (or whatever you set it up as).

**NOTE**: The very first time you run `docker-compose up` docker will download
quite a few layers which will take longer than 15-20 seconds :).

**NOTE**: If you have a huge `.sql` to import, the wordpress container will
timeout, but the db container will import it eventually. When it finishes,
`ctrl-c` the running docker-compose and start it again with
`docker-compose start` (or `up` as before).

## Shut down

Press `ctrl-c` to shut down the containers.
If that fails and they don't shut down, then issue:

```
$ docker-compose stop
```


## Destoying the containers

```
$ docker-compose down
```

## Cleanup

To cleanup any dangling volumes docker has left, issue this commnad:

```
$ docker volume rm (docker volume ls -qf dangling=true)
```
