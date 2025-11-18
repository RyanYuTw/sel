#!/bin/bash

echo "安裝 PHP 依賴..."
composer install

echo "安裝 Node.js 依賴..."
npm install

echo "複製環境配置..."
cp .env.example .env

echo "生成應用金鑰..."
php artisan key:generate

echo "執行資料庫遷移..."
php artisan migrate

echo "執行資料庫種子..."
php artisan db:seed

echo "建立儲存連結..."
php artisan storage:link

echo "設定完成！"
echo "執行以下命令啟動開發伺服器："
echo "1. php artisan serve"
echo "2. npm run dev"