# WordPress helpers

This whole directory is mounted at `/var/www/html/wp-dev`.

## WP-dev

The file `wp-dev.php` is included at the top of the auto-generated
`wp-config.php`.

## Fetch

If you have a live/staging instance of a WordPress website and want to work
locally, after you get the source files and database, you might be missing
some media files. These files could easily be gigabytes of data.

This script downloads them on a per-request basis, so if a page require one
and it is not found locally, this script will be invoked.

It only fetches files that are year/month based:
http://domain.com/wp-content/uploads/YYYY/MM/filename.ext

To enable - ensure you've got `FETCH_ENABLED: 1` and
`FETCH_REMOTE_SERVER_UPLOADS_URL: ""` is not empty in your
`docker-compose.yaml`.

## New User

To create a new admin user visit `http://localhost:8080/wp-dev/new-user.php`.