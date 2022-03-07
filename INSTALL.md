1. Изначально выгрузить из репозитория

    git clone из https://github.com/vepayonline/processing.git
    
    Загрузить дамп БД.
    
    В настройках виртуального хоста Apache указать в DocumentRoot путь к каталогу /web
    
    Включить mod_rewrite.
      
        Для подготовки релиза: 
    
        Установить NodeJS:
                  
        curl -sL https://deb.nodesource.com/setup_10.x | bash - apt-get install -y nodejs
        
        Установить пакеты:
        
        npm install uglify-js@3.14.5 -g
        
        npm i clean-css-cli -g    
        
        Установить composer:
        
        curl -sS https://getcomposer.org/installer -o composer-setup.php
        
        sudo php composer-setup.php --install-dir=/usr/bin --filename=composer
        
        Установить пакет:
        
        composer global require "fxp/composer-asset-plugin:^1.4.1"
        
        Перед релизом:
        
        Обновить пакеты: composer update
        
        Обновить ассеты: ./yii asset assets.php config/assets-prod.php
       
2. php.ini

    В PHP включить short_open_tag = on.
    
    Прописать date.timezone = Europe/Moscow
    
    Включить модули: xml, DOM, curl, PDO, PDO MySQL,  GD, openssl, iconv, gmp, zip, mbstring, Intl, ICU, Fileinfo, bcmath, json
     
    Прописать в curl.cainfo = "..." и openssl.cafile = "..." путь к фалу cacert.pem

3. Настроить:

    Для init - установить права на выполнение
    
    Запустить ./init --env=prod
    
    в config\db.php 
    
    Прописать хост и пользователя базы данных
    
    На каталоги runtime, web\assets, web\shopdata установить права на запись
    
    Для yii,sign,init - установить права на выполнение

4. Настроить в cron задания

    yii widget/rsbcron - 1 раз в минуту
    
    yii widget/notification - 1 раз в минуту
    
    yii widget/queue - 1 раз в минуту
    
    yii widget/updatestatm - 1 раз в час
    
    yii widget/send-otch - в 7.00
    
    yii widget/parts-balance-send-to-partners - в 3.00
    
    yii widget/send-emails-late-updated-pay-schets - в 9.00

    yii exchange/update - каждые сутки по Вильнюсу (UTC+3) в 00:00

5. В дальнейшем обновлять:

    Обновить файлы: git checkout master, git pull 
    
    Проверка подписи: ./sign --act=check
       
    Обновить БД: ./yii migrate/up 
        
    Сбросить кэш: ./yii cache/flush-all
