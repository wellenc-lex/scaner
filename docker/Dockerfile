FROM richarvey/nginx-php-fpm:1.10.3

ADD app /var/www/app

RUN rm -rf /etc/nginx/sites-enabled/*

ADD docker/conf/nginx/ /etc/nginx/sites-available/

RUN apk --no-cache add docker sudo libxslt nano

RUN echo "www-data ALL=(ALL) ALL" >> /etc/sudoers && echo "nginx ALL=(ALL) ALL" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/cp" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/docker" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/xsltproc" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/chmod" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/rm" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/mkdir" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/sed" >> /etc/sudoers && echo "Set disable_coredump false" >> /etc/sudo.conf

RUN sed -i "s/pm.max_children = 4/pm.max_children = 100/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/pm.max_spare_servers = 4/pm.max_spare_servers = 100/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 2048M/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/memory_limit = 128M/memory_limit = 2048M/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 2048M/g" "/usr/local/etc/php/php.ini-development" 

RUN ln -s /etc/nginx/sites-available/site.conf /etc/nginx/sites-enabled/site.conf

ADD docker/conf/configs/ /configs/

RUN chown -R nginx:nginx /var/www/app
RUN chmod -R 777 /var/www/app/