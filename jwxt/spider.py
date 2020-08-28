import requests
import re
from lxml import etree
from datetime import datetime
from threading import Thread, Lock
from queue import LifoQueue, Queue
import http.cookiejar
import urllib.request

# 通过 week 获取 week_string
def get_week_string(string):
	if '-' not in string:
		result = string
	elif ',' not in string:
		num1 = int(string.split('-')[0])
		num2 = int(string.split('-')[1])
		week = [str(i) for i in range(num1, num2+1)]
		result = ','.join(week)
	else:
		num1 = int(string.split(',')[0].split('-')[0])
		num2 = int(string.split(',')[0].split('-')[1])
		num3 = int(string.split(',')[1].split('-')[0])
		num4 = int(string.split(',')[1].split('-')[1])
		week = [str(i) for i in range(num1, num2+1)] + [str(i) for i in range(num3, num4+1)]
		result = ','.join(week)

	return result


class PersonalSpider():
    url = 'http://jwxt.xtu.edu.cn/app.do'
    HEADERS = {
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        'Accept-Encoding': 'gzip, deflate',
        'Headers': 'text/json;charset=utf-8',
        'Accept-Language': 'zh-CN,zh;q=0.9',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.129 Safari/537.36'
    }
    all_schedule = {}
    def __init__(self, sid, pwd):
        # self.method = method
        self.sid = sid
        self.params = {
            'method': '',
            'xh': sid,
            'pwd': pwd,
        }
        self.queue = self.get_query_range()
        self.get_token()
        self.lock = Lock()


    def get_token(self):
        self.params['method'] = 'authUser'
        response = requests.get(self.url, params=self.params, headers=self.HEADERS)
        self.HEADERS['token'] = response.json()['token']
        self.msg = 1 if response.json()['msg'] == '登录成功' else 0
        self.params.pop('pwd')


    def get_info(self):
        if self.msg == 0:
            return dict(errcode='-4031', status='400', errmsg='Username or password error', data=[])
        # self.params['method'] = self.method
        self.params['method'] = 'getUserInfo'
        response = requests.get(self.url, params=self.params, headers=self.HEADERS)
        text = response.json()

        info = {}
        info['name'] = text['xm']  # 学生姓名
        info['sex'] = text['xb']  # 性别
        info['college'] = text['yxmc']  # 院系名称
        info['major'] = text['zymc']  # 专业名称
        info['class'] = text['bj']  # 班级名称
        info['phone'] = text['dh']  # 联系电话
        info['qq'] = text['qq']  # qq
        info['email'] = text['email']  # email

        return info


    def get_grade(self):
        if self.msg == 0:
            return dict(errcode='-4031', status='400', errmsg='Username or password error', data=[])

        self.params['method'] = 'getCjcx'
        response = requests.get(self.url, params=self.params, headers=self.HEADERS)
        _grades = response.json()

        grades = {}
        for _grade in _grades:
            grade = {}
            grade['name']       = _grade['xm']  # 姓名
            grade['sid'] = self.sid       # 学号
            grade['course']     = _grade['kcmc']  # 课程名
            grade['comp_grade']      = _grade['zcj']  # 成绩
            grade['type']   = _grade['kclbmc']  # 课程类别，如必修
            grade['class_type'] = _grade['kcxzmc']  # 课程性质，如公共基础课
            grade['term']   = _grade['xqmc']  # 学期，如2018-2019-1
            grade['nature_of_test']   = _grade['ksxzmc']  # 考试性质，如正常考试、重修
            grade['credit']    = _grade['xf']  # 学分
            if grade['term'] not in grades.keys():
                grades[grade['term']] = [grade]
            else:
                grades[grade['term']].append(grade)

        return grades


    def get_query_range(self):
        start = int(self.sid[0:4])
        end = datetime.now().year
        month = datetime.now().month

        queue = LifoQueue()
        for i in range(start, end):
            queue.put(f'{i}-{i+1}-1')
            # query.put('%s-%s-1' % (i, i+1))
            queue.put(f'{i}-{i+1}-2')
            # query.put('%s-%s-2' % (i, i+1))

        if 1 <= month < 3:
            queue.get()
        elif 9 <= month:
            queue.put(f'{end}-{end+1}-1')
            # query.put('%s-%s-2' % (end, end+1))

        return queue


    def get_all_schedule(self):
        if self.msg == 0:
            return dict(errcode='-4031', status='400', errmsg='Username or password error', data=[])

        threads = []
        num  = self.queue.qsize()
        for i in range(num):
            t = Thread(target=self.get_one_schedule, args=(self.all_schedule, self.queue))
            threads.append(t)
        for t in threads:
            t.start()
        for t in threads:
            t.join()
        return self.all_schedule


    def get_one_schedule(self, all_schedule, queue):
        self.params['method'] = 'getKbcxAzc'
        q = queue.get()
        print(q)
        with self.lock:
            self.params['xnxqid'] = q
            result = {}
            for i in range(2, 18):
                i = str(i)
                self.params['zc'] = i
                res = requests.get(self.url, params=self.params, headers=self.HEADERS)
                schedules = res.json()
                items = []
                for schedule in schedules:
                    item = {}
                    try:
                        item['course'] = schedule['kcmc']  # 课程名称
                        item['teacher'] = schedule['jsxm']  # 老师名称
                        item['location'] = schedule['jsmc']  # 课程地点
                        # item['time'] = schedule['kcsj']  # 上课时间， 格式为x0a0b 表示为 周x第a节到第b节
                        item['day'] = schedule['kcsj'][0]
                        item['section_start'] = str(int(schedule['kcsj'][1:3]))
                        item['section_end'] = str(int(schedule['kcsj'][3:5]))
                        item['section_length'] = str(int(item['section_end']) - int(item['section_start']) + 1)
                        item['start_time'] = schedule['kssj']  # 开始时间
                        item['end_time'] = schedule['jssj']  # 结束时间
                        item['week'] = schedule['kkzc']  # 开课周次
                        item['week_string'] = get_week_string(item['week'])
                    except Exception as e:
                        print(e)
                    items.append(item)
                result[i] = items
        all_schedule[q] = result


    def get_schedule(self, xq):
        if self.msg == 0:
            return dict(errcode='-4031', status='400', errmsg='Username or password error', data=[])

        self.params['method'] = 'getKbcxAzc'
        self.params['xnxqid'] = xq
        result = {}
        for i in range(2, 18):
            i = str(i)
            self.params['zc'] = i
            res = requests.get(self.url, params=self.params, headers=self.HEADERS)
            schedules = res.json()
            items = []
            for schedule in schedules:
                item = {}
                try:
                    item['course'] = schedule['kcmc']  # 课程名称
                    item['teacher'] = schedule['jsxm']  # 老师名称
                    item['location'] = schedule['jsmc']  # 课程地点
                    # item['time'] = schedule['kcsj']  # 上课时间， 格式为x0a0b 表示为 周x第a节到第b节
                    item['day'] = schedule['kcsj'][0]
                    item['section_start'] = str(int(schedule['kcsj'][1:3]))
                    item['section_end'] = str(int(schedule['kcsj'][3:5]))
                    item['section_length'] = str(int(item['section_end']) - int(item['section_start']) + 1)
                    item['start_time'] = schedule['kssj']  # 开始时间
                    item['end_time'] = schedule['jssj']  # 结束时间
                    item['week'] = schedule['kkzc']  # 开课周次
                    item['week_string'] = get_week_string(item['week'])
                except:
                    pass
                if item:
                    items.append(item)
            result[i] = items
        return result


    def get_now_schedule(self):
        results = self.get_schedule('2020-2021-1')
        return results


    def get_exam(self):
        if self.msg == 0:
            return dict(errcode='-4031', status='400', errmsg='Username or password error', data=[])
        # self.params['method'] = self.method
        self.params['method'] = 'getKscx'
        response = requests.get(self.url, params=self.params, headers=self.HEADERS)
        items = response.json()
        results = []
        for item in items:
            str1 = item['ksqssj']
            str2 = item['vksjc']
            pattern = r"第(\d*)周(星期.{1})"
            result = re.match(pattern, str2)

            location = item['jsmc']
            course = item['kcmc']
            datetime = str1.split('~')
            date = datetime[0].split(' ')[0]
            start_time = datetime[0].split(" ")[1]
            end_time = datetime[1].split(" ")[1]
            week = result.group(1)
            day = result.group(2)

            exam = dict(course=course, date=date, week=week, day=day, start_time=start_time,
                        end_time=end_time, location=location)
            results.append(exam)
        return results


