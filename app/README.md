
# scaner
Project created in order to help in recon while pentesting by automating the scanning process && making comfortable to read output.

Made with Yii2 && mysql && love.

Tools used: Nmap, Amass, Aquatone, Dirsearch, git-all-secrets, Vhost scanner, Shodan, race-the-web. (Big thanks 4 authors!)

Output overview:

![Alt text](/img1.png?raw=true)
![Alt text](/img2.png?raw=true)
![Alt text](/img3.png?raw=true)
![Alt text](/img4.png?raw=true)
![Alt text](/img5.png?raw=true)

Any help highly appreciated. 

How to run && install (ubuntu): copy the soft dir to /var/www/soft, install the needed apt && tools requirements && composer extensions.

<h2> Apt (clean system): </h2>

apt-get update && apt-get full-upgrade -y && apt-get install build-essential -y && apt-get install php-fpm php-mysql nginx nmap zip mysql-server -y && sudo apt install apt-transport-https ca-certificates curl --insecure  software-properties-common && curl --insecure  -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add - && sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu bionic stable" && sudo apt update && sudo apt install docker-ce -y && snap install amass && sudo mysql_secure_installation && sudo apt-get install python3.6 -y  && apt-get install xsltproc chromium-browser phpmyadmin python-certbot-nginx -y
<p></p>

cd /var/www/soft/linkfinder/ && python /var/www/soft/linkfinder/setup.py install && apt install python3-pip -y && pip3 install -r /var/www/soft/vhost/requirements.txt  && cd /var/www/soft/race && snap install go --classic && export GOPATH=/var/www/soft/race/ && echo "export GOPATH=/var/www/soft/race/" >> ~/.bash_profile && go get github.com/lyft/protoc-gen-validate/ && go get ./... 

<p></p>

unzip www.zip -d / && unzip nginx.zip -d / && unzip php.zip -d / && rm /etc/nginx/sites-enabled/default  && chown -R www-data:www-data /var/www && cd /var/www/soft/race/ && make build && mv race-the-web2.0.1-2-g69df0ab /bin/race && wget https://github.com/michenriksen/aquatone/releases/download/v1.4.2/aquatone_linux_amd64_1.4.2.zip && unzip aquatone_linux_amd64_1.4.2.zip && mv aquatone /usr/local/bin/aquatone

<h2>Visudo config: </h2>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /var/www/soft/race/bin/amass </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/nmap </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /bin/mv </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/xsltproc </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/find </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/docker </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /usr/local/bin/aquatone </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /bin/mkdir </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/xsltproc </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/find </p>
<p>www-data ALL=(ALL:ALL) NOPASSWD: /bin/nohup /bin/race </p>


<h2>Crontab:</h2>

<p>* * * * *  curl --insecure  https://localhost.soft/verify/active --data "secret=keyfrom common/config/main-local" >/dev/null 2>&1</p>
<p>0 5,17 * * * curl --insecure  https://localhost.soft/verify/passive --data "secret=keyfrom common/config/main-local" >/dev/null 2>&1</p>
<p>*/10 * * * * curl --insecure  https://localhost.soft/passive/index --data "secret=keyfrom frontend/config/params-local" >/dev/null 2>&1</p>
<p>* * * * * curl --insecure  https://localhost.soft/verify/queue --data "secret=keyfrom common/config/main-local" >/dev/null 2>&1</p>
<p>*/40 * * * * docker system prune -f</p>
<p>* * * * * renice -n -18 -p $(pgrep ^nmap$)</p>
<p>@reboot /bin/race</p>
<p>@reboot sleep 10 && renice -n -20 -p $(pgrep ^race$)</p>

<h2> Nginx: </h2>

    server {
            listen 80 default_server;
            listen [::]:80 default_server;

            server_name dev.localhost.soft localhost.soft;

            if ($host = dev.localhost.soft) {
                return 301 https://$host$request_uri;
            } # managed by Certbot


            if ($host = localhost.soft) {
                return 301 https://$host$request_uri;
            } # managed by Certbot

            location ~ /\. {
                deny all;
            }
            root /var/www/html/scaner/frontend/web;

            index index.php;

            location ~ \.php$ {
                    include snippets/fastcgi-php.conf;
                    fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
            }

    }

    server {
            root /var/www/html/scaner/frontend/web;

            location ~ /\. {
                deny all;
            }

            charset utf-8;
            client_max_body_size 128M;
            index index.php;
            server_name localhost.soft; # managed by Certbot

            location / {
                    try_files $uri $uri/ /index.php?$args;
            }

            location ~ \.php$ {
                    include snippets/fastcgi-php.conf;
                    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                    fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
            }

            location /phpmyadmin {
          root /usr/share/;
          index index.php;
          try_files $uri $uri/ =404;

          location ~ ^/phpmyadmin/(doc|sql|setup)/ {
            deny all;
          }

          location ~ /phpmyadmin/(.+\.php)$ {
            fastcgi_pass unix:/run/php/php7.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
            include snippets/fastcgi-php.conf;
          }
         }

        listen [::]:443 ssl ipv6only=on default_server; # managed by Certbot
        listen 443 ssl default_server; # managed by Certbot
        ssl_certificate /etc/letsencrypt/live/localhost.soft-0002/fullchain.pem; # managed by Certbot
        ssl_certificate_key /etc/letsencrypt/live/localhost.soft-0002/privkey.pem; # managed by Certbot
        include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
        ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot

        }

    server {
      root /var/www/html/scanerdev/frontend/web;

      location ~ /\. {
        deny all;
      }

      charset utf-8;
      client_max_body_size 128M;
      index index.php;
      server_name dev.localhost.soft;

      location / {
              auth_basic "Private Property";
              auth_basic_user_file /etc/nginx/.htpasswd;              
              try_files $uri $uri/ /index.php?$args;
      }

      location ~ \.php$ {
         include snippets/fastcgi-php.conf;
         fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
         fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
         fastcgi_read_timeout 300;
    }

      location /phpmyadmin {
          root /usr/share/;
          index index.php;
          try_files $uri $uri/ =404;

          location ~ ^/phpmyadmin/(doc|sql|setup)/ {
            deny all;
      }

      location ~ /phpmyadmin/(.+\.php)$ {
        fastcgi_pass unix:/run/php/php7.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        include snippets/fastcgi-php.conf;
      }
     }

        listen 443 ssl; # managed by Certbot
        ssl_certificate /etc/letsencrypt/live/localhost.soft-0002/fullchain.pem; # managed by Certbot
        ssl_certificate_key /etc/letsencrypt/live/localhost.soft-0002/privkey.pem; # managed by Certbot
        include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
        ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot

    }

    server {
     listen 80;
     server_name _;
     return 404;
    }

    server {
     listen 443 ssl;
     server_name _;
     return 404;
    }


