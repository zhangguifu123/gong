import json
import requests
import config
from flask import Flask, request, abort, make_response
from spider import PersonalSpider, EcardSpider
from pipeline import InfoPipeline, GradePipeline, SchedulePipeline, AllSchedulePipeline, ExamPipeline

app = Flask(__name__)
app.config.from_object(config)


@app.route('/')
def index():
    return 'hello,world'


@app.route('/info', methods=['POST'])
def get_info():
    xh = request.form.get('xh')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((xh, pwd)):
        abort(404)

    pipiline = InfoPipeline(xh)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(xh, pwd)
        info = spider.get_info()
        pipiline.insert_data(info)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(xh, pwd)
            info = spider.get_info()
            pipiline.insert_data(info)
        else:
            info = pipiline.output_data()


    response = make_response(json.dumps(info, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


@app.route('/grade', methods=['POST'])
def get_grade():
    # method = 'getCjcx'
    xh = request.form.get('xh')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((xh, pwd)):
        abort(404)

    pipiline = GradePipeline(xh)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(xh, pwd)
        grade = spider.get_grade()
        pipiline.insert_data(grade)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(xh, pwd)
            grade = spider.get_grade()
            pipiline.insert_data(grade)
        else:
            grade = pipiline.output_data()


    response = make_response(json.dumps(grade, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


@app.route('/exam', methods=['POST'])
def get_exam():
    xh = request.form.get('xh')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((xh, pwd)):
        abort(404)

    pipiline = ExamPipeline(xh)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(xh, pwd)
        exam = spider.get_exam()
        pipiline.insert_data(exam)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(xh, pwd)
            exam = spider.get_exam()
            pipiline.insert_data(exam)
        else:
            exam = pipiline.output_data()

    response = make_response(json.dumps(exam, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response



@app.route('/nowschedule', methods=['POST'])
def get_nowschedule():
    # method = 'getKbcxAzc'
    xq = '2020-2021-1'
    xh = request.form.get('xh')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((xh, pwd)):
        abort(404)

    pipiline = SchedulePipeline(xh)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(xh, pwd)
        now_schedule = spider.get_now_schedule()
        pipiline.insert_data(now_schedule, xq)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(xh, pwd)
            now_schedule = spider.get_now_schedule()
            pipiline.insert_data(now_schedule, xq)
        elif flag == 1:
            now_schedule = pipiline.output_data()


    response = make_response(json.dumps(now_schedule, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


@app.route('/allschedule', methods=['POST'])
def get_allschedule():
    xh = request.form.get('xh')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((xh, pwd)):
        abort(404)

    pipiline = AllSchedulePipeline(xh)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(xh, pwd)
        allschedule = spider.get_all_schedule()
        pipiline.insert_data(allschedule)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(xh, pwd)
            allschedule = spider.get_all_schedule()
            pipiline.insert_data(allschedule)
        elif flag == 1:
            allschedule = pipiline.output_data()

    response = make_response(json.dumps(allschedule, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


@app.route('/schedule', methods=['POST'])
def get_one_schedule():
    xh = request.form.get('xh')
    pwd = request.form.get('pwd')
    xq = request.form.get('xq')
    if not all((xh, pwd)):
        abort(404)

    spider = PersonalSpider(xh, pwd)
    result = spider.get_schedule(xq)

    response = make_response(json.dumps(result, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


def get_uid(association_code):
	url = "http://zgf.jsky31.cn:10302/api/course/uid/{}".format(association_code)
	res = requests.get(url)
	uid = res.json()['data'][0]['uid']
	return uid

@app.route('/associated_schedule', methods=['POST'])
def get_associated_schedule():
    association_code = request.form.get('association_code')
    uid = get_uid(association_code) # uid == xh
    pipeline = SchedulePipeline(uid)
    now_schedule = pipeline.output_data()

    response = make_response(json.dumps(now_schedule, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response



@app.route('/ecard/balance', methods=['POST'])
def get_balance():
	xh = request.form.get('xh')
	pwd = request.form.get('pwd')
	if not all([xh, pwd]):
		results = dict(errcode='-4002', status='400', errmsg='Missing password',
			data=[])
	else:
		ecard = EcardSpider(xh, pwd)
		results = dict(errcode='0', status='200', errmsg='success',
			data=ecard.data)

	response = make_response(json.dumps(results, ensure_ascii=False))
	response.mimetype = 'text/json'

	return response


@app.route('/ecard/billing', methods=['POST'])
def get_bill():
	xh = request.form.get('xh')
	pwd = request.form.get('pwd')
	StartDate = request.form.get('StartDate')
	EndDate = request.form.get('EndDate')

	if not all([xh, pwd]):
		results = dict(errcode='-4002', status='400', errmsg='Missing password',
			data=[])
	elif not all([StartDate, EndDate]):
		if not StartDate:
			results = dict(errcode='-4413', status='400', errmsg='Missing start date',
				data=[])
		if not EndDate:
			results = dict(errcode='-4414', status='400', errmsg='Missing end date',
				data=[])
	else:
		ecard = EcardSpider(xh, pwd)
		results = dict(errcode='0', status='200', errmsg='success',
			data=ecard.query_bill(StartDate, EndDate))

	response = make_response(json.dumps(results, ensure_ascii=False))
	response.mimetype = 'text/json'

	return response


@app.route('/ecard/today_billing', methods=['POST'])
def get_today_bill():
    xh = request.form.get('xh')
    pwd = request.form.get('pwd')

    if not all([xh, pwd]):
        results = dict(errcode='-4002', status='400', errmsg='Missing password',
                        data=[])
    else:
        ecard = EcardSpider(xh, pwd)
        results = dict(errcode='0', status='200', errmsg='success',
			data=ecard.get_today_bill())

    response = make_response(json.dumps(results, ensure_ascii=False))
    response.mimetype = 'text/json'

    return response










if __name__ == "__main__":
    app.run()