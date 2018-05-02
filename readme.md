# Science Trotters API

## Build With

* Lumen `5.6` - PHP Framework
*  PostgreSQL - Database system.
* Composer - Packages manager and CLI tools.
* PHP `>=7.2`
* APACHE OR NGINX

## Getting Started

1) Prerequisites
First of all you need an installation of Node JS.
https://getcomposer.org/

2) Dependencies 

Install all dependencies
```
composer install
```
3) Configuration

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

4) Usage

The main entry url is `your_site_name.your_domain_name/public`


## License
This project is licensed under the GPLv2 License - see the [GPLv2-LICENSE.md](https://github.com/medialab/ScienceTrotterS_API/GPLv2-LICENSE.md) file for details

