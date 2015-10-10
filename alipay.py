# coding:utf-8
from pyquery import PyQuery as pq
import requests
# import time
orderList = {}
key = ""
api = ""
def postData(PaymentID, Time, Name, Amount):
    data = {'key': key,'ddh': PaymentID,'time': Time,'name': Name,'money': Amount}
    requests.post(api, data=data)
def check_order(SessionID):
	# localtime = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
	# print "staring get order", localtime
	r = requests.get("https://lab.alipay.com/consume/record/items.htm", cookies = {'ALIPAYJSESSIONID': SessionID})
	if r.url.startswith('https://auth.alipay.com/'):
            print "error , sent email"
	orderTable = pq(r.text)("tbody tr")
	for order in orderTable:
		order_data = {}
		order_pq = pq(order)
		try:
			order_data['money']  = order_pq(".amount.income").text() # 交易金额
			if order_data['money'] == "":
				continue
			order_data['time'] = order_pq(".time").text() # 交易时间
			order_data['name'] = order_pq(".name.emoji-li").text()[:-7].encode("utf-8")  # 描述
			order_data['ddh'] = order_pq(".number").text() # 交易号
			if order_data['ddh'] in orderList:
				print "all order already exist"
				break
			else:
					postData(order_data['ddh'],order_data['time'],order_data['name'],order_data['money'])
					orderList[order_data['ddh']] = order_data
		except Exception as e:
			print "error , sent email"

if __name__ == "__main__":
	SessionID =  raw_input("input session id:")
	while True:
		print len(orderList)
		if len(orderList) > 1000:
			orderList = {}
		check_order(SessionID)
		time.sleep(5)
