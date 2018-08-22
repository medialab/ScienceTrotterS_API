# Science Trotters API

## Build With

* Lumen `5.6` - PHP Framework
*  PostgreSQL - Database system.
* Composer - Packages manager and CLI tools.
* PHP `>=7.2`
* APACHE OR NGINX

## Getting Started

1) Dependencies 

Install all dependencies
```
composer install
```

2) Copy /config/conf.php.default to conf.php and set his configuartions


3) Configuration
Set up Environment Variables in /etc/php/{$php_version}/fpm/pool.d/www.conf
env[POSTGRES_HOST] = your database host
env[POSTGRES_PORT] = your database port
env[POSTGRES_DB] = your database
env[POSTGRES_USER] = your database user
env[POSTGRES_PASSWORD] = your database password
env[POSTGRES_CHARSET] = your database charset



Copy the file `.env.example` and past it to a new file `.env`
Then edit the Postgres configuration.
```
# POSTGRES CONFIG

PG_CONNECTION=
PG_HOST=
PG_PORT=
PG_DATABASE=
PG_USERNAME=
PG_PASSWORD=
PG_CHARSET=
PG_PREFIX=
PG_SCHEMA=
```

4) Create Database On server


5) To populate the Database

go to the website root	
execute the command: php tng


6) Nginx Vhost

server {
	listen 80;
	listen [::]:80;

	server_name api-sts.actu.com;
	root {$site_path}/api/public;
	index index.html index.htm index.nginx-debian.html index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
	
		# With php7.2-cgi alone:
		#fastcgi_pass 127.0.0.1:9000;
		# With php7.2-fpm:
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:{$php_soket};
	}

    listen [::]:443 ssl; # managed by Certbot
    listen 443 ssl; # managed by Certbot
    ssl_certificate {$ssl_path}/live/api-sts.actu.com/fullchain.pem; # managed by Certbot
    ssl_certificate_key {$ssl_path}/live/api-sts.actu.com/privkey.pem; # managed by Certbot
    include {$ssl_path}/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam {$ssl_path}/ssl-dhparams.pem; # managed by Certbot

}


7) Usage

The main entry url is `your_site_name.your_domain_name/public`

## Docker deployment

Build the PHP container
```bash
docker build -t phpcomposer .
```

Start containers
```bash
docker-compose  up
```

Initialize depedencies + databse intialisation
```bash
./setup_php_db.sh 
```

The API is served at htt://localhost:5000
Configuration to be added later.
 

## License
This project is licensed under the GPLv2 License - see the [GPLv2-LICENSE.md](https://github.com/medialab/ScienceTrotterS_API/GPLv2-LICENSE.md) file for details