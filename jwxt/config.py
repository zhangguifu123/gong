import pymysql

DEBUG = True

threaded = True

CONNECTION = pymysql.connect(
    host='172.21.0.4',
    port=3306,
    user='root',
    password='yourpassword',
    database='gong',
    charset='utf8'
)
