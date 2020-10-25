<?php

include 'include.php';
include 'database.php';
session_start();
$invoice_id = rand(1000, 9999);
// $_SESSION['invoice_id'] = $invoice_id;
$price_in_usd = (isset($_GET['amount']) ? $_GET['amount'] : '');
$memid = (isset($_GETT['memid']) ? $_GET['memid'] : '');
$product_url = date("M,d,Y h:i:s A");
// $price_in_btc = file_get_contents($blockchain_root . "tobtc?currency=USD&value=" . $price_in_usd);
$url = $blockchain_root . "tobtc?currency=USD&value=" . $price_in_usd;
$ch  = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$resp = curl_exec($ch);
$price_in_btc = json_decode($resp);
// print json_decode(array('price' => $price_in_btc ));
// json_encode(array('price_btc' => $price_in_btc ));
// $_SESSION['btc'] = $price_in_btc; 
// $db = new mysqli($mysql_host, $mysql_username, $mysql_password) or die(__LINE__ . ' Invalid connect: ' . mysqli_error());

// $db->select_db($mysql_database) or die( "Unable to select database. Run setup first.");

//Add the invoice to the database
// $stmt = $db->prepare("replace INTO invoices (invoice_id, price_in_usd, price_in_btc, product_url) values(?,?,?,?)");
// $stmt->bind_param("idds",$invoice_id, $price_in_usd, $price_in_btc, $product_url);
// $result = $stmt->execute();
$query1 = "INSERT INTO [biconatrade].[dbo].[invoices] (invoice_id, price_in_usd, price_in_btc, date_time, mem_id) VALUES ('$invoice_id', '$price_in_usd', '$price_in_btc', '$product_url','$memid')";
// print($query1); exit;
$result1 = sqlsrv_query($conn, $query1);



// if (!$result1) {
//     die(__LINE__ . ' Invalid query: ' . mysqli_error($db));1a
// }
// include 'create.php';
?>


<html>
<head>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
    <!-- <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous"> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- <script type="text/javascript" src="bitcoin.js"></script> -->
    <script type="text/javascript">
    $(document).ready(function() {
    // var root = "https://blockchain.info/";    
    var amount = '<?php echo $price_in_btc; ?>';
    var root = "https://chart.googleapis.com/chart?chs=225x225&chld=L|2&cht=";
    var buttons = $('.blockchain-btn');
    buttons.find('.blockchain').hide();
    buttons.find('.stage-begin');

    buttons.each(function(index) {
        var _button = $(this);

        (function() {
            var button = _button;

            button.click(function() {
                var create_url = $(this).data('create-url');

                button.find('.blockchain').hide();

                button.find('.stage-loading').trigger('show').show();

                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: create_url,
                    success: function(response) {
                        button.find('.qr-code').empty();

                        button.find('.blockchain').hide();

                        if (!response || !response.input_address) {
                            button.find('.stage-error').trigger('show').show().html(button.find('.stage-error').html().replace('[[error]]', 'Unknown Error'));
                            return;
                        }
                        function checkBalance() {
                            $.ajax({
                                type: "GET",
                                url: root + 'q/getreceivedbyaddress/'+response.input_address,
                                data : {format : 'plain'},
                                success: function(response) {
                                    if (!response) return;
                                    var value = parseInt(response);
                                    if (value > 0) {
                                        button.find('.blockchain').hide();
                                        button.find('.stage-paid').trigger('show').show().html(button.find('.stage-paid').html().replace('[[value]]', value / 100000000));
                                    } else {
                                        setTimeout(checkBalance, 5000);
                                    }
                                }
                            });
                        }

                        try {
                            ws = new WebSocket('wss://ws.blockchain.info/inv');

                            if (!ws) return;

                            ws.onmessage = function(e) {
                                try {
                                    var obj = $.parseJSON(e.data);

                                    if (obj.op == 'utx') {
                                        var tx = obj.x;

                                        var result = 0;
                                        for (var i = 0; i < tx.out.length; i++) {
                                            var output = tx.out[i];

                                            if (output.addr == response.input_address) {
                                                result += parseInt(output.value);
                                            }
                                        }
                                    }

                                    button.find('.blockchain').hide();
                                    button.find('.stage-paid').trigger('show').show().html(button.find('.stage-paid').html().replace('[[value]]', result / 100000000));

                                    ws.close();
                                } catch(e) {
                                    console.log(e);

                                    console.log(e.data);
                                }
                            };

                            ws.onopen = function() {
                                ws.send('{"op":"addr_sub", "addr":"'+ response.input_address +'"}');
                            };
                        } catch (e) {
                            console.log(e);
                        }

                        button.find('.stage-ready').trigger('show').show().html(button.find('.stage-ready').html().replace('[[address]]', response.input_address));

                        // button.find('.qr-code').html('<img style="margin:5px" src="'+root+'qr?data='+response.input_address+'&amount='+response.price+'&size=125">');
                        button.find('.qr-code').html('<img style="margin:5px" src="'+root+'qr&chl=bitcoin:'+response.input_address+'?amount='+amount+'%26label=Moloch.net%26message=Donation">');
                        button.unbind();

                        ///Check for incoming payment
                        setTimeout(checkBalance, 5000);
                    },
                    error : function(e) {
                        button.find('.blockchain').hide();

                        button.find('.stage-error').show().trigger('show').html(button.find('.stage-error').html().replace('[[error]]', e.responseText));
                    }
                });
            });
        })();
    });
});
    
    </script>
    
    <script type="text/javascript">
	$(document).ready(function() {
		$('.stage-paid').on('show', function() {
			window.location.href = './receive.php?invoice_id=<?php echo $invoice_id; ?>';
		});
	});
	</script>
