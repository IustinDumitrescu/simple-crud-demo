FROM php:8.4.16-fpm-alpine3.22
RUN apk add --no-cache postgresql-dev bash && docker-php-ext-install pdo pdo_pgsql pgsql
WORKDIR /app
COPY . .
RUN chmod +x *.sh 
RUN ./composer_install.sh
EXPOSE 9000
ENTRYPOINT [ "./database_migration.sh" ] 
CMD ["php-fpm"]