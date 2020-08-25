import pymysql
from config import CONNECTION
from datetime import date, timedelta
from spider import PersonalSpider

class GongGongPipeline():
    """
    继承该类
    只需重写sql语句，以及get_data_list和output_data方法
    """
    def __init__(self, xh):
        self.connection = CONNECTION
        self.cursor = self.connection.cursor()
        self.xh = xh
        self.today = date.today()
        self.update_time = None

        self.create_table_sql = None

        self.check_flag_sql = None
        self.insert_flag_sql = None
        self.update_flag_sql = None

        self.insert_data_sql = None
        self.select_data_sql = None
        self.delete_data_sql = None

        self.get_update_time_sql = None
        self.insert_update_time_sql = None
        self.update_update_time_sql = None




    def create_table(self):
        """
        用于首次创建对应的表
        """
        try:
            self.cursor.execute(self.create_table_sql)
        except Exception as e:
            print(e)


    def get_flag(self):
        """
        :return: 返回一个整型flag变量,0标识未在库中，1标识已在库中
        """
        self.cursor.execute(self.check_flag_sql)
        flag = self.cursor.fetchone()
        if flag == None:
            self.cursor.execute(self.insert_flag_sql)
            self.cursor.execute(self.insert_update_time_sql)
            self.cursor.execute(self.update_update_time_sql)
            self.connection.commit()
            flag = 0
        else:
            flag = int(flag[0])
            if flag == 1:
                self.cursor.execute(self.get_update_time_sql)
                last_update_time = self.cursor.fetchone()[0]
                if last_update_time  <= self.today - timedelta(days=self.update_time):
                    self.delete_data()
                    flag = 0
        return flag


    def get_data_list(self, *args, **kwargs):
        """
        需根据不同数据,处理成入库的格式
        """
        pass


    def insert_data(self, *args, **kwargs):
        data_list = self.get_data_list(*args, **kwargs)
        try:
            self.cursor.executemany(self.insert_data_sql, data_list)
            self.cursor.execute(self.update_flag_sql)
            self.cursor.execute(self.update_update_time_sql)
            self.connection.commit()
        except Exception as e:
            print(e)
            self.connection.rollback()


    def select_data(self):
        try:
            self.cursor.execute(self.select_data_sql)
            results = list(self.cursor.fetchall())
        except Exception as e:
            print(e)
            results = []
        return results


    def delete_data(self):
        try:
            self.cursor.execute(self.delete_data_sql)
            self.cursor.execute(self.update_update_time_sql)
            self.connection.commit()
        except Exception as e:
            print(e)


    def output_data(self):
        pass


