# coding:utf-8
from pyquery import PyQuery as pq
import requests
import time
import smtplib
from email.mime.text import MIMEText
orderList = {}
SessionID = "" #  cookies中的ALIPAYJSESSIONID值
key = " XXXXXX" # API 密匙
api = "XXXXX" # API 地址
mailto_list=['xxx@qq.com','xxxx@qq.com']           #收件人(列表)
mail_host="smtp.qq.com"            #使用的邮箱的smtp服务器地址
mail_user="xxx"                           # 发邮件QQ 帐号
mail_pass="xxxx"                             # QQ密码
mail_postfix="qq.com"                     #邮箱的后缀
def send_mail(to_list,sub,content):
    me="alipay"+"<"+mail_user+"@"+mail_postfix+">"
    msg = MIMEText(content,_subtype='plain')
    msg['Subject'] = sub
    msg['From'] = me
    msg['To'] = ";".join(to_list)                #将收件人列表以‘；’分隔
    try:
        server = smtplib.SMTP()
        server.connect(mail_host)                            #连接服务器
        server.login(mail_user,mail_pass)               #登录操作
        server.sendmail(me, to_list, msg.as_string())
        server.close()
    except:
    	exit()
def postData(PaymentID, Time, Name, Amount):
    data = {'key': key,'ddh': PaymentID,'time': Time,'name': Name,'money': Amount}
    requests.post(api, data=data,timeout=5)
def check_order(SessionID):
	localtime = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
	print "staring get order", localtime
	r = requests.get("https://lab.alipay.com/consume/record/items.htm", cookies = {'ALIPAYJSESSIONID': SessionID},timeout=20)
	if r.url.startswith('https://auth.alipay.com/'):
		try:
			send_mail(mailto_list,"alipay dead","alipay dead")
			exit()
		except:
			exit()
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
			print e
			try:
				send_mail(mailto_list,"alipay dead","alipay dead")
				exit()
			except:
				exit()
if __name__ == "__main__":
	while True:
		print len(orderList)
		if len(orderList) > 1000:
			orderList = {}
		check_order(SessionID)
		time.sleep(5)