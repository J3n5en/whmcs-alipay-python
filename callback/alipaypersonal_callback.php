<?php

# Required File Includes
include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");
/*
function verify_data($order_data) {
    //sig_format = '|'.join([order_data["tradeNo"], order_data["desc"].decode("utf-8"), order_data["time"], order_data["username"], order_data["userid"], str(order_data["amount"]), order_data["status"], PUSH_STATE_KEY]).encode("utf-8")
    $string = $order_data["tradeNo"] . '|' . $order_data["desc"] . '|' . $order_data["time"] . '|' . $order_data["username"] . '|' . $order_data["userid"] . '|' . (string)$order_data["amount"] . '|' . $order_data['security_code'];
    $sig    = strtoupper(md5($string));
    log_result(json_encode($order_data));
    log_result($sig . '==' . $order_data['sig']);
    if ($order_data["tradeNo"] && $order_data['sig'] == $sig) {
        return true;
    }
}
*/
function verify_post($order_data, $key) {
    log_result(json_encode($order_data));
    log_result($key);
    if ($order_data['money'] > 0 && !empty($order_data['ddh']) && !empty($order_data['key']) && $order_data['key'] == $key) {
        if (strpos($order_data['name'], 'whmcs_') === 0) {
            $invoiceid = (int)substr($order_data['name'], strlen('whmcs_'));
            if ($invoiceid > 0) {
                $order_data['invoice_id'] = $invoiceid;
                $order_data['status']     = 'success';
                log_result($order_data);

                return $order_data;
            }

        }
    }

    return false;
}

function log_result($word) {
    $file = '/tmp/alipay_log.txt';
    if (!is_string($word)) {
        $word = json_encode($word);
    }
    $string = strftime("%Y%m%d%H%I%S", time()) . "\t" . $word . "\n";
    file_put_contents($file, $string, FILE_APPEND);
}

$gatewaymodule = "alipaypersonal"; # Enter your gateway module name here replacing template
$GATEWAY       = getGatewayVariables($gatewaymodule);
if (!$GATEWAY["type"])
    die("Module Not Activated"); # Checks gateway module is active before accepting callback

$order_data                  = $_POST;
$gatewaySELLER_EMAIL         = $GATEWAY['seller_email'];
$gatewaySECURITY_CODE        = $GATEWAY['security_code'];
$order_data = verify_post($order_data, $gatewaySECURITY_CODE);
if (!$order_data) {
    logTransaction($GATEWAY["name"], $_POST, "Unsuccessful");
    echo 'faild';
    exit;
}

# Get Returned Variables
$status    = $order_data['status'];    //获取支付宝传递过来的交易状态
$invoiceid = $order_data['invoice_id']; //订单号
$transid   = $order_data['ddh']; //转账交易号
$amount    = $order_data['money'];       //获取支付宝传递过来的总价格
$fee       = 0;
if ($status == 'success') {
    $invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY["name"]); # Checks invoice ID is a valid invoice number or ends processing
    //checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does
    $table  = "tblaccounts";
    $fields = "transid";
    $where  = array("transid" => $transid);
    $result = select_query($table, $fields, $where);
    $data   = mysql_fetch_array($result);
    if (!$data) {
        addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule);
        logTransaction($GATEWAY["name"], $_POST, "Successful");
    }
    echo "success";
} else {
    echo 'faild';
}

?>