import requests
from lxml import etree
import re
from threading import Thread, Lock
from queue import Queue
import http.cookiejar
import urllib.request


class Ecard():

	Headers = {
		'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36'
	}

	def __init__(self, xh, pwd):
		self.xh = xh
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

		url = f'http://ecard.xtu.edu.cn/loginstudent.action?name={self.xh}&passwd={self.pwd}&userType=1&loginType=2'
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
	ecard = Ecard(xh='201805710203', pwd='191850')
	# results = ecard.query_bill(StartDate='20191101', EndDate='20191130')
	# print(len(results))

	results = ecard.get_today_bill()
	print(results)