class EcardSpider():
	Headers = {
		'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36'
	}

	def __init__(self, sid, pwd):
		self.sid = sid
		self.pwd = pwd
		self.get_cookie()
		self.get_ecard_info()
		self.lock = Lock()


	def get_cookie(self):
		'''
		用requests库模拟请求时，会出现没有cookie情况
		所以换成了用urllib库去请求
		'''

		# url = 'http://ecard.xtu.edu.cn/loginstudent.action'
		# data = {
		# 	'name': self.xh,
		# 	'passwd': self.pwd,
		# 	'userType': '1',
		# 	'loginType': '2'
		# }
		# res = requests.post(url, headers=self.Headers, data=data)
		# print(res.cookies)
		# Cookie = res.cookies.get_dict()
		# string = ''
		# for k, v in Cookie.items():
		# 	string += f'{k}={v};'

		cookie = http.cookiejar.CookieJar()
		handler = urllib.request.HTTPCookieProcessor(cookie)
		opener = urllib.request.build_opener(handler)

		url = f'http://ecard.xtu.edu.cn/loginstudent.action?name={self.sid}&passwd={self.pwd}&userType=1&loginType=2'
		response = opener.open(url)
		string = ''
		for item in cookie:
			string += f'{item.name}={item.value}'

		self.Headers['Cookie'] = string


	def get_ecard_info(self):
		url = 'http://ecard.xtu.edu.cn/accountcardUser.action'
		res = requests.get(url, headers=self.Headers)
		html = etree.HTML(res.text)
		items = html.xpath("//table//table//table//table//tr")

		name = items[1].xpath('./td[2]/div/text()')[0] # 姓名
		ecard_id = items[1].xpath('./td[4]/div/text()')[0] # 卡号

		string = str(items[12].xpath('./td[2]/text()')[0])
		balance = string.split('元')[0] # 余额
		string = string.split('元')[1]
		unclaimed = string.split('）')[1] # 待领金额

		s0 = items[11].xpath('./td[4]/div/text()')[0] # 卡状态
		s1 = items[11].xpath('./td[6]/div/text()')[0] # 冻结状态
		s2 = items[12].xpath('./td[6]/text()')[0].replace('\xa0', '') # 挂失状态

		# card_status = {}
		# card_status['0'] = s0
		# card_status['1'] = s1
		# card_status['2'] = s2
		if s0 == '正常':
			status = '0'
		elif s1 != '正常':
			status = '1'
		elif s2 != '正常':
			status = '2'

		self.ecard_id = ecard_id
		self.data = dict(name=name, ecard_id=ecard_id, balance=balance,
			unclaimed=unclaimed, status=status)


	def get_today_bill(self):
		url = 'http://ecard.xtu.edu.cn/accounttodatTrjnObject.action'
		data = {
			'account': self.ecard_id,
			'inputObject': 'all',
			'Submit': '(unable to decode value)'
		}
		res = requests.post(url, headers=self.Headers, data=data)
		pattern = r'共(\d+)页'
		num = int(re.findall(pattern, res.text)[0])

		results = []
		if num >= 1:
			q = Queue()
			for i in range(1, num+1):
				q.put(i)

			threads = []
			for i in range(num):
				t = Thread(target=self.get_one_page_bill, args=(q, url, results))
				threads.append(t)
			for t in threads:
				t.start()
			for t in threads:
				t.join()

		return results


	def query_bill(self, StartDate, EndDate):
		data1 = {
			'account': self.ecard_id,
			'inputObject': 'all',
			'Submit': '(unable to decode value)'
		}
		url1 = "http://ecard.xtu.edu.cn/accounthisTrjn1.action"
		res1 = requests.post(url1, headers=self.Headers, data=data1)

		data2 = {
			'inputStartDate': StartDate,
			'inputEndDate': EndDate,
		}
		url2 = "http://ecard.xtu.edu.cn/accounthisTrjn2.action"
		res2 = requests.post(url2, headers=self.Headers, data=data2)

		url3 = "http://ecard.xtu.edu.cn/accounthisTrjn3.action"
		res3 = requests.post(url3, headers=self.Headers)
		pattern = r'共(\d+)页'
		num = int(re.findall(pattern, res3.text)[0])


		url4 = "http://ecard.xtu.edu.cn/accountconsubBrows.action"

		results = []
		if num >= 1:
			q = Queue()
			for i in range(1, num+1):
				q.put(i)

			threads = []
			for i in range(num):
				t = Thread(target=self.get_one_page_bill, args=(q, url4, results))
				threads.append(t)
			for t in threads:
				t.start()
			for t in threads:
				t.join()

			return results


	def get_one_page_bill(self, queue, url, results):
		i = queue.get()
		data = dict(pageNum=str(i))
		res = requests.post(url, headers=self.Headers, data=data)
		html = etree.HTML(res.text)
		items = html.xpath("//table//table[2]//table//tr")

		for item in items[1:-1]:
			date = item.xpath("./td[1]/text()")[0]
			try:
				location = item.xpath("./td[5]/text()")[0].replace(' ', '')
			except:
				location = ''
			amount = item.xpath("./td[6]/text()")[0]
			balance = item.xpath("./td[7]/text()")[0]
			time = item.xpath("./td[8]/text()")[0]
			with self.lock:
				results.append(dict(date=date, location=location, amount=amount,
					balance=balance, time=time))


if __name__ == '__main__':
    pass
    sid = '201805710203'
    pwd = 'SKTFaker11'
    spider = PersonalSpider(sid, pwd)
    # print(spider.get_exam_info())
    # print(spider.get_exam())
    spider.get_schedule('2018-2019-1')