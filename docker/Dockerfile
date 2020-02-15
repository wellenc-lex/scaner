FROM richarvey/nginx-php-fpm
ADD app /var/www/app
RUN rm -rf /etc/nginx/sites-enabled/*
ADD docker/conf/nginx/site.conf /etc/nginx/sites-available/site.conf
RUN ln -s /etc/nginx/sites-available/site.conf /etc/nginx/sites-enabled/site.conf
ADD docker/conf/configs/nmap.xsl /tmp/style.xsl
RUN apk --no-cache add docker sudo libxslt nano
RUN echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/docker" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/xsltproc" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/chmod" >> /etc/sudoers 
RUN sed -i "s/pm.max_children = 4/pm.max_children = 50/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/pm.max_spare_servers = 4/pm.max_spare_servers = 50/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 2048M/g" "/usr/local/etc/php/php.ini-production"