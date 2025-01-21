---
title: "That Time Composer's Timeout Drove Me Nuts"
date: 2024-09-30
author: Takeshi Yu
gravatar: 3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39
twitter: '@takeshi_hey'
---

You know what's frustrating? Spending hours debugging what seems like a simple Docker build issue, only to find out it's something completely unexpected. That's exactly what happened to me recently while working on a Laravel deployment.

---

Pretty standard stuff - I was building a Docker image for a Laravel app that needed to run on Linode. Nothing fancy, just your typical setup:

* [PHP8.3](https://www.php.net/releases/8.3/en.php) with all the usual suspects.
* Setting up [MongoDB](https://www.mongodb.com/docs/drivers/php-drivers/) drivers and extensions via [PECL](https://pecl.php.net).
* Installing [Caddy Server](https://caddyserver.com).
* [Supervisor](http://supervisord.org) to keep processes in check.
* Copying files and configuring related services.

## The Problem

Everything seemed fine until the build kept failing at exactly **300 seconds**. Like clockwork. Here's what I was seeing:

```dockerfile
=> CANCELED exporting to image                                                                                       37.6s
...
The following exception is caused by a process timeout
Check https://getcomposer.org/doc/06-config.md#process-timeout for details

ERROR: failed to solve: Canceled: context canceled
In Process.php line 1205:

  The process "DOCKER_BUILDKIT=1 docker 'build' '--no-cache' '-t' 'my/api' '.'" exceeded the timeout of 300 seconds.

build [--dev] [--no-dev] [--] [<args>...]
```

## The Head-Scratching Begins

My first thought? "Oh great, Docker's being Docker again." You know how it goes - you immediately blame the most obvious suspect. I spent a good chunk of time diving into [Docker's  documentation](https://docs.docker.com/get-started/), looking for some mysterious build timeout setting that I must have missed. But something didn't add up. Many official images take way longer than **300 seconds** to build, and they work just fine. Plus, if Docker had a built-in timeout, wouldn't it be, I don't know, documented somewhere?

## The Plot Thickens

Being a good developer (or at least trying to be), I figured I'd try to optimize my build with [multi-stage builds]((https://docs.docker.com/build/building/multi-stage/)). Here's what I came up with:

```dockerfile
FROM php:8.3-fpm AS mongodb-builder

RUN apt-get update && apt-get install -y \
    libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# ...

FROM php:8.3-fpm

COPY --from=mongodb-builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=mongodb-builder /usr/local/etc/php/conf.d/docker-php-ext-mongodb.ini /usr/local/etc/php/conf.d/

RUN apt-get update && apt-get install -y \
        curl gnupg gosu zip unzip git supervisor sqlite3 libcap2-bin \
        libpng-dev dnsutils librsvg2-bin fswatch vim libzip-dev \
        libxml2-dev libcurl4-openssl-dev libssl-dev \
    && docker-php-ext-install zip pdo_mysql bcmath soap intl opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ...
```

Did it fix the problem? Nope. But it did make my Dockerfile look prettier, so there's that.

## The Lightbulb Moment

Then I noticed something in the error message that I'd glossed over before:

```shell
Check https://getcomposer.org/doc/06-config.md#process-timeout for details
```

Composer? But I'm just installing Composer in my Dockerfile:

```shell
&& curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
```

That's when it hit me. I had these neat little shortcuts in my `composer.json`:

```json
"lint": "pint",
"clear": [
    "@php artisan lighthouse:clear-cache",
    "@php artisan config:clear",
    "@php artisan route:clear",
    "@php artisan view:clear",
    "@php artisan cache:clear"
],
"test": [
    "@lint",
    "@clear",
    "pest --profile --colors=always"
],
"docker": "DOCKER_BUILDKIT=1 docker",
"build": [
    "@clear",
    "@docker build --no-cache -t tgk/be .",
    "@docker tag tgk/be heytakeshi/tgk-be:latest",
    "@docker push heytakeshi/tgk-be"
]
```

And I was running my Docker builds through Composer with `composer build`. Guess what has a **300-second** default timeout? Yep, Composer.

## The Fix

The solution was embarrassingly simple. Either:

1. Tell Composer to chill out a bit:

```json
"config": {
    // ...
    "process-timeout": 600
},
```

2. Or move the Docker commands to a shell script where Composer can't boss them around:

```shell
#!/bin/bash

DOCKER_BUILDKIT=1 docker build --no-cache -t my/api .
docker tag my/api my/api
docker push my/api
```

I went with option 1 because I was too lazy to update all my deployment scripts.

## The Lesson

Sometimes the problem isn't where you think it is. I spent way too long looking at Docker documentation when the answer was right there in the error message. Also, maybe running everything through Composer scripts isn't always the best idea - but that's a problem for future me.

Next time you hit a weird timeout, check your Composer config. It might just save you a few hours of Docker documentation diving.
