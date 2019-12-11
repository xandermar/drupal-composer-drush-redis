# from https://www.drupal.org/docs/8/system-requirements/drupal-8-php-requirements
FROM php:7.3-apache-stretch
# TODO switch to buster once https://github.com/docker-library/php/issues/865 is resolved in a clean way (either in the PHP image or in PHP itself)

# install the PHP extensions we need
RUN set -eux; \
	\
	if command -v a2enmod; then \
		a2enmod rewrite; \
	fi; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libjpeg-dev \
		libpng-dev \
		libpq-dev \
		libzip-dev \
	; \
	\
	docker-php-ext-configure gd \
		--with-freetype-dir=/usr \
		--with-jpeg-dir=/usr \
		--with-png-dir=/usr \
	; \
	\
	docker-php-ext-install -j "$(nproc)" \
		gd \
		opcache \
		pdo_mysql \
		pdo_pgsql \
		zip \
	; \
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	rm -rf /var/lib/apt/lists/*

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
		echo 'opcache.fast_shutdown=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini


RUN apt-get update && apt-get install -y \
	curl \
	git \
	mysql-client \
	vim \
	zip \
	wget

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php composer-setup.php && \
	mv composer.phar /usr/local/bin/composer && \
	php -r "unlink('composer-setup.php');"

RUN wget -O drush.phar https://github.com/drush-ops/drush-launcher/releases/download/0.4.2/drush.phar && \
	chmod +x drush.phar && \
	mv drush.phar /usr/local/bin/drush

WORKDIR /var/www/html

RUN composer create-project drupal/drupal /var/www/html
RUN composer require composer/installers
RUN composer require drupal/accordion_menus
RUN composer require drupal/address
RUN composer require drupal/admin_toolbar
RUN composer require drupal/adsense
RUN composer require drupal/advagg
RUN composer require drupal/antibot
RUN composer require drupal/auto_entitylabel
RUN composer require drupal/backup_migrate
RUN composer require drupal/block_class
RUN composer require drupal/block_content_permissions 
RUN composer require drupal/bootstrap
RUN composer require drupal/bulk_user_registration
RUN composer require drupal/chosen
RUN composer require drupal/ckeditor_bootstrap_grid
RUN composer require drupal/ckeditor_media_embed
RUN composer require drupal/conditional_fields
RUN composer require drupal/content_access
RUN composer require drupal/content_sync
RUN composer require drupal/custom_search
RUN composer require drupal/ds
RUN composer require drupal/easy_breadcrumb
RUN composer require drupal/editor_advanced_link
RUN composer require drupal/email_registration
RUN composer require drupal/entity_browser
RUN composer require drupal/entity_usage
RUN composer require drupal/facets
RUN composer require drupal/feeds
RUN composer require drupal/feeds_tamper
RUN composer require drupal/field_formatter_class
RUN composer require drupal/field_permissions
RUN composer require drupal/file_entity
RUN composer require drupal/filebrowser
RUN composer require drupal/flexslider
RUN composer require drupal/fontawesome
RUN composer require drupal/fontawesome_menu_icons
RUN composer require drupal/force_password_change
RUN composer require drupal/front
RUN composer require drupal/google_analytics
RUN composer require drupal/honeypot
RUN composer require drupal/imce
RUN composer require drupal/libraries
RUN composer require drupal/login_redirect_per_role
RUN composer require drupal/mass_password_change
RUN composer require drupal/mass_pwreset
RUN composer require drupal/media_entity
RUN composer require drupal/menu_block
RUN composer require drupal/menu_block_title
RUN composer require drupal/metatag
RUN composer require drupal/module_filter
RUN composer require drupal/multi_node_add
RUN composer require drupal/nice_menus
RUN composer require drupal/nodeaccess
RUN composer require drupal/page_manager
RUN composer require drupal/panels
RUN composer require drupal/paragraphs
RUN composer require drupal/pathauto
RUN composer require drupal/phpmailer
RUN composer require drupal/poll
RUN composer require drupal/printfriendly
RUN composer require drupal/protected_pages
RUN composer require drupal/rabbit_hole
RUN composer require drupal/radix_layouts
RUN composer require drupal/realname
RUN composer require drupal/recaptcha
RUN composer require drupal/redirect
RUN composer require drupal/redirect_after_login
RUN composer require drupal/rules
RUN composer require drupal/search_api
RUN composer require drupal/search_api_page
RUN composer require drupal/search_api_solr
RUN composer require drupal/shs
RUN composer require drupal/simple_access
RUN composer require drupal/smtp
RUN composer require drupal/starrating
RUN composer require drupal/superfish
RUN composer require drupal/svg_image
RUN composer require drupal/swiftmailer
RUN composer require drupal/tamper
RUN composer require drupal/taxonomy_machine_name
RUN composer require drupal/taxonomy_manager
RUN composer require drupal/text_summary_options
RUN composer require drupal/views_bootstrap
RUN composer require drupal/views_bulk_operations
RUN composer require drupal/views_data_export
RUN composer require drupal/views_flipped_table
RUN composer require drupal/views_xml_backend
RUN composer require drupal/we_megamenu
RUN composer require drupal/webform
RUN composer require drupal/webform_views
RUN composer require drupal/xmlsitemap
RUN composer require drush/drush
RUN composer require predis/predis
RUN composer require wikimedia/composer-merge-plugin
RUN composer require drupal/font_awesome
RUN composer require drupal/redis
RUN composer require dynamic_layouts
RUN composer require drupal/bootstrap_library
RUN composer require drupal/gdoc_field
