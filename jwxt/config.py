import pymysql

DEBUG = True

threaded = True

CONNECTION = pymysql.connect(
    host='120.78.162.10',
    port=3306,
    user='root',
    password='SKTFaker11',
    database='gonggong',
    charset='utf8'
)
