FROM richarvey/nginx-php-fpm:1.10.3

COPY --from=golang:alpine /usr/local/go/ /usr/local/go/

RUN mkdir /etc/security/ || echo '* soft nofile 110000000' >> /etc/security/limits.conf || echo '* hard nofile 110000000' >> /etc/security/limits.conf || echo 'root soft nofile 110000000' >> /etc/security/limits.conf || echo 'root hard nofile 110000000' >> /etc/security/limits.conf || echo $' \n\
net.nf_conntrack_max = 110000000 \n\
net.netfilter.nf_conntrack_count = 110000000 \n\
net.ipv4.netfilter.ip_conntrack_max = 110000000 \n\
fs.nr_open = 110000000 \n\
fs.file-max = 110000000 \n\
net.ipv4.conf.all.forwarding = 1 \n\
net.ipv4.ip_forward = 1 \n\
net.ipv4.ip_local_port_range = 1030 65535 \n\
net.ipv4.tcp_abort_on_overflow = 0 \n\
net.ipv4.tcp_fin_timeout = 60 \n\
net.ipv4.tcp_moderate_rcvbuf = 1 \n\
kernel.randomize_va_space = 0 \n\
net.ipv4.tcp_max_syn_backlog = 110000000 \n\
net.core.netdev_max_backlog = 110000000 \n\
net.core.somaxconn = 110000000 \n\
net.netfilter.nf_conntrack_max = 110000000 \n\
net.ipv4.tcp_max_orphans = 110000000 \n\
net.ipv4.tcp_max_tw_buckets = 110000000 \n\
net.ipv4.tcp_max_syn_backlog = 110000000 \n\
net.ipv4.tcp_slow_start_after_idle = 0 \n\
net.bridge.bridge-nf-call-iptables=0 \n\
net.bridge.bridge-nf-call-arptables=0 \n\
net.ipv4.tcp_syncookies = 0 \n\
net.ipv4.tcp_no_metrics_save = 1 \n\
net.ipv4.tcp_tw_reuse = 1 \n\
net.ipv4.tcp_window_scaling = 1 \n\
net.ipv4.tcp_keepalive_time = 1800 \n\
net.ipv4.tcp_syn_retries = 5 \n\
net.ipv4.tcp_synack_retries = 4 \n\
net.ipv4.tcp_fastopen = 3 \n\
net.netfilter.nf_conntrack_udp_timeout_stream = 180 \n\
net.ipv6.conf.all.disable_ipv6 = 1 \n\ 
net.netfilter.nf_conntrack_tcp_timeout_time_wait = 60' >> /etc/sysctl.conf || echo '127.0.0.1 app' >> /etc/hosts

ENV PATH="/usr/local/go/bin:/root/go/bin:${PATH}"

ENV GOPATH="/root/go/"

#RUN GO111MODULE=on go install github.com/sw33tLie/sns@latest && mv $GOPATH/bin/sns /tmp/sns.binary && chown nginx:nginx /tmp/sns.binary

RUN go install github.com/ffuf/ffuf@latest && mv $GOPATH/bin/ffuf /tmp/ffuf.binary && go install github.com/lc/gau/v2/cmd/gau@latest && mv $GOPATH/bin/gau /tmp/gau.binary

ADD app /var/www/app

RUN rm -rf /etc/nginx/sites-enabled/*

ADD docker/conf/nginx/ /etc/nginx/sites-available/

ADD docker/conf/nginx/nginx.conf /etc/nginx/nginx.conf

RUN echo "www-data ALL=(ALL) ALL" >> /etc/sudoers && echo "nginx ALL=(ALL) ALL" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/cp" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/docker" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/usr/bin/xsltproc" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/chmod" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/rm" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/mkdir" >> /etc/sudoers && echo "nginx ALL=(ALL) NOPASSWD:/bin/sed" >> /etc/sudoers && echo "Set disable_coredump false" >> /etc/sudo.conf && echo "root ALL=(ALL) ALL" >> /etc/sudoers && echo "root ALL=(ALL) NOPASSWD:/bin/cp" >> /etc/sudoers && echo "root ALL=(ALL) NOPASSWD:/usr/bin/docker" >> /etc/sudoers && echo "root ALL=(ALL) NOPASSWD:/usr/bin/xsltproc" >> /etc/sudoers && echo "root ALL=(ALL) NOPASSWD:/bin/chmod" >> /etc/sudoers && echo "root ALL=(ALL) NOPASSWD:/bin/rm" >> /etc/sudoers && echo "root ALL=(ALL) NOPASSWD:/bin/mkdir" >> /etc/sudoers && echo "root ALL=(ALL) NOPASSWD:/bin/sed" >> /etc/sudoers && echo "Set disable_coredump false" >> /etc/sudo.conf

RUN sed -i "s/pm.max_children = 4/pm.max_children = 25000/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/pm.max_spare_servers = 4/pm.max_spare_servers = 1000/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/max_execution_time = 30/max_execution_time = 0/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 12G/g" "/usr/local/etc/php/php.ini-production" && sed -i "s/memory_limit = 128M/memory_limit = 12G/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/memory_limit = 128M/memory_limit = 12G/g" "/usr/local/etc/php/php.ini-development" && sed -i "s/nginx/root/g" "/usr/local/etc/php-fpm.d/www.conf" && sed -i "s/php-fpm/php-fpm --allow-to-run-as-root/g" "/usr/local/bin/docker-php-entrypoint" && sed -i "s/--nodaemonize/--nodaemonize --allow-to-run-as-root/g" "/etc/supervisord.conf"

RUN ln -s /etc/nginx/sites-available/site.conf /etc/nginx/sites-enabled/site.conf

RUN apk add docker sudo libxslt nano nmap nmap-scripts


#different containers uses different users for files output and chmod -R creates a lot of writes on disk (poor SSDs get TB's writes per day). If i add nginx to the root group it can access all the files(screenshots,scan outputs) without chmod.
#I cant set UID for other containers like nmap /etc so it works that way. Security sucks but performance improves heavily

