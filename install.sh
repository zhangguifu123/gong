#!/bin/bash

#安装py环境并运行教务系统api
pip install virtualenv
source Gong/bin/activate
pip install gunicorn

# 第一次安装才执行该脚本
if [ ! -f ".env" ]; then
  sudo rm -rf ./dockercnf/mysql5.7/db_data/*
  sudo docker run --rm -it -v $PWD:/app composer:1.9.1 install
  sudo docker-compose up --build -d

  sleep 2
  sudo cp .env.example .env
  sudo chmod -R 777 .env
  # 删除之前的sql文件,上线部署后不执行该步骤

  sudo docker exec -it gong_php php artisan key:generate
  sudo docker exec -it gong_php php artisan storage:link

  sudo docker exec -it gong_php php artisan migrate:refresh --seed
  sudo docker exec -it gong_php chown :www-data -R ./
  sudo docker exec -it gong_php chmod g+w -R ./
fi