class GradePipeline(GongGongPipeline):
    def __init__(self, xh):
        super(GradePipeline, self).__init__(xh)
        self.update_time = 7

        self.create_table_sql = """CREATE TABLE GRADES (
                                    ID INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    NAME VARCHAR(255) NOT NULL,
                                    STUDENT_ID VARCHAR(255) NOT NULL,
                                    COURSE VARCHAR(255) NOT NULL,
                                    GRADE VARCHAR(255) NOT NULL,
                                    CATEGORY VARCHAR(255),
                                    NATURE_OF_COURSE VARCHAR(255),
                                    SEMESTER VARCHAR(255) NOT NULL,
                                    NATURE_OF_TEST VARCHAR(255),
                                    CREDITS FLOAT NOT NULL
                                    )"""

        self.insert_flag_sql = f"INSERT INTO FLAG (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE) VALUES({self.xh}, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT GRADE FROM FLAG WHERE STUDENT_ID={self.xh}"
        self.update_flag_sql = f"UPDATE FLAG SET GRADE=1 WHERE STUDENT_ID={self.xh}"

        self.insert_data_sql = """INSERT INTO GRADES (NAME, STUDENT_ID, COURSE, GRADE, CATEGORY, NATURE_OF_COURSE, SEMESTER, NATURE_OF_TEST, CREDITS)
                                  VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM GRADES WHERE STUDENT_ID={self.xh}"
        self.delete_data_sql = f"DELETE FROM GRADES WHERE STUDENT_ID={self.xh}"

        self.insert_update_time_sql = f"""INSERT INTO UPDATE_TIME (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE)
                                                          VALUES ({self.xh}, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT GRADE FROM UPDATE_TIME WHERE STUDENT_ID={self.xh}"
        self.update_update_time_sql = f"UPDATE UPDATE_TIME SET GRADE='{self.today.strftime('%Y-%m-%d')}' WHERE STUDENT_ID={self.xh}"


    def get_data_list(self, grade):
        data_list = []
        for items in grade.values():
            for item in items:
                data = (item['name'], item['student_id'], item['course'], item['grade'], item['category'],
                        item['nature_of_course'],item['semester'], item['nature_of_test'], item['credits'])
                data_list.append(data)
        return data_list


    def output_data(self):
        results = self.select_data()
        keys = ['name', 'student_id', 'course', 'grade', 'category', 'nature_of_course', 'semester', 'nature_of_test', 'credits']
        semesters = [] # 记录一共多少学期
        grades_list = [] # 单学期的成绩数据
        all_semesters_grades_list = [] #所以学期成绩数据
        for index, result in enumerate(results):
            values = [result[i] for i in range(1, 10)]
            # 判断是否为同一个学期
            if result[7] not in semesters:
                # 提交上一学期的所有成绩，首次进入循环除外
                if index != 0:
                    all_semesters_grades_list.append(grades_list)
                semesters.append(result[7])
                grades_list = [dict(zip(keys, values))]
            else:
                grades_list.append(dict(zip(keys, values)))
            # 提交最后一次学期成绩
            if index == len(results)-1:
                all_semesters_grades_list.append(grades_list)

        grades = dict(zip(semesters, all_semesters_grades_list))
        return grades


class SchedulePipeline(GongGongPipeline):
    def __init__(self, xh):
        super(SchedulePipeline, self).__init__(xh)
        self.update_time = 7

        self.create_table_sql = """CREATE TABLE SCHEDULE (
                                    ID INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    STUDENT_ID VARCHAR(255) NOT NULL,
                                    SEMESTER VARCHAR(255) NOT NULL,
                                    WEEK VARCHAR(255) NOT NULL,
                                    COURSE VARCHAR(255) NOT NULL,
                                    TEACHER VARCHAR(255) NOT NULL,
                                    LOCATION VARCHAR(255),
                                    TIME VARCHAR(255) NOT NULL,
                                    START_TIME VARCHAR(255),
                                    END_TIME VARCHAR(255),
                                    WEEKS VARCHAR(255) NOT NULL 
                                    )"""

        self.insert_flag_sql = f"INSERT INTO FLAG (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE, EXAM) VALUES({self.xh}, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT SCHEDULE FROM FLAG WHERE STUDENT_ID={self.xh}"
        self.update_flag_sql = f"UPDATE FLAG SET SCHEDULE=1 WHERE STUDENT_ID={self.xh}"


        self.insert_data_sql = """INSERT INTO SCHEDULE (STUDENT_ID, SEMESTER, WEEK, COURSE, TEACHER, LOCATION, TIME, START_TIME, END_TIME, WEEKS)
                                  VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM SCHEDULE WHERE STUDENT_ID={self.xh}"
        self.delete_data_sql = f"DELETE FROM SCHEDULE WHERE STUDENT_ID={self.xh}"

        self.insert_update_time_sql = f"""INSERT INTO UPDATE_TIME (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE, EXAM)
                                                                  VALUES ({self.xh}, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT SCHEDULE FROM UPDATE_TIME WHERE STUDENT_ID={self.xh}"
        self.update_update_time_sql = f"UPDATE UPDATE_TIME SET SCHEDULE='{self.today.strftime('%Y-%m-%d')}' WHERE STUDENT_ID={self.xh}"

    def get_data_list(self, schedule, semester):
        data_list = []
        for week, items in schedule.items():
            for item in items:
                if item:
                    data = [self.xh, semester, week, item['course'], item['teacher'], item['location'],
                            item['time'], item['start_time'], item['end_time'], item['week']]
                    data_list.append(data)
        return data_list


    def output_data(self):
        results = self.select_data()
        keys = ['course', 'teacher', 'location', 'time', 'start_time', 'end_time', 'week']
        semester_schedule = []
        weeks = []
        week_schedule = []
        for index, result in enumerate(results):
            values = [result[i] for i in range(4, 11)]
            if result[3] not in weeks:
                if index != 0:
                    semester_schedule.append(week_schedule)
                    week_schedule = []
                week = result[3]
                weeks.append(week)
                week_schedule.append(dict(zip(keys, values)))
            else:
                week_schedule.append(dict(zip(keys, values)))

            if index == len(results)-1:
                semester_schedule.append(week_schedule)

        schedule = dict(zip(weeks, semester_schedule))
        for i in range(2, 18):
            if str(i) not in schedule.keys():
                schedule[i] = []
        schedule = dict(sorted(schedule.items(), key=lambda x: int(x[0])))
        return schedule


    # def output_data(self):
    #     results = self.select_data()
    #     keys = ['course', 'teacher', 'location', 'time', 'start_time', 'end_time', 'week']
    #     semesters = []
    #     all_semester_schedule = []
    #     for index, result in enumerate(results):
    #         values = [result[i] for i in range(4, 11)]
    #
    #         if result[2] not in semesters:
    #             semesters.append(result[2])
    #             if index != 0:
    #                 all_semester_schedule.append(semester_schedule)
    #             weeks = []
    #             semester_schedule = {}
    #             week_schedule = []
    #         else:
    #             pass
    #
    #         if result[3] not in weeks:
    #             week = result[3]
    #             weeks.append(week)
    #             semester_schedule[str(int(week)-1)] = week_schedule
    #             week_schedule = [dict(zip(keys, values))]
    #         else:
    #             week_schedule.append(dict(zip(keys, values)))
    #
    #         if index == len(results)-1:
    #             all_semester_schedule.append(semester_schedule)
    #
    #     schedule = dict(zip(semesters, all_semester_schedule))
    #     return schedule


class InfoPipeline(GongGongPipeline):
    def __init__(self, xh):
        super(InfoPipeline, self).__init__(xh)
        self.update_time = 30

        self.create_table_sql = """ CREATE TABLE INFO (
                                    ID INT(255) PRIMARY KEY AUTO_INCREMENT,
                                    STUDENT_ID VARCHAR(255) NOT NULL,
                                    NAME VARCHAR(255) NOT NULL,
                                    GENDER VARCHAR(255) NOT NULL,
                                    DEPARTMENT VARCHAR(255) NOT NULL,
                                    MAJOR VARCHAR(255) NOT NULL,
                                    CLASS VARCHAR(255) NOT NULL,
                                    PHONE VARCHAR(255) ,
                                    QQ VARCHAR(255) ,
                                    EMAIL VARCHAR(255) 
                                    )"""

        self.insert_flag_sql = f"INSERT INTO FLAG (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE, EXAM) VALUES({self.xh}, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT INFO FROM FLAG WHERE STUDENT_ID={self.xh}"
        self.update_flag_sql = f"UPDATE FLAG SET INFO=1 WHERE STUDENT_ID={self.xh}"

        self.insert_data_sql = """INSERT INTO INFO (STUDENT_ID, NAME, GENDER, DEPARTMENT, MAJOR, CLASS, PHONE, QQ, EMAIL)
                                          VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM INFO WHERE STUDENT_ID={self.xh}"
        self.delete_data_sql = f"DELETE FROM INFO WHERE STUDENT_ID={self.xh}"

        self.insert_update_time_sql = f"""INSERT INTO UPDATE_TIME (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE, EXAM)
                                                          VALUES ({self.xh}, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT INFO FROM UPDATE_TIME WHERE STUDENT_ID={self.xh}"
        self.update_update_time_sql = f"UPDATE UPDATE_TIME SET INFO='{self.today.strftime('%Y-%m-%d')}' WHERE STUDENT_ID={self.xh}"

    def get_data_list(self, info):
        data = [v for v in info.values()]
        data.insert(0, self.xh)
        data_list = [data]
        return data_list


    def output_data(self):
        results = self.select_data()
        result = results[0]
        keys = ['name', 'gender', 'department', 'major', 'class', 'phone', 'qq', 'email']
        values = [result[i] for i in range(2, 10)]
        info = dict(zip(keys, values))
        return info


class AllSchedulePipeline(GongGongPipeline):
    def __init__(self, xh):
        super(AllSchedulePipeline, self).__init__(xh)
        self.update_time = 30

        self.create_table_sql = """CREATE TABLE ALLSCHEDULE (
                                    ID INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    STUDENT_ID VARCHAR(255) NOT NULL,
                                    SEMESTER VARCHAR(255) NOT NULL,
                                    WEEK VARCHAR(255) NOT NULL,
                                    COURSE VARCHAR(255) NOT NULL,
                                    TEACHER VARCHAR(255) NOT NULL,
                                    LOCATION VARCHAR(255),
                                    TIME VARCHAR(255) NOT NULL,
                                    START_TIME VARCHAR(255),
                                    END_TIME VARCHAR(255),
                                    WEEKS VARCHAR(255) NOT NULL )"""

        self.insert_flag_sql = f"INSERT INTO FLAG (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE, EXAM) VALUES({self.xh}, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT ALLSCHEDULE FROM FLAG WHERE STUDENT_ID={self.xh}"
        self.update_flag_sql = f"UPDATE FLAG SET ALLSCHEDULE=1 WHERE STUDENT_ID={self.xh}"

        self.insert_data_sql = """INSERT INTO ALLSCHEDULE (STUDENT_ID, SEMESTER, WEEK, COURSE, TEACHER, LOCATION, TIME, START_TIME, END_TIME, WEEKS)
                                          VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM ALLSCHEDULE WHERE STUDENT_ID={self.xh}"
        self.delete_data_sql = f"DELETE FROM ALLSCHEDULE WHERE STUDENT_ID={self.xh}"

        self.insert_update_time_sql = f"""INSERT INTO UPDATE_TIME (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE, EXAM)
                                                                                  VALUES ({self.xh}, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT ALLSCHEDULE FROM UPDATE_TIME WHERE STUDENT_ID={self.xh}"
        self.update_update_time_sql = f"UPDATE UPDATE_TIME SET ALLSCHEDULE='{self.today.strftime('%Y-%m-%d')}' WHERE STUDENT_ID={self.xh}"


    def get_data_list(self, schedule):
        data_list = []
        for semester, v in schedule.items():
            for week, items in v.items():
                for item in items:
                    if item:
                        data = (self.xh, semester, week, item['course'], item['teacher'], item['location'],
                                item['time'], item['start_time'], item['end_time'], item['week'])
                        data_list.append(data)
        return data_list


    def output_data(self):
        results = self.select_data()
        keys = ['course', 'teacher', 'location', 'time', 'start_time', 'end_time', 'week']
        semesters = []
        all_semester_schedule = []
        for index, result in enumerate(results):
            values = [result[i] for i in range(4, 11)]

            if result[2] not in semesters:
                semesters.append(result[2])
                if index != 0:
                    semester_schedule.pop('1')
                    all_semester_schedule.append(semester_schedule)
                weeks = []
                semester_schedule = {}
                week_schedule = []
            else:
                pass

            if result[3] not in weeks:
                week = result[3]
                weeks.append(week)
                semester_schedule[str(int(week)-1)] = week_schedule
                week_schedule = [dict(zip(keys, values))]
            else:
                week_schedule.append(dict(zip(keys, values)))

            if index == len(results)-1:
                semester_schedule.pop('1')
                all_semester_schedule.append(semester_schedule)

        schedule = dict(zip(semesters, all_semester_schedule))
        return schedule


class ExamPipeline(GongGongPipeline):
    def __init__(self, xh):
        super(ExamPipeline, self).__init__(xh)
        self.update_time = 30

        self.create_table_sql = """CREATE TABLE EXAM (
                                    ID INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    STUDENT_ID VARCHAR(255) NOT NULL,
                                    COURSE VARCHAR(255) NOT NULL,
                                    DATE VARCHAR(255) NOT NULL,
                                    WEEK VARCHAR(255) NOT NULL,
                                    DAY VARCHAR(255) NOT NULL,
                                    START_TIME VARCHAR(255) NOT NULL,
                                    END_TIME VARCHAR(255) NOT NULL,
                                    LOCATION VARCHAR(255) NOT NULL
                                    )"""

        self.insert_flag_sql = f"INSERT INTO FLAG (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE, EXAM) VALUES({self.xh}, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT EXAM FROM FLAG WHERE STUDENT_ID={self.xh}"
        self.update_flag_sql = f"UPDATE FLAG SET EXAM=1 WHERE STUDENT_ID={self.xh}"

        self.insert_data_sql = """INSERT INTO EXAM (STUDENT_ID, COURSE, DATE, WEEK, DAY, START_TIME, END_TIME, LOCATION)
                                          VALUES (%s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM EXAM WHERE STUDENT_ID={self.xh}"
        self.delete_data_sql = f"DELETE FROM EXAM WHERE STUDENT_ID={self.xh}"

        self.insert_update_time_sql = f"""INSERT INTO UPDATE_TIME (STUDENT_ID, INFO, GRADE, SCHEDULE, ALLSCHEDULE, EXAM)
                                                                                  VALUES ({self.xh}, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT EXAM FROM UPDATE_TIME WHERE STUDENT_ID={self.xh}"
        self.update_update_time_sql = f"UPDATE UPDATE_TIME SET EXAM='{self.today.strftime('%Y-%m-%d')}' WHERE STUDENT_ID={self.xh}"


    def get_data_list(self, exam):
        data_list = []
        for item in exam:
            data = [x for x in item.values()]
            data.insert(0, self.xh)
            data_list.append(data)
        return data_list


    def output_data(self):
        results = self.select_data()
        keys = ['student_id', 'course', 'date', 'week', 'day', 'start_time', 'end_time', 'location']
        exam = []
        for result in results:
            values = [result[i] for i in range(1, 9)]
            values.insert(0, self.xh)
            exam.append(dict(zip(keys, values)))
        return exam


if __name__ == '__main__':
    pass
    # xh = '201805710203'
    # pwd = 'SKTFaker11'
    # spider = PersonalSpider(xh, pwd)
    # grade = spider.get_grade()
    # xq = '2018-2019-1'
    # schedule = spider.get_one_schedule(xq)
    # allschedule = spider.get_all_schedule()
    # print(schedule)
    # info = spider.get_info()
    # exam = spider.get_exam()


    # GradesPipeline测试
    # pipeline1 = GradePipeline(xh)
    # print(pipeline1.today)
    # pipeline1.output_data()
    # pipeline1.create_table()
    # print(pipeline1.today)
    # print(pipeline1.get_flag())
    # print(pipeline1.get_data_list(grade))
    # print(pipeline1.check_update_time())
    # pipeline1.insert_data(grade)

    # SchedulePipeline测试
    # pipeline2 = SchedulePipeline(xh)
    # print(pipeline2.get_flag())
    # print(pipeline2.get_data_list(schedule, xq))
    # schedule = pipeline2.output_data()
    # schedule = pipeline2.select_data()
    # print(schedule)
    # pipeline2.insert_data(schedule, xq)
    # pipeline2.create_table()


    #InfoPipeline测试
    # pipline3 = InfoPipeline(xh)
    # info = pipline3.output_data()
    # print(info)
    # pipline3.create_table()
    # pipline3.insert_data(info)
    # print(date.today())



    #AllSchedulePipeline测试
    # pipeline4 = AllSchedulePipeline(xh)
    # pipeline4.create_table()
    # print(pipeline4.get_flag())
    # pipeline4.insert_data(allschedule)
    # print(pipeline4.output_data())


    # ExamPipeline测试
    # pipeline5 = ExamPipeline(xh)
    # print(pipeline5.get_data_list(exam))
    # pipeline5.create_table()
    # pipeline5.insert_data(exam)