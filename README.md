# rpgconmanager
This application manages registrations, games and tables of the Orcidee Convention.

# Prerequisites
* Have Docker installed

# Installation
* Clone the project
* Run `docker-compose up`

# Import DB
Get a dump file from production and save it in the `db_migrations` folder. Then run the following:
```
docker-compose exec db bash
mysql -u orcidee -p orcidee < ./app/db_migrations/DUMP_FILE.sql
```

# Contribute
* See issues & wiki