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
    sid = request.form.get('sid')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((sid, pwd)):
        abort(404)

    pipiline = InfoPipeline(sid)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(sid, pwd)
        info = spider.get_info()
        pipiline.insert_data(info)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(sid, pwd)
            info = spider.get_info()
            pipiline.insert_data(info)
        else:
            info = pipiline.output_data()


    result = {
        'errcode': '0',
        'status': '200',
        'errmsg': 'success',
        'data': [
            info
        ]
    }


    response = make_response(json.dumps(result, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


@app.route('/grade', methods=['POST'])
def get_grade():
    # method = 'getCjcx'
    sid = request.form.get('sid')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((sid, pwd)):
        abort(404)

    pipiline = GradePipeline(sid)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(sid, pwd)
        grade = spider.get_grade()
        pipiline.insert_data(grade)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(sid, pwd)
            grade = spider.get_grade()
            pipiline.insert_data(grade)
        else:
            grade = pipiline.output_data()

    result = {
        'errcode': '0',
        'status': '200',
        'errmsg': 'success',
        'data': grade
    }


    response = make_response(json.dumps(result, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


@app.route('/exam', methods=['POST'])
def get_exam():
    sid = request.form.get('sid')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((sid, pwd)):
        abort(404)

    pipiline = ExamPipeline(sid)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(sid, pwd)
        exam = spider.get_exam()
        pipiline.insert_data(exam)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(sid, pwd)
            exam = spider.get_exam()
            pipiline.insert_data(exam)
        else:
            exam = pipiline.output_data()

    result = {
        'errcode': '0',
        'status': '200',
        'errmsg': 'success',
        'data': exam
    }


    response = make_response(json.dumps(result, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response



@app.route('/now_schedule', methods=['POST'])
def get_now_schedule():
    # method = 'getKbcxAzc'
    xq = '2020-2021-1'
    sid = request.form.get('sid')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((sid, pwd)):
        abort(404)

    pipiline = SchedulePipeline(sid)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(sid, pwd)
        now_schedule = spider.get_now_schedule()
        pipiline.insert_data(now_schedule, xq)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(sid, pwd)
            now_schedule = spider.get_now_schedule()
            pipiline.insert_data(now_schedule, xq)
        elif flag == 1:
            now_schedule = pipiline.output_data()

    result = {
        'errcode': '0',
        'status': '200',
        'errmsg': 'success',
        'data': now_schedule
    }


    response = make_response(json.dumps(result, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


@app.route('/all_schedule', methods=['POST'])
def get_all_schedule():
    sid = request.form.get('sid')
    pwd = request.form.get('pwd')
    refresh = request.form.get('refresh')
    if refresh == None:
        refresh = False

    if not all((sid, pwd)):
        abort(404)

    pipiline = AllSchedulePipeline(sid)
    if refresh:
        pipiline.delete_data()
        spider = PersonalSpider(sid, pwd)
        all_schedule = spider.get_all_schedule()
        pipiline.insert_data(all_schedule)
    else:
        flag = pipiline.get_flag()
        if flag == 0:
            spider = PersonalSpider(sid, pwd)
            all_schedule = spider.get_all_schedule()
            pipiline.insert_data(all_schedule)
        elif flag == 1:
            all_schedule = pipiline.output_data()

    result = {
        'errcode': '0',
        'status': '200',
        'errmsg': 'success',
        'data': all_schedule
    }


    response = make_response(json.dumps(result, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


@app.route('/schedule', methods=['POST'])
def get_one_schedule():
    sid = request.form.get('sid')
    pwd = request.form.get('pwd')
    term = request.form.get('term')
    if not all((sid, pwd)):
        abort(404)

    spider = PersonalSpider(sid, pwd)
    schedule = spider.get_schedule(term)

    result = {
        'errcode': '0',
        'status': '200',
        'errmsg': 'success',
        'data': schedule
    }


    response = make_response(json.dumps(result, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response


def get_sid(association_code):
	url = "http://zgf.jsky31.cn:10302/api/course/uid/{}".format(association_code)
	res = requests.get(url)
	sid = res.json()['data'][0]['uid']
	return sid

@app.route('/associated_schedule', methods=['POST'])
def get_associated_schedule():
    association_code = request.form.get('association_code')
    sid = get_sid(association_code)
    schedulepipeline = SchedulePipeline(sid)
    infopipeline = InfoPipeline(sid)
    name = infopipeline.output_data()['name']
    now_schedule = schedulepipeline.output_data()

    result = {
        'errcode': '0',
        'status': '200',
        'errmsg': 'success',
        'data': {
            'name': name,
            'schedule': now_schedule
        }
    }


    response = make_response(json.dumps(result, ensure_ascii=False))
    response.mimetype = 'text/json'
    return response



@app.route('/ecard/balance', methods=['POST'])
def get_balance():
	sid = request.form.get('sid')
	pwd = request.form.get('pwd')
	if not all([sid, pwd]):
		results = dict(errcode='-4002', status='400', errmsg='Missing password',
			data=[])
	else:
		ecard = EcardSpider(sid, pwd)
		results = dict(errcode='0', status='200', errmsg='success',
			data=ecard.data)

	response = make_response(json.dumps(results, ensure_ascii=False))
	response.mimetype = 'text/json'

	return response


@app.route('/ecard/billing', methods=['POST'])
def get_bill():
	sid = request.form.get('sid')
	pwd = request.form.get('pwd')
	StartDate = request.form.get('StartDate')
	EndDate = request.form.get('EndDate')

	if not all([sid, pwd]):
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
		ecard = EcardSpider(sid, pwd)
		results = dict(errcode='0', status='200', errmsg='success',
			data=ecard.query_bill(StartDate, EndDate))

	response = make_response(json.dumps(results, ensure_ascii=False))
	response.mimetype = 'text/json'

	return response


@app.route('/ecard/today_billing', methods=['POST'])
def get_today_bill():
    sid = request.form.get('sid')
    pwd = request.form.get('pwd')

    if not all([sid, pwd]):
        results = dict(errcode='-4002', status='400', errmsg='Missing password',
                        data=[])
    else:
        ecard = EcardSpider(sid, pwd)
        results = dict(errcode='0', status='200', errmsg='success',
			data=ecard.get_today_bill())

    response = make_response(json.dumps(results, ensure_ascii=False))
    response.mimetype = 'text/json'

    return response










if __name__ == "__main__":
    app.run()