# coding=utf-8
# 端口 5000
bind = '172.17.0.17:5000'

# 设置守护进程,将进程交给supervisor管理
daemon = 'false'

# 工作模式协程
worker_class = 'gevent'

# 设置最大并发量
worker_connections = 2000

# 设置进程文件目录
pidfile = '/var/run/gunicorn.pid'

# 设置访问日志和错误信息日志路径
accesslog = "log/access.log"
errorlog = "log/debug.log"
loglevel = "debug"

# 设置日志记录水平
loglevel = 'warning'
