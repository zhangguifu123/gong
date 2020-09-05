import pymysql
from config import CONNECTION
from datetime import date, timedelta
from spider import PersonalSpider, JWXTSpider

class GongGongPipeline():
    """
    继承GongGongPipeline类
    只需重写sql语句，以及get_data_list和output_data方法
    """
    def __init__(self, sid=None):
        self.connection = CONNECTION
        self.connection.ping(reconnect=True)
        self.cursor = self.connection.cursor()
        self.sid = sid
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
    def __init__(self, sid):
        super(GradePipeline, self).__init__(sid)
        self.update_time = 7

        self.create_table_sql = """CREATE TABLE grades (
                                    id INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    name VARCHAR(255) NOT NULL,
                                    sid VARCHAR(255) NOT NULL,
                                    course VARCHAR(255) NOT NULL,
                                    comp_grade VARCHAR(255) NOT NULL,
                                    type VARCHAR(255),
                                    class_type VARCHAR(255),
                                    term VARCHAR(255) NOT NULL,
                                    nature_of_test VARCHAR(255),
                                    credit FLOAT NOT NULL
                                    )"""

        self.insert_flag_sql = f"INSERT INTO flag (sid, info, grades, schedule, all_schedule, exam, gpa) VALUES({self.sid}, 0, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT grades FROM flag WHERE sid={self.sid}"
        self.update_flag_sql = f"UPDATE flag SET grades=1 WHERE sid={self.sid}"

        self.insert_data_sql = """INSERT INTO grades (name, sid, course, comp_grade, type, class_type, term, nature_of_test, credit)
                                  VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM grades WHERE sid={self.sid}"
        self.delete_data_sql = f"DELETE FROM grades WHERE sid={self.sid}"

        self.insert_update_time_sql = f"""INSERT INTO update_time (sid, info, grades, schedule, all_schedule, exam, gpa)
                                                          VALUES ({self.sid}, NULL, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT grades FROM update_time WHERE sid={self.sid}"
        self.update_update_time_sql = f"UPDATE update_time SET grades='{self.today.strftime('%Y-%m-%d')}' WHERE sid={self.sid}"


    def get_data_list(self, grade):
        data_list = []
        for items in grade.values():
            for item in items:
                data = (item['name'], item['sid'], item['course'], item['comp_grade'], item['type'],
                        item['class_type'],item['term'], item['nature_of_test'], item['credit'])
                data_list.append(data)
        return data_list


    def output_data(self):
        results = self.select_data()
        keys = ['name', 'sid', 'course', 'comp_grade', 'type', 'class_type', 'term', 'nature_of_test', 'credit']
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
    def __init__(self, sid):
        super(SchedulePipeline, self).__init__(sid)
        self.update_time = 7

        self.create_table_sql = """CREATE TABLE schedule (
                                    id INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    sid VARCHAR(255) NOT NULL,
                                    term VARCHAR(255) NOT NULL,
                                    week VARCHAR(255) NOT NULL,
                                    course VARCHAR(255) NOT NULL,
                                    teacher VARCHAR(255) NOT NULL,
                                    location VARCHAR(255),
                                    day VARCHAR(255) NOT NULL,
                                    section_start VARCHAR(255) NOT NULL,
                                    section_end VARCHAR(255) NOT NULL,
                                    section_length VARCHAR(255) NOT NULL,
                                    start_time VARCHAR(255),
                                    end_time VARCHAR(255),
                                    weeks VARCHAR(255) NOT NULL,
                                    week_string VARCHAR(255) NOT NULL
                                    )"""

        self.insert_flag_sql = f"INSERT INTO flag (sid, info, grades, schedule, all_schedule, exam, gpa) VALUES({self.sid}, 0, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT schedule FROM flag WHERE sid={self.sid}"
        self.update_flag_sql = f"UPDATE flag SET schedule=1 WHERE sid={self.sid}"


        self.insert_data_sql = """INSERT INTO schedule (sid, term, week, course, teacher, location, day, section_start, section_end, section_length, start_time, end_time, weeks, week_string)
                                  VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM schedule WHERE sid={self.sid}"
        self.delete_data_sql = f"DELETE FROM schedule WHERE sid={self.sid}"

        self.insert_update_time_sql = f"""INSERT INTO update_time (sid, info, grades, schedule, all_schedule, exam, gpa)
                                                                  VALUES ({self.sid}, NULL, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT schedule FROM update_time WHERE sid={self.sid}"
        self.update_update_time_sql = f"UPDATE update_time SET schedule='{self.today.strftime('%Y-%m-%d')}' WHERE sid={self.sid}"

    def get_data_list(self, schedule, semester):
        data_list = []
        for week, items in schedule.items():
            # for item in items:
            #     if item:
            #         data = [self.sid, semester, week, item['course'], item['teacher'], item['location'],
            #                 item['day'], item['section_start'], item['section_end'], item['section_length'], item['start_time'], item['end_time'], item['week'], item['week_string']]
            #         data_list.append(data)
            for day, _item in items.items():
                if _item:
                    for item in _item:
                        data = [self.sid, semester, week, item['course'], item['teacher'], item['location'],item['day'],
                                item['section_start'], item['section_end'], item['section_length'], item['start_time'], item['end_time'], item['week'], item['week_string']]
                        data_list.append(data)
        return data_list


    def output_data(self):
        results = self.select_data()
        keys = ['course', 'teacher', 'location', 'day', 'section_start', 'section_end', 'section_length', 'start_time', 'end_time', 'week', 'week_string']
        semester_schedule = []
        weeks = []
        week_schedule = {
            '1': [],
            '2': [],
            '3': [],
            '4': [],
            '5': [],
            '6': [],
            '7': [],
        }
        for index, result in enumerate(results):
            values = [result[i] for i in range(4, 15)]
            if result[3] not in weeks:
                if index != 0:
                    semester_schedule.append(week_schedule)
                    week_schedule = {
                        '1': [],
                        '2': [],
                        '3': [],
                        '4': [],
                        '5': [],
                        '6': [],
                        '7': [],
                    }
                week = result[3]
                weeks.append(week)
                week_schedule[result[7]].append(dict(zip(keys, values)))
            else:
                week_schedule[result[7]].append(dict(zip(keys, values)))

            if index == len(results)-1:
                semester_schedule.append(week_schedule)

        schedule = dict(zip(weeks, semester_schedule))
        for i in range(2, 18):
            if str(i) not in schedule.keys():
                schedule[str(i)] = {
                    '1': [],
                    '2': [],
                    '3': [],
                    '4': [],
                    '5': [],
                    '6': [],
                    '7': [],
                }
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
    def __init__(self, sid):
        super(InfoPipeline, self).__init__(sid)
        self.update_time = 30

        self.create_table_sql = """ CREATE TABLE info (
                                    id INT(255) PRIMARY KEY AUTO_INCREMENT,
                                    sid VARCHAR(255) NOT NULL,
                                    name VARCHAR(255) NOT NULL,
                                    sex VARCHAR(255) NOT NULL,
                                    college VARCHAR(255) NOT NULL,
                                    major VARCHAR(255) NOT NULL,
                                    class VARCHAR(255) NOT NULL,
                                    phone VARCHAR(255) ,
                                    qq VARCHAR(255) ,
                                    email VARCHAR(255)
                                    )"""

        self.insert_flag_sql = f"INSERT INTO flag (sid, info, grades, schedule, all_schedule, exam, gpa) VALUES({self.sid}, 0, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT info FROM flag WHERE sid={self.sid}"
        self.update_flag_sql = f"UPDATE flag SET info=1 WHERE sid={self.sid}"

        self.insert_data_sql = """INSERT INTO info (sid, name, sex, college, major, class, phone, qq, email)
                                          VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM info WHERE sid={self.sid}"
        self.delete_data_sql = f"DELETE FROM info WHERE sid={self.sid}"

        self.insert_update_time_sql = f"""INSERT INTO update_time (sid, info, grades, schedule, all_schedule, exam, gpa)
                                                          VALUES ({self.sid}, NULL, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT info FROM update_time WHERE sid={self.sid}"
        self.update_update_time_sql = f"UPDATE update_time SET info='{self.today.strftime('%Y-%m-%d')}' WHERE sid={self.sid}"

    def get_data_list(self, info):
        data = [v for v in info.values()]
        data.insert(0, self.sid)
        data_list = [data]
        return data_list


    def output_data(self):
        results = self.select_data()
        result = results[0]
        keys = ['name', 'sex', 'college', 'major', 'class', 'phone', 'qq', 'email']
        values = [result[i] for i in range(2, 10)]
        info = dict(zip(keys, values))
        return info


# class AllSchedulePipeline(GongGongPipeline):
#     def __init__(self, sid):
#         super(AllSchedulePipeline, self).__init__(sid)
#         self.update_time = 30
#
#         self.create_table_sql = """CREATE TABLE all_schedule (
#                                     id INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
#                                     sid VARCHAR(255) NOT NULL,
#                                     term VARCHAR(255) NOT NULL,
#                                     week VARCHAR(255) NOT NULL,
#                                     course VARCHAR(255) NOT NULL,
#                                     teacher VARCHAR(255) NOT NULL,
#                                     location VARCHAR(255),
#                                     day VARCHAR(255) NOT NULL,
#                                     section_start VARCHAR(255) NOT NULL,
#                                     section_end VARCHAR(255) NOT NULL,
#                                     section_length VARCHAR(255) NOT NULL,
#                                     start_time VARCHAR(255),
#                                     end_time VARCHAR(255),
#                                     weeks VARCHAR(255) NOT NULL,
#                                     week_string VARCHAR(255) NOT NULL
#                                     )"""
#
#         self.insert_flag_sql = f"INSERT INTO flag (sid, info, grades, schedule, all_schedule, exam) VALUES({self.sid}, 0, 0, 0, 0, 0)"
#         self.check_flag_sql = f"SELECT all_schedule FROM flag WHERE sid={self.sid}"
#         self.update_flag_sql = f"UPDATE flag SET all_schedule=1 WHERE sid={self.sid}"
#
#         self.insert_data_sql = """INSERT INTO all_schedule (sid, term, week, course, teacher, location, day, section_start, section_end, section_length, start_time, end_time, weeks， week_string)
#                                           VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
#         self.select_data_sql = f"SELECT * FROM all_schedule WHERE sid={self.sid}"
#         self.delete_data_sql = f"DELETE FROM all_schedule WHERE sid={self.sid}"
#
#         self.insert_update_time_sql = f"""INSERT INTO update_time (sid, info, grades, schedule, all_schedule, exam)
#                                                                           VALUES ({self.sid}, NULL, NULL, NULL, NULL, NULL)"""
#         self.get_update_time_sql = f"SELECT all_schedule FROM update_time WHERE sid={self.sid}"
#         self.update_update_time_sql = f"UPDATE update_time SET all_schedule='{self.today.strftime('%Y-%m-%d')}' WHERE sid={self.sid}"
#
#
#     def get_data_list(self, schedule):
#         data_list = []
#         for semester, v in schedule.items():
#             for week, items in v.items():
#                 # for item in items:
#                 #     if item:
#                 #         data = (self.sid, semester, week, item['course'], item['teacher'], item['location'],
#                 #                 item['day'], item['section_start'], item['section_end'], item['section_length'], item['start_time'], item['end_time'], item['week'], item['week_string'])
#                 #         data_list.append(data)
#                 for day, _item in items.items():
#                     if _item:
#                         for item in _item:
#                             data = [self.sid, semester, week, item['course'], item['teacher'], item['location'],
#                                     item['day'],
#                                     item['section_start'], item['section_end'], item['section_length'],
#                                     item['start_time'], item['end_time'], item['week'], item['week_string']]
#                             data_list.append(data)
#         return data_list
#
#
#     def output_data(self):
#         results = self.select_data()
#         keys = ['course', 'teacher', 'location', 'day', 'section_start', 'section_end', 'section_length', 'start_time', 'end_time', 'week', 'week_string']
#         semesters = []
#         all_semester_schedule = []
#         for index, result in enumerate(results):
#             values = [result[i] for i in range(4, 15)]
#
#             if result[2] not in semesters:
#                 semesters.append(result[2])
#                 if index != 0:
#                     semester_schedule.pop('1')
#                     all_semester_schedule.append(semester_schedule)
#                 weeks = []
#                 semester_schedule = {}
#                 week_schedule = []
#             else:
#                 pass
#
#             if result[3] not in weeks:
#                 week = result[3]
#                 weeks.append(week)
#                 semester_schedule[str(int(week)-1)] = week_schedule
#                 week_schedule = [dict(zip(keys, values))]
#             else:
#                 week_schedule.append(dict(zip(keys, values)))
#
#             if index == len(results)-1:
#                 semester_schedule.pop('1')
#                 all_semester_schedule.append(semester_schedule)
#
#         schedule = dict(zip(semesters, all_semester_schedule))
#         return schedule


class ExamPipeline(GongGongPipeline):
    def __init__(self, sid):
        super(ExamPipeline, self).__init__(sid)
        self.update_time = 30

        self.create_table_sql = """CREATE TABLE exam (
                                    id INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    sid VARCHAR(255) NOT NULL,
                                    course VARCHAR(255) NOT NULL,
                                    date VARCHAR(255) NOT NULL,
                                    week VARCHAR(255) NOT NULL,
                                    day VARCHAR(255) NOT NULL,
                                    start_time VARCHAR(255) NOT NULL,
                                    end_time VARCHAR(255) NOT NULL,
                                    location VARCHAR(255) NOT NULL
                                    )"""

        self.insert_flag_sql = f"INSERT INTO flag (sid, info, grades, schedule, all_schedule, exam, gpa) VALUES({self.sid}, 0, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT exam FROM flag WHERE sid={self.sid}"
        self.update_flag_sql = f"UPDATE flag SET exam=1 WHERE sid={self.sid}"

        self.insert_data_sql = """INSERT INTO exam (sid, course, date, week, day, start_time, end_time, location)
                                          VALUES (%s, %s, %s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM exam WHERE sid={self.sid}"
        self.delete_data_sql = f"DELETE FROM exam WHERE sid={self.sid}"

        self.insert_update_time_sql = f"""INSERT INTO update_time (sid, info, grades, schedule, all_schedule, exam, gpa)
                                                                                  VALUES ({self.sid}, NULL, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT exam FROM update_time WHERE sid={self.sid}"
        self.update_update_time_sql = f"UPDATE update_time SET exam='{self.today.strftime('%Y-%m-%d')}' WHERE sid={self.sid}"


    def get_data_list(self, exam):
        data_list = []
        for item in exam:
            data = [x for x in item.values()]
            data.insert(0, self.sid)
            data_list.append(data)
        return data_list


    def output_data(self):
        results = self.select_data()
        keys = ['sid', 'course', 'date', 'week', 'day', 'start_time', 'end_time', 'location']
        exam = []
        for result in results:
            values = [result[i] for i in range(1, 9)]
            exam.append(dict(zip(keys, values)))
        return exam


class GPAPipeline(GongGongPipeline):
    def __init__(self, sid):
        super(GPAPipeline, self).__init__(sid)
        self.update_time = 7

        self.create_table_sql = """CREATE TABLE gpa (
                                    id INT(255) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    sid VARCHAR(255) NOT NULL,
                                    term VARCHAR(255) NOT NULL,
                                    gpa VARCHAR(255) NOT NULL,
                                    average_grade VARCHAR(255) NOT NULL,
                                    gpa_class_rank VARCHAR(255) NOT NULL,
                                    gpa_major_rank VARCHAR(255) NOT NULL
                                    )"""

        self.insert_flag_sql = f"INSERT INTO flag (sid, info, grades, schedule, all_schedule, exam, gpa) VALUES({self.sid}, 0, 0, 0, 0, 0, 0)"
        self.check_flag_sql = f"SELECT gpa FROM flag WHERE sid={self.sid}"
        self.update_flag_sql = f"UPDATE flag SET gpa=1 WHERE sid={self.sid}"

        self.insert_data_sql = """INSERT INTO gpa (sid, term, gpa, average_grade, gpa_class_rank, gpa_major_rank)
                                          VALUES (%s, %s, %s, %s, %s, %s)"""
        self.select_data_sql = f"SELECT * FROM gpa WHERE sid={self.sid}"
        self.delete_data_sql = f"DELETE FROM gpa WHERE sid={self.sid}"

        self.insert_update_time_sql = f"""INSERT INTO update_time (sid, info, grades, schedule, all_schedule, exam, gpa)
                                                                                  VALUES ({self.sid}, NULL, NULL, NULL, NULL, NULL, NULL)"""
        self.get_update_time_sql = f"SELECT gpa FROM update_time WHERE sid={self.sid}"
        self.update_update_time_sql = f"UPDATE update_time SET gpa='{self.today.strftime('%Y-%m-%d')}' WHERE sid={self.sid}"


    def get_data_list(self, all_gpa):
        data_list = []
        for item in all_gpa.values():
            data = [x for x in item.values()]
            data.insert(0, self.sid)
            data_list.append(data)
        return data_list


    def output_data(self, term):
        results = self.select_data()
        result = list(filter(lambda x: x[2] == term, results))[0]
        keys = ['term', 'gpa', 'average_grade', 'gpa_class_rank', 'gpa_major_rank']
        values = [result[i] for i in range(2, 7)]
        gpa = dict(zip(keys, values))
        return gpa






if __name__ == '__main__':
    pass
    # sid = '201805710203'
    # pwd = 'SKTFaker11'
    # spider = PersonalSpider(sid, pwd)
    # grade = spider.get_grade()
    # xq = '2018-2019-1'
    # schedule = spider.get_schedule(xq)
    # all_schedule = spider.get_all_schedule()
    # print(schedule)
    # info = spider.get_info()
    # exam = spider.get_exam()
    # spider = JWXTSpider(sid, pwd)
    # gpa = spider.get_all_gpa()
    # gpa = spider.get_gpa('2018-2019-2')


    # GradesPipeline测试
    # pipeline1 = GradePipeline(sid)
    # print(pipeline1.today)
    # pipeline1.output_data()
    # pipeline1.create_table()
    # print(pipeline1.today)
    # print(pipeline1.get_flag())
    # print(pipeline1.get_data_list(grade))
    # print(pipeline1.check_update_time())
    # pipeline1.insert_data(grade)

    # SchedulePipeline测试
    # pipeline2 = SchedulePipeline(sid)
    # print(pipeline2.get_flag())
    # print(pipeline2.get_data_list(schedule, xq))
    # schedule = pipeline2.output_data()
    # schedule = pipeline2.select_data()
    # print(schedule)
    # pipeline2.insert_data(schedule, xq)
    # pipeline2.create_table()


    #InfoPipeline测试
    # pipline3 = InfoPipeline(sid)
    # info = pipline3.output_data()
    # print(info['name'])
    # pipline3.create_table()
    # pipline3.insert_data(info)
    # print(date.today())



    #AllSchedulePipeline测试
    # pipeline4 = AllSchedulePipeline(sid)
    # pipeline4.create_table()
    # print(pipeline4.get_flag())
    # pipeline4.insert_data(allschedule)
    # print(pipeline4.get_data_list(all_schedule))
    # print(pipeline4.output_data())


    # ExamPipeline测试
    # pipeline5 = ExamPipeline(sid)
    # print(pipeline5.get_data_list(exam))
    # pipeline5.create_table()
    # pipeline5.insert_data(exam)


    # GPAPipeline测试
    # pipeline6 = GPAPipeline(sid)
    # pipeline6.create_table()
    # print(pipeline6.get_data_list(gpa))
    # pipeline6.insert_data(gpa)
    # print(pipeline6.output_data('2018-2019-2'))
