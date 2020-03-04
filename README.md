Project created for automating the scanning process ( Amass+Aquatone+Dirscan+Nmap ) in easy to view and use mode.

Screenshots:



Enjoy.

How to install:

Install docker on your machine.

git clone https://github.com/ultras5631/scaner/

Rename docker-compose.yml.example into docker-compose.yml

Move env.example at app/.env

Insert your API keys into the amass.ini.example and rename it into amass.ini

Change your secret in the crontab.txt.example (same as in the app/.env) and rename it into crontab.txt

Done. Start the site up:

CD into the docker directory and run:

Initial startup command: cd PROJECTDIR/DOCKER/ && docker-compose -f docker-compose.yml up -d && docker cp env.example docker_app_1:/var/www/app/.env && docker cp conf/configs/ docker_app_1:/ && docker exec docker_app_1 chown -R nginx:nginx /configs && docker exec docker_app_1 ln -s /screenshots/ /var/www/app/frontend/web/

Later you can start your project with docker-compose -f PROJECTDIR/DOCKER/docker-compose.yml up -d
Done.

Special thanks to these tools developers:
https://github.com/OWASP/Amass
https://github.com/ffuf/ffuf
https://github.com/instrumentisto/nmap-docker-image/projects
https://github.com/michenriksen/aquatone
https://github.com/Bo0oM/ and his fuzz.txt 
https://github.com/honze-net/nmap-bootstrap-xsl
Nmap and Yii Framework's developers.

Site will be avaliable at http://localhost and default credentials are admin@admin.com:admin

Phpmyadmin located at: http://localhost:8080
