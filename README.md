Project created for automating the scanning process ( Amass+Aquatone+Dirscan+Nmap ) in easy to view and use mode.

Enjoy.

How to install:

Install docker
Clone the repository
Rename docker-compose.yml.example to docker-compose.yml
Move env.example to the app/.env
Insert your API keys into the amass.ini.example and rename it to amass.ini
Change your secret in the crontab.txt.example (same as in the app/.env) and rename it into crontab.txt

Done. Start the site up:

CD into the project directory and run:

docker-compose -f docker-compose.yml up -d && docker cp env.example docker_app_1:/var/www/app/.env && docker cp conf/configs/ docker_app_1:/root/ && docker exec docker_app_1 ln -s /screenshots/ /var/www/app/frontend/web/

Done.

Site will be avaliable at http://localhost and default credentials are admin@admin.com:admin

Phpmyadmin located at: http://localhost:8080
