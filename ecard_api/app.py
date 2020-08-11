from flask import Flask, request, make_response
from ecard import Ecard
import json


app = Flask(__name__)

@app.route('/')
def index():
	return 'hello, world'

@app.route('/ecard/balance', methods=['POST'])
def get_balance():
	xh = request.form.get('xh')
	pwd = request.form.get('pwd')
	if not all([xh, pwd]):
		result = dict(errcode='-4002', status='400', errmsg='Missing password',
			data=[])
	else:
		ecard = Ecard(xh, pwd)
		result = dict(errcode='0', status='200', errmsg='success',
			data=ecard.data)

	response = make_response(json.dumps(result, ensure_ascii=False))
	response.mimetype = 'text/json'

	return response


@app.route('/ecard/billing', methods=['POST'])
def get_bill():
	xh = request.form.get('xh')
	pwd = request.form.get('pwd')
	StartDate = request.form.get('StartDate')
	EndDate = request.form.get('EndDate')

	if not all([xh, pwd]):
		result = dict(errcode='-4002', status='400', errmsg='Missing password',
			data=[])
	elif not all([StartDate, EndDate]):
		if not StartDate:
			result = dict(errcode='-4413', status='400', errmsg='Missing start date',
				data=[])
		if not EndDate:
			result = dict(errcode='-4414', status='400', errmsg='Missing end date',
				data=[])
	else:
		ecard = Ecard(xh, pwd)
		result = dict(errcode='0', status='200', errmsg='success',
			data=ecard.query_bill(StartDate, EndDate))

	response = make_response(json.dumps(result, ensure_ascii=False))
	response.mimetype = 'text/json'

	return response


if __name__ == '__main__':
	app.run(debug=True, threaded=True)