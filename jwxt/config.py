import pymysql

DEBUG = True

threaded = True

CONNECTION = pymysql.connect(
    host='172.23.0.2',
    port=3306,
    user='root',
    password='yourpassword',
    database='gong',
    charset='utf8'
)
