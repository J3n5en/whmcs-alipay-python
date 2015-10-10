<?php
//$prex         = "whmcs";        //订单描述前置标记
//$security_code   = "";        //安全检验码
//$seller_email    = "";        //卖家支付宝帐户
function alipaypersonal_config() {
    $configarray = array(
        "FriendlyName"  => array(
            "Type"  => "System",
            "Value" => "Alipay 支付宝收款接口"
        ),
        "seller_email"  => array(
            "FriendlyName" => "卖家支付宝帐户",
            "Type"         => "text",
            "Size"         => "32",
        ),
        "security_code" => array(
            "FriendlyName" => "安全检验码",
            "Type"         => "text",
            "Size"         => "32",
        ),
    );

    return $configarray;
}

function alipaypersonal_form($params) {

    # Invoice Variables
    $systemurl = $params['systemurl'];
    $invoiceid = $params['invoiceid'];
    $amount    = $params['amount']; # Format: ##.##
    $seller_email = $params['seller_email'];
    $name      = 'whmcs_' . $invoiceid;
    $memo      = "请勿修改付款说明里内容,否则无法完成订购";
    $form_html = '<form accept-charset="GBK" id="alipaysubmit" name="alipaysubmit" action="https://shenghuo.alipay.com/send/payment/fill.htm" method="POST">
		<input type="hidden" name="optEmail" value="' . $seller_email. '"/>
		<input type="hidden" name="payAmount" value="' . $amount . '"/>
		<input type="hidden" name="title" value="' . $name . '"/>
		<input type="hidden" name="memo" value="' . $memo . '"/>
		<input type="hidden" value="submit" value="submit">
	</form>';
    $img       = $systemurl . "/modules/gateways/callback/pay-with-alipay.png"; //这个图片要先存放好.
    $code      = $form_html . '<a href="#" onclick="alipaypersonal_submit();"><img style="width: 152px;" src="' . $img . '" alt="点击使用支付宝支付"></a>';
    $script     = '<script language="javascript">
                    function alipaypersonal_submit(){
                        document.charset="GBK";
                        document.getElementById(\'alipaysubmit\').submit();
                    }
                 </script>';

     $code .= $script;
    return $code;
}

function alipaypersonal_link($params) {
    return alipaypersonal_form($params);
}

?>