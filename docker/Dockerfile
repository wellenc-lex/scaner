FROM richarvey/nginx-php-fpm:1.10.3

COPY --from=golang:alpine /usr/local/go/ /usr/local/go/

RUN mkdir /etc/security/ && echo '*                soft    nofile          200005000' >> /etc/security/limits.conf && echo '*                hard    nofile          200005000' >> /etc/security/limits.conf && echo 'root             soft    nofile          200005000' >> /etc/security/limits.conf && echo 'root             hard    nofile          200005000' >> /etc/security/limits.conf && echo $' \n\
fs.file-max = 200005000 \n\
net.ipv4.conf.all.forwarding=1 \n\
net.ipv4.tcp_tw_reuse=1 \n\
net.ipv4.tcp_max_tw_buckets=450000 \n\
net.ipv4.ip_forward =1 \n\
net.ipv4.ip_local_port_range= 1000 65535 \n\
net.ipv4.tcp_max_syn_backlog= 65535 \n\
net.core.somaxconn = 320000 \n\
net.ipv4.tcp_max_tw_buckets = 1440000 \n\
net.ipv4.tcp_window_scaling =1 \n\
net.ipv4.tcp_moderate_rcvbuf=0 \n\
net.ipv4.tcp_mem=100000000 100000000 100000000 \n\
net.ipv4.tcp_rmem=4096 65535 1000000 \n\
net.ipv4.tcp_wmem=4096 65535 1000000 \n\
net.core.rmem_max=1000000 \n\
net.core.wmem_max=1000000 \n\
net.core.netdev_max_backlog=65535 \n\
net.ipv4.ip_conntrack_max = 1000000 \n\
net.ipv4.netfilter.ip_conntrack_max = 1000000 \n\
fs.nr_open=200005000' >> /etc/sysctl.conf

ENV PATH="/usr/local/go/bin:/root/go/bin:${PATH}"

ENV GOPATH="/root/go/"

RUN go install github.com/ffuf/ffuf@latest && mv $GOPATH/bin/ffuf /tmp/ffuf.binary && chown nginx:nginx /tmp/ffuf.binary

#RUN GO111MODULE=on go install github.com/sw33tLie/sns@latest && mv $GOPATH/bin/sns /tmp/sns.binary && chown nginx:nginx /tmp/sns.binary

#RUN go install github.com/hiddengearz/jsubfinder@latest && wget https://raw.githubusercontent.com/ThreatUnkown/jsubfinder/master/.jsf_signatures.yaml && mv $GOPATH/bin/jsubfinder /tmp/jsubfinder.binary && mv .jsf_signatures.yaml /tmp/.jsf_signatures.yaml && chown nginx:nginx /tmp/jsubfinder.binary && chown nginx:nginx /tmp/.jsf_signatures.yaml

ADD app /var/www/app

RUN rm -rf /etc/nginx/sites-enabled/*

ADD docker/conf/nginx/ /etc/nginx/sites-available/

ADD docker/conf/nginx/nginx.conf /etc/nginx/nginx.conf

RUN echo "www-data ALL=(ALL) ALL" >> /etc/sudoers && echo "nginx ALL=(ALL) ALL" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/cp" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/docker" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/xsltproc" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/chmod" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/rm" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/mkdir" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/sed" >> /etc/sudoers && echo "Set disable_coredump false" >> /etc/sudo.conf

RUN sed -i "s/pm.max_children = 4/pm.max_children = 12000/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/pm.max_spare_servers = 4/pm.max_spare_servers = 200/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 10G/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/memory_limit = 128M/memory_limit = 10G/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 10G/g" "/usr/local/etc/php/php.ini-development" 

RUN ln -s /etc/nginx/sites-available/site.conf /etc/nginx/sites-enabled/site.conf

#because these volumes are mounted on host and if i try to chown paths they become unavaliable on the host so no modifications cant be made and i can't make changes on the fly && build containers later/etc.
#RUN chmod -R 777 /var/www/app/ && chmod -R 777 /etc/nginx/sites-available/

RUN apk add docker sudo libxslt nano nmap nmap-scripts


#different containers uses different users for files output and chmod -R creates a lot of writes on disk (poor SSDs get TB's writes per day). If i add nginx to the root group it can access all the files(screenshots,scan outputs) without chmod.
#I cant set UID for other containers like nmap /etc so it works that way. Security sucks but performance improves heavily
RUN adduser nginx root && adduser www-data root