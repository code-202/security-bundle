FROM code202/php-franken:8.5

ARG USER
ARG UID=1000
ARG CONTAINER_SHELL=/bin/bash

RUN apk add --no-cache zsh openssh make

RUN <<-EOF
	# Use "adduser -D ${USER}" for alpine based distros
	adduser -u "$UID" -h /home/developer "$USER" -s "$CONTAINER_SHELL" -D
	# Add additional capability to bind to port 80 and 443
	setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp
	# Give write access to /config/caddy and /data/caddy
	chown -R ${USER}:${USER} /config/caddy /data/caddy
EOF

USER ${USER}

