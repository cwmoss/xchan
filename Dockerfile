# syntax=docker/dockerfile:1
FROM php:8.2-cli

WORKDIR /app/
COPY ./ ./

ENV X_LISTEN 0.0.0.0:8080
EXPOSE 8080

ENTRYPOINT php public/index.php
