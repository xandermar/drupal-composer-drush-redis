FROM drupal

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

RUN composer update

RUN composer require composer/installers && \  
    composer require drupal/accordion_menus && \  
    composer require drupal/address && \  
    composer require drupal/admin_toolbar && \  
    composer require drupal/adsense && \  
    composer require drupal/advagg && \  
    composer require drupal/antibot && \  
    composer require drupal/auto_entitylabel && \  
    composer require drupal/backup_migrate && \  
    composer require drupal/block_class && \  
    composer require drupal/block_content_permissions && \ 
    composer require drupal/bootstrap && \  
    composer require drupal/bulk_user_registration && \  
    composer require drupal/chosen && \  
    composer require drupal/ckeditor_bootstrap_grid && \  
    composer require drupal/ckeditor_media_embed && \  
    composer require drupal/conditional_fields && \  
    composer require drupal/content_access && \  
    composer require drupal/content_sync && \  
    composer require drupal/custom_search && \  
    composer require drupal/ds && \  
    composer require drupal/easy_breadcrumb && \  
    composer require drupal/editor_advanced_link && \  
    composer require drupal/email_registration && \  
    composer require drupal/entity_browser && \  
    composer require drupal/entity_usage && \  
    composer require drupal/facets && \  
    composer require drupal/feeds && \  
    composer require drupal/feeds_tamper && \  
    composer require drupal/field_formatter_class && \  
    composer require drupal/field_permissions && \  
    composer require drupal/file_entity && \  
    composer require drupal/filebrowser && \  
    composer require drupal/flexslider && \  
    composer require drupal/fontawesome && \  
    composer require drupal/fontawesome_menu_icons && \  
    composer require drupal/force_password_change && \  
    composer require drupal/front && \  
    composer require drupal/google_analytics && \  
    composer require drupal/honeypot && \  
    composer require drupal/imce && \  
    composer require drupal/libraries && \  
    composer require drupal/login_redirect_per_role && \  
    composer require drupal/mass_password_change && \  
    composer require drupal/mass_pwreset && \  
    composer require drupal/media_entity && \  
    composer require drupal/menu_block && \  
    composer require drupal/menu_block_title && \  
    composer require drupal/metatag && \  
    composer require drupal/module_filter && \  
    composer require drupal/multi_node_add && \  
    composer require drupal/nice_menus && \  
    composer require drupal/nodeaccess && \  
    composer require drupal/page_manager && \  
    composer require drupal/panels && \  
    composer require drupal/paragraphs && \  
    composer require drupal/pathauto && \  
    composer require drupal/phpmailer && \  
    composer require drupal/poll && \  
    composer require drupal/printfriendly && \  
    composer require drupal/protected_pages && \  
    composer require drupal/rabbit_hole && \  
    composer require drupal/radix_layouts && \  
    composer require drupal/realname && \  
    composer require drupal/recaptcha && \  
    composer require drupal/redirect && \  
    composer require drupal/redirect_after_login && \  
    composer require drupal/rules && \  
    composer require drupal/search_api && \  
    composer require drupal/search_api_page && \  
    composer require drupal/search_api_solr && \  
    composer require drupal/shs && \  
    composer require drupal/simple_access && \  
    composer require drupal/smtp && \  
    composer require drupal/starrating && \  
    composer require drupal/superfish && \  
    composer require drupal/svg_image && \  
    composer require drupal/swiftmailer && \  
    composer require drupal/tamper && \  
    composer require drupal/taxonomy_machine_name && \  
    composer require drupal/taxonomy_manager && \  
    composer require drupal/text_summary_options && \  
    composer require drupal/views_bootstrap && \  
    composer require drupal/views_bulk_operations && \  
    composer require drupal/views_data_export && \  
    composer require drupal/views_flipped_table && \  
    composer require drupal/views_xml_backend && \  
    composer require drupal/we_megamenu && \  
    composer require drupal/webform && \  
    composer require drupal/webform_views && \  
    composer require drupal/xmlsitemap && \  
    composer require drush/drush && \  
    composer require predis/predis && \  
    composer require wikimedia/composer-merge-plugin && \
    composer require drupal/font_awesome && \
    composer require drupal/redis && \
    composer require dynamic_layouts
