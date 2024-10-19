Project created for automating the scanning process ( Amass+Aquatone+FFUF+Nmap ) in easy to view and use mode.

Screenshots:

![Alt text](/images/amass.png?raw=true "Amass output example")

![Alt text](/images/amass2.png?raw=true "Amass output example")

![Alt text](/images/dirscan1.png?raw=true "Dirscan output example")

![Alt text](/images/dirscan2.png?raw=true "Dirscan output example")

![Alt text](/images/vhost.png?raw=true "Vhost output example")

Enjoy.

How to install:

Install docker on your machine.

git clone https://github.com/wellenc-lex/scaner/

Rename docker-compose.yml.example into docker-compose.yml

Move env.example at app/.env

Insert your API keys into the amass1.ini.example and rename it into amass1.ini

Change your secret in the crontab.txt.example (same as in the app/.env) and rename it into crontab.txt

Done. Start the site up:

CD into the docker directory and run:

WINDOWS USERS SHOULD SET THAT VARIABLE before running the docker-compose file: `set COMPOSE_CONVERT_WINDOWS_PATHS=1`

Initial powershell startup command: not recommended - poor performance!
(set COMPOSE_CONVERT_WINDOWS_PATHS=1) ; (docker-compose --project-name docker --compatibility -f docker-compose.yml.windows up -d --no-deps --build --remove-orphans) ; (docker cp env.example docker_app_1:/var/www/app/.env) ; (docker cp docker/conf/configs/ docker_app_1:/) ; (docker exec docker_app_1 chmod -R 777 /configs) ; (docker exec docker_app_1 chmod -R 777 /dockerresults) ; (docker exec docker_app_1 chmod -R 777 /ffuf) ; (docker exec docker_app_1 chmod -R 777 /jsa) ; (docker exec docker_app_1 chmod -R 777 /httpxresponses) ; (docker exec docker_app_1 chmod -R 777 /screenshots) ; (docker exec docker_app_1 chmod -R 777 /nmap) ; (docker exec docker_app_1 chmod -R 777 /nuclei) ; (docker exec assetdb_postgres psql postgres://postgres:postgres@127.0.0.1:5432 -c "CREATE DATABASE assetdb;") ; (docker exec assetdb_postgres psql postgres://postgres:postgres@127.0.0.1:5432 -c "ALTER DATABASE assetdb SET TIMEZONE to 'UTC'; CREATE EXTENSION IF NOT EXISTS pg_trgm SCHEMA public;")

Linux: cd PROJECTDIR/DOCKER/ && docker-compose --project-name docker --compatibility -f docker-compose.yml up -d --no-deps --build --remove-orphans && docker cp env.example docker_app_1:/var/www/app/.env && docker cp conf/configs/ docker_app_1:/ && docker exec docker_app_1 chmod -R 777 /configs && docker exec docker_app_1 chmod -R 777 /dockerresults && docker exec docker_app_1 chmod -R 777 /ffuf && docker exec docker_app_1 chmod -R 777 /jsa && docker exec docker_app_1 chmod -R 777 /httpxresponses && docker exec docker_app_1 chmod -R 777 /screenshots && docker exec docker_app_1 chmod -R 777 /nuclei 
docker exec docker_app_1 /bin/bash -c "cd /configs/nuclei-templates/ && git fetch origin main && git reset --hard origin/main && git clean -df"

Later you can start your project with docker-compose -f PROJECTDIR/DOCKER/docker-compose.yml up -d

Site will be avaliable at http://localhost and nginx creds are nginx:Admin, default site credentials are admin@admin.com:admin and phpmyadmin is here: https://scaner.local/phpmyadmin/

Special thanks to these tools developers:
https://github.com/OWASP/Amass
https://github.com/ffuf/ffuf
https://github.com/instrumentisto/nmap-docker-image/projects
https://github.com/michenriksen/aquatone
https://github.com/Bo0oM/ and his fuzz.txt 
https://github.com/honze-net/nmap-bootstrap-xsl
Nmap and Yii Framework's developers.




