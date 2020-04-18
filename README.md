Project created for automating the scanning process ( Amass+Aquatone+Dirscan+Nmap ) in easy to view and use mode.

Screenshots:

![Alt text](/amass.png?raw=true "Amass output example")

![Alt text](/amass2.png?raw=true "Amass output example")

![Alt text](/dirscan1.png?raw=true "Dirscan output example")

![Alt text](/dirscan2.png?raw=true "Dirscan output example")

![Alt text](/vhost.png?raw=true "Vhost output example")

Enjoy.

How to install:

Install docker on your machine.

git clone https://github.com/ultras5631/scaner/

Rename docker-compose.yml.example into docker-compose.yml

Make your changes at env.example (don't rename it!)

Insert your API keys into the amass.ini.example and rename it into amass.ini

Change your api secret&authorization header in the crontab.txt.example (same as in the env.example) and rename it into crontab.txt

If you would like to change Http Basic Auth you need to change the auth string in the env.example AND crontab.txt

Done. Start the site up:

CD into the docker directory and run:

Initial startup command: cd /root/project/docker/ && docker-compose -f docker-compose.yml up && docker cp env.example docker_app_1:/var/www/app/.env && docker exec docker_app_1 chown -R nginx:nginx /configs && docker exec docker_app_1 ln -s /screenshots/ /var/www/app/frontend/web/

Later you can start your project with: docker-compose -f /root/project/docker/docker-compose.yml up -d

Site will be avaliable at http://localhost and default credentials are nginx:Admin and admin@admin.com:admin Phpmyadmin located here: http://localhost:8080 or https://scaner.app/phpmyadmin (add to your /etc/hosts to access the external IP)

Special thanks to these tools developers:
https://github.com/OWASP/Amass
https://github.com/ffuf/ffuf
https://github.com/instrumentisto/nmap-docker-image/projects
https://github.com/michenriksen/aquatone
https://github.com/Bo0oM/ and his fuzz.txt 
https://github.com/honze-net/nmap-bootstrap-xsl
Nmap and Yii Framework's developers.


Tips:
Install docker on fresh ubuntu from 0:

sudo apt-get remove docker docker-engine docker.io containerd runc && sudo apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg-agent \
    software-properties-common && curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

sudo add-apt-repository \
   "deb [arch=amd64] https://download.docker.com/linux/ubuntu \
   $(lsb_release -cs) \
   stable" && sudo apt-get update && sudo apt-get install -y docker-ce docker-ce-cli containerd.io && sudo groupadd docker && sudo usermod -aG docker $USER && newgrp docker 

curl -L https://github.com/docker/compose/releases/download/1.25.4/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose && chmod +x /usr/local/bin/docker-compose && sudo ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose

sudo fallocate -l 1G /swapfile && sudo chmod 600 /swapfile && sudo mkswap /swapfile && sudo swapon /swapfile && echo '/swapfile swap swap sw 0 0' | sudo tee -a /etc/fstab && apt install unzip -y && unzip -K project.zip

Tip:

To trust localhost certificate: sudo security add-trusted-cert \
  -d -r trustRoot \
  -k /Library/Keychains/System.keychain PROJECT/docker/conf/nginx/nginx.crt