# Basic Nginx configuration

The `conf.d` directory is mounted as Nginx's `/etc/nginx/conf.d`.

Add any configuration you may need -- all `.conf` files are automatically
included by nginx.

To check nginx conf and restart the container after conf changes:

```sh
docker-compose exec nginx nginx -t && docker-compose restart nginx
```