<style>
.tooltip {
  position: relative;
  display: inline-block;
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 140px;
  background-color: #555;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px;
  position: absolute;
  z-index: 1;
  bottom: 150%;
  left: 50%;
  margin-left: -75px;
  opacity: 0;
  transition: opacity 0.3s;
}

.tooltip .tooltiptext::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: #555 transparent transparent transparent;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
}
.blockchain.stage-ready {
    margin-top: 38px;
}

</style>
</head>
    <body>
       <h1></h1>
    
        <div class="blockchain-btn" style="width:auto" data-create-url="create.php?invoice_id=<?php echo $invoice_id; ?>"> 
            <div class="blockchain stage-begin" id="stage-begin">
                <!-- <img src="pay_now_64.png"> -->
                <button class="md-trigger" id="modal" data-modal="modal"></button>
            </div>
            <div class="blockchain stage-loading" style="text-align:center">
                <img src="<?php echo $blockchain_root; ?>Resources/loading-large.gif">
            </div>
            <div class="blockchain stage-ready" style="text-align:center">
            <b>Bitcoin Address</b>
            <div class='qr-code'></div>
            <div style="clear:both;">
            <h2><b><?php echo $price_in_btc; ?></b> BTC</h2>
            <!-- <h4>BTC<h4> -->
            </div>
                Send <?php echo $price_in_btc; ?> BTC (in ONE payment) to :<br /><br />
                <input type="text" style="text-align: center;color: blue;border:1px solid blue;" value="[[address]]" size="38" id="myInput">
                <div class="tooltip">
                <button onclick="myFunction()" onmouseout="outFunc()">
                <span class="tooltiptext" id="myTooltip">Copy to clipboard</span>
                <i style="font-size:15px;color:blue;" class="fa">&#xf0c5;</i>
                </button>
                </div> 
                <br /><br />
<!-- Timer -->
<div id="countdown2"></div><br />
<script>
function countdown( elementName, minutes, seconds )
{
    var element, endTime, hours, mins, msLeft, time;

    function twoDigits( n )
    {
        return (n <= 9 ? "0" + n : n);
    }

    function updateTimer()
    {
        msLeft = endTime - (+new Date);
        if ( msLeft < 1000 ) {
            element.innerHTML = "countdown's over!";
            window.location.href = 'index.php';
        } else {
            time = new Date( msLeft );
            hours = time.getUTCHours();
            mins = time.getUTCMinutes();
            element.innerHTML = (hours ? hours + ':' + twoDigits( mins ) : mins) + ':' + twoDigits( time.getUTCSeconds() );
            setTimeout( updateTimer, time.getUTCMilliseconds() + 500 );
        }
    }

    element = document.getElementById( elementName );
    endTime = (+new Date) + 1000 * (60*minutes + seconds) + 500;
    updateTimer();
}

countdown( "countdown2", 60, 0 );
</script>
 don't include transaction fee in this amount <br />
<!-- End of Timer -->
                if you send any other bitcoin amount payment system will ignore it!<br />
                for any deposit related query, please contact: <a href="support@biconatrade.com" target="_blank">support@biconatrade.com</a>
                 <!-- <b>[[address]]</b> <br /> -->
            </div>
            <div class="blockchain stage-paid">
                Payment Received <b>[[value]] BTC</b>. Thank You.
            </div>
            <div class="blockchain stage-error">
                <font color="red">[[error]]</font>
            </div>
        </div>
        <script>
        function myFunction() {
        var copyText = document.getElementById("myInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        var tooltip = document.getElementById("myTooltip");
        tooltip.innerHTML = "Copied: " + copyText.value;
        }

        function outFunc() {
        var tooltip = document.getElementById("myTooltip");
        tooltip.innerHTML = "Copy to clipboard";
        }
        </script>
    </body>
    <script type="text/javascript">
$(document).ready(function(){
    $("#modal").trigger('click'); 
});
</script>

</html>