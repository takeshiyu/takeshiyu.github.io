---
title: 'Docker 建構超時：一個意外的 Composer 設定'
date: 2024-09-30
author: Takeshi Yu
gravatar: 3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39
twitter: '@takeshi_hey'
---

在一個需要使用 Docker 部署 Laravel 的專案中，我遇到了一個棘手的問題，建構映像檔的過程中經常出現超過時間上限而導致失敗的狀況。該專案要求使用 Docker 部署，主要步驟包括：

1. 安裝 [PHP8.3](https://www.php.net/releases/8.3/en.php)，以及必要的模組套件
3. 透過 [PECL](https://pecl.php.net) 安裝 [MongoDB 驅動與模組](https://www.mongodb.com/docs/drivers/php-drivers/)
4. 安裝 [Caddy Server](https://caddyserver.com)
5. 安裝 [Supervisor](http://supervisord.org)
6. 複製檔案、設定相關服務等等

最後將映像檔部署到 [Linode](https://www.linode.com) 上運行，需要安裝與設定的部分並沒有比較特殊，除了需要額外安裝、編譯 MongoDB。

---

但隨專案推進，Docker 構建過程頻繁在 300 秒後失敗：

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

## <a href="#">#</a> Docker 有限制時間？

因為是在建立階段失敗，並不是在啟動階段，所以很直接懷疑是不是 `Docker` 本身有某種執行時間的限制？網路上確實有一些類似狀況的討論，但跟我遇到的錯誤都不太一樣，且如果可以透過設定來改善的話，[官方文件](https://docs.docker.com/get-started/) 應該會有詳盡的說明，但似乎也沒有任何的相關資訊。

且限制 **300秒** 似乎不合理，假設有第三方廠商要產生自家的映像檔，例如：PHP8.3 等等，需要安裝的東西肯定比我多很多，**Docker 應該要可以根據需要，而進行長時間建立映像檔，只要過程中沒有報錯**。又或者，他們採用了什麼方式避免了這樣的狀況？使用了 [Multi-stage builds](https://docs.docker.com/build/building/multi-stage/) 嗎 ?

## <a href="#">#</a> 多階段建構 ( Multi-stage builds )

採用多階段建構的方式，確實加快了建立的速度：

```dockerfile
FROM php:8.3-fpm AS mongodb-builder

RUN apt-get update && apt-get install -y \
    libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

...

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

...
```
但偶爾還是會遇到超過時間限制問題 ...，再次確認失敗錯誤訊息後，發現了這一行：

```shell
Check https://getcomposer.org/doc/06-config.md#process-timeout for details
```

所以是建立映像檔的過程中，因為 `composer` 安裝套件過久，所以導致失敗嗎？

## <a href="#">#</a> Composer Process Timeout

但是整個 Dockerfile 裡，唯一跟 composer 有關係的只有安裝 composer 命令：

```shell
&& curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
```

如果是這一行導致錯誤，不可能出現 `composer process timeout`，因為並沒有執行 `composer install`，也沒有進行任何與 PHP 套件下載有關的操作。所以應該不是 Dockerfile 裡的命令造成的結果。後來，重複看了 [composer process timeout](https://getcomposer.org/doc/06-config.md#process-timeout)：

> The timeout in seconds for process executions, defaults to 300 (5mins). The duration processes like git clones can run before Composer assumes they died out. You may need to make this higher if you have a slow connection or huge vendors.

忽然想到，我在 `composer.json` 中定義了一些 script，像是執行測試、格式化檔案、建立映像檔等等，可以方便執行命令：

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

而每次執行 composer script 時，就會觸發 Composer 預設的 `process timeout`。如果建立映像檔過程比較久，就有可能會超過所限制的時間，這就是為什麼 docker build 過程中會出現超時的原因！

## <a href="#">#</a> 解決方案

解決的方式有兩種，一種是直接 [調整 composer 的時間限制](https://getcomposer.org/doc/articles/scripts.md#managing-the-process-timeout)，延長執行時間上限：

```json
"config": {
    ...
    "process-timeout": 600
},
```
另一種就是把建立映像檔的 docker 命令分離出來，成為一個獨立的 shell script：

```shell
#!/bin/bash

DOCKER_BUILDKIT=1 docker build --no-cache -t my/api .
docker tag my/api my/api
docker push my/api
```

這樣就不再受到 Composer 執行時間的限制，也可以更靈活管理 Docker 建構過程。

## <a href="#">#</a> 結論

最終，選擇調整 Composer 的默認執行時間是一個比較簡單有效的解決方案。這次的經驗提示了在複雜的開發環境中，工具之間可能存在的隱藏交互影響。雖然 Composer 和 Docker 都是強大的工具，但它們的默認設定可能在某些情況下相互衝突。
