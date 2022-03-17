FROM richarvey/nginx-php-fpm:1.10.3

COPY --from=golang:alpine /usr/local/go/ /usr/local/go/
 
ENV PATH="/usr/local/go/bin:/root/go/bin:${PATH}"

ENV GOPATH="/root/go/"

RUN go install github.com/ffuf/ffuf@latest 

RUN mv $GOPATH/bin/ffuf /tmp/ffuf.binary && chown nginx:nginx /tmp/ffuf.binary

ADD app /var/www/app

RUN rm -rf /etc/nginx/sites-enabled/*

ADD docker/conf/nginx/ /etc/nginx/sites-available/

ADD docker/conf/nginx/nginx.conf /etc/nginx/nginx.conf

RUN echo "www-data ALL=(ALL) ALL" >> /etc/sudoers && echo "nginx ALL=(ALL) ALL" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/cp" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/docker" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/xsltproc" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/chmod" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/rm" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/mkdir" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/sed" >> /etc/sudoers && echo "Set disable_coredump false" >> /etc/sudo.conf

RUN sed -i "s/pm.max_children = 4/pm.max_children = 2000/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/pm.max_spare_servers = 4/pm.max_spare_servers = 100/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 4096M/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/memory_limit = 128M/memory_limit = 4096M/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 4096M/g" "/usr/local/etc/php/php.ini-development" 

RUN ln -s /etc/nginx/sites-available/site.conf /etc/nginx/sites-enabled/site.conf

ADD docker/conf/configs/ /configs/

RUN apk --no-cache add docker sudo libxslt nano

#because these volumes are mounted on host and if i try to chown paths they become unavaliable on the host so no modifications cant be made.
RUN chmod -R 777 /var/www/app/ && chmod -R 777 /etc/nginx/sites-available/ && chmod -R 777 /configs/ 
RUN whoami