---
title: '一個意外的 Composer Process Timeout 設定'
date: 2024-09-30
author: Takeshi Yu
gravatar: 3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39
twitter: '@takeshi_hey'
---

使用 Docker 部署 Laravel 的過程裡，經常出現執行超過時間上限而導致失敗的狀況。主要步驟包括：

1. 安裝 [PHP8.3](https://www.php.net/releases/8.3/en.php)，以及必要的模組套件
2. 透過 [PECL](https://pecl.php.net) 安裝 [MongoDB 驅動與模組](https://www.mongodb.com/docs/drivers/php-drivers/)
3. 安裝 [Caddy Server](https://caddyserver.com)
4. 安裝 [Supervisor](http://supervisord.org)
5. 複製檔案、設定相關服務等等

最後將映像檔部署到 [Linode](https://www.linode.com) 上運行，需要安裝與設定的部分並沒有比較特殊，除了需要額外安裝、編譯 [MongoDB](https://www.mongodb.com/)。

---

但是過程頻繁的在 300 秒後失敗：

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

## Docker 有限制建構時間？

因為是在建立映像檔時失敗，並不是在啟動階段，因此懷疑是否 Docker 本身有某種執行時間的限制？網路上確實有一些類似狀況的討論，但錯誤都不太一樣，且如果可以透過設定來改善的話，[官方文件](https://docs.docker.com/get-started/) 應該會有詳盡的說明？但似乎也沒有任何的相關資訊。

限制 **300秒** 似乎不合理，假設有第三方廠商要產生自己的映像檔，例如：PHP8.3 等等，需要安裝的東西肯定比我多很多？**Docker 應該可以根據需要，而進行長時間建立映像檔，只要過程中沒有出現錯誤**。又或者，需要採用什麼方式避免這樣的狀況？使用 [多階段建構 ( Multi-stage builds )](https://docs.docker.com/build/building/multi-stage/) 嗎 ?

## 多階段建構 ( Multi-stage builds )

採用多階段建構的方式，確實加快了建立的速度：

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

但問題並沒有完全解決，還是有機會發生。再次確認失敗錯誤訊息後，發現了這一行：

```shell
Check https://getcomposer.org/doc/06-config.md#process-timeout for details
```

所以是建立映像檔的過程中，因為 Composer 安裝套件過久，所以導致失敗嗎？

## Composer Process Timeout

但是整個 Dockerfile 裡，唯一跟 Composer 有關係的只有安裝 Composer 命令：

```shell
&& curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
```

如果是這一行導致錯誤，應該不可能出現 `composer process timeout`，因為並沒有執行 `composer install`，也沒有進行任何與 PHP 套件下載有關的操作。所以應該不是 Dockerfile 裡的命令造成的結果。後來，重複看了 [composer process timeout](https://getcomposer.org/doc/06-config.md#process-timeout)：

> The timeout in seconds for process executions, defaults to 300 (5mins). The duration processes like git clones can run before Composer assumes they died out. You may need to make this higher if you have a slow connection or huge vendors.

忽然想到， `composer.json` 中有定義了一些 `script`，像是執行測試、格式化檔案、建立映像檔等等：

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

方便執行命令：

```shell
composer lint   # 格式化
composer test   # 執行測試
composer build  # 建立映像檔
```

而每次執行時，都會遵守預設的 `process timeout` 時間。如果這個過程比較久，就有可能會超過所限制的時間，而導致建構失敗。

## 解決方案

可能的解決方式有兩種，一種是直接 [調整 Composer 的時間限制](https://getcomposer.org/doc/articles/scripts.md#managing-the-process-timeout)，延長執行時間上限：

```json
"config": {
    // ...
    "process-timeout": 600
},
```
另一種就是把建立映像檔的命令分離出來：

```shell
#!/bin/bash

DOCKER_BUILDKIT=1 docker build --no-cache -t my/api .
docker tag my/api my/api
docker push my/api
```

這樣就不再受到 Composer 執行時間的限制，也可以更靈活管理映像檔建構過程。

最終，選擇調整 Composer 的默認執行時間是一個比較簡單有效的解決方案，符合日常的開發習慣。
