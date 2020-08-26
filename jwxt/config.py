import pymysql

DEBUG = True

threaded = True

CONNECTION = pymysql.connect(
    host='127.0.0.1',
    port=8001,
    user='root',
    password='yourpassword',
    database='gong',
    charset='utf8'
)
