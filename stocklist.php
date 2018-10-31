<link rel="stylesheet" type="text/css" href="bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="material.min.css">
<link rel="stylesheet" type="text/css" href="tick.css">
<script src="jquery.min.js"></script>
<script src="socket.io.js"></script>

<?php  $arrNiftyStocks = ['9077','797','2302','9336','9339','832','3877','907','979','10512','1055','1067','1150','1212','3061','1247','1250','1260','1271','1290','1283','54453','1324','1372','11573','1375','1387','1407','4267','1504','4075','1550','3936','4199','1703','9009','1839','1900','2017','2071','2094','4106','8197','4104','3999','1909','2211','7169','2224']; ?>
<script type="text/javascript">
    $(document).ready(function(){
        obj_socket = io.connect('http://10.6.25.2:3000', {'multiplex': false, transports: ["websocket", "polling"]});
        obj_socket.on('connect', function () {
            details = {
                socket_id: obj_socket.id,
                action: 'nitfystocks',
				inputtoken: JSON.stringify(['9077','797','2302','9336','9339','832','3877','907','979','10512','1055','1067','1150','1212','3061','1247','1250','1260','1271','1290','1283','54453','1324','1372','11573','1375','1387','1407','4267','1504','4075','1550','3936','4199','1703','9009','1839','1900','2017','2071','2094','4106','8197','4104','3999','1909','2211','7169','2224'])
            }
            obj_socket.emit('requesttoken', details);
        });

        obj_socket.on('getNifty50Data', function (data) { //process data
	//var json = JSONC.decompress( data );
			var serverData = $.parseJSON(data);

            /*if(parseInt($('#bbprice_'+serverData.wtoken).html()) > parseInt(serverData.bestbuy_price)){
                $('#bbprice_'+serverData.wtoken).css('color',"red");
            }else{
                $('#bbprice_'+serverData.wtoken).css('color',"green");
            }
            //----------------------------------------------------------------------------------
            if(parseInt($('#bbqty_'+serverData.wtoken).html()) > parseInt(serverData.bestbuy_qty)){
                $('#bbqty_'+serverData.wtoken).css('color',"red");
            }else{
                $('#bbqty_'+serverData.wtoken).css('color',"green");
            }
             //----------------------------------------------------------------------------------
            if(parseInt($('#bsprice_'+serverData.wtoken).html()) > parseInt(serverData.bestsell_price)){
                $('#bsprice_'+serverData.wtoken).css('color',"red");
            }else{
                $('#bsprice_'+serverData.wtoken).css('color',"green");
            }
             //----------------------------------------------------------------------------------
            if(parseInt($('#bsqty_'+serverData.wtoken).html()) > parseInt(serverData.bestsell_qty)){
                $('#bsqty_'+serverData.wtoken).css('color',"red");
            }else{
                $('#bsqty_'+serverData.wtoken).css('color',"green");
            }*/
             //----------------------------------------------------------------------------------
            if(parseInt($('#ltp_'+serverData.wtoken).html()) > parseInt(serverData.last_traded_price)){
                $('#ltp_'+serverData.wtoken).css('color',"red");
            }else{
                $('#ltp_'+serverData.wtoken).css('color',"green");
            }
             //----------------------------------------------------------------------------------
           /*if(parseInt($('#tsq_'+serverData.wtoken).html()) > parseInt(serverData.total_sell_qty)){
                $('#tsq_'+serverData.wtoken).css('color',"red");
            }else{
                $('#tsq_'+serverData.wtoken).css('color',"green");
            }
             //----------------------------------------------------------------------------------
            if(parseInt($('#tbq_'+serverData.wtoken).html()) > parseInt(serverData.total_buy_qty)){
                $('#tbq_'+serverData.wtoken).css('color',"red");
            }else{
                $('#tbq_'+serverData.wtoken).css('color',"green");
            }
             //----------------------------------------------------------------------------------
            if(parseInt($('#lv_'+serverData.wtoken).html()) > parseInt(serverData.lv_last_trd_time)){
                $('#lv_'+serverData.wtoken).css('color',"red");
            }else{
                $('#lv_'+serverData.wtoken).css('color',"green");
            }*/
             //----------------------------------------------------------------------------------
            $('#bbprice_'+serverData.wtoken).html(serverData.bestbuy_price);
            $('#bbqty_'+serverData.wtoken).html(serverData.bestbuy_qty);
            $('#bsprice_'+serverData.wtoken).html(serverData.bestsell_price);
            $('#bsqty_'+serverData.wtoken).html(serverData.bestsell_qty);
            $('#ltp_'+serverData.wtoken).html(serverData.last_traded_price);
            $('#tsq_'+serverData.wtoken).html(serverData.total_sell_qty);
            $('#tbq_'+serverData.wtoken).html(serverData.total_buy_qty);
            $('#lv_'+serverData.wtoken).html(serverData.lv_last_trd_time);
        });
    });
</script>
	<?php
	try{
	if (extension_loaded('redis')) {
			$redis = new Redis(); 
			$connect_redis=false;
			if($redis->connect('10.6.25.33', 6379)){
				$redis->select(13); 
				$connect_redis=true;
		   }
	}
	}catch(Exception $e){
		$connect_redis=false;
	}
?>
<div class="container">


<div class="row">
    <?php
       
        $tdCounter = 1;
	foreach ($arrNiftyStocks as $key => $value) {
		$redis_data=[];
		if($connect_redis && $data_fetched=$redis->get($value)){
			$redis_data=json_decode($data_fetched,true);
		}
    ?>
    <div class="mdl-cell mdl-cell--3-col lst-cell--12-col cardM" id="KSEC-WelcomePage-RC1" style="">
        <div class="research-card mdl-card mdi-border"> 
            <div class="mdl-card__title mdl-list__item mdl-list__item--two-line">  
                <span class="mdl-card__title-text mdl-list__item-primary-content">
                    <span class="mdl-color-text--blue-300 listview-symbol"><?=  $redis_data['SecurityName'] ?? " Loading ..."; ?></span>
                    <span class="mdl-list__item-sub-title">Capital Goods&nbsp;</span>
                    <span class="KSEC-Card-RecDate listview-recdata">10/16/2018 9:02:27 AM</span> 
                </span>
            </div> 
             <div class="mdl-card__supporting-text">
                <div class="mdl-grid">
                    <div class="mdl-list__item mdl-cell--6-col  mdl-list__item--two-line lst-cell--1-col" style="padding: 0px !important;">
                        <span class="mdl-list__item-primary-content"> 
                            <span class="mdl-list__item-sub-title">Bestbuy Price</span>
                            <span id="bbprice_<?php echo $value; ?>"><?=  $redis_data['BD_bestbuy_price'] ?? "0000"; ?></span>             
                        </span> 
                    </div> 
                    <div class="mdl-list__item mdl-cell--6-col  mdl-list__item--two-line lst-cell--1-col" style="padding: 0px !important; ">
                        <span class="mdl-list__item-primary-content">
                            <span class="mdl-list__item-sub-title">Bestbuy Qty</span>
                            <span id="bbqty_<?php echo $value; ?>"><?=  $redis_data['BD_bestbuy_qty'] ?? " 0000"; ?></span>             
                        </span>         
                    </div> 

                    <div class="mdl-list__item mdl-cell--6-col  mdl-list__item--two-line lst-cell--1-col" style="padding: 0px !important; ">
                        <span class="mdl-list__item-primary-content">
                            <span class="mdl-list__item-sub-title">Bestsell Price</span>
                            <span id="bsprice_<?php echo $value; ?>"><?=  $redis_data['BD_bestsell_price'] ?? " 0000"; ?></span>             
                        </span>
                    </div>

                    <div class="mdl-list__item mdl-cell--6-col mdl-list__item--two-line lst-cell--1-col" style="padding: 0px !important; ">
                        <span class="mdl-list__item-primary-content">                   
                            <span class="mdl-list__item-sub-title">Bestsell Qty</span>
                            <span id="bsqty_<?php echo $value; ?>"><?=  $redis_data['BD_bestsell_qty'] ?? " 0000"; ?></span>             
                        </span>         
                    </div>   


                   <div class="mdl-list__item mdl-cell--6-col  mdl-list__item--two-line lst-cell--1-col" style="padding: 0px !important;">             
                        <span class="mdl-list__item-primary-content">                   
                            <span class="mdl-list__item-sub-title">Last Traded Price</span> 
                            <span class="capitalize" id="ltp_<?php echo $value; ?>"><?=  $redis_data['BD_last_traded_price'] ?? " 0000"; ?></span>                
                       </span>         
                   </div> 



                    <div class="mdl-list__item mdl-cell--6-col mdl-list__item--two-line lst-cell--1-col" style="padding: 0px !important; ">
                        <span class="mdl-list__item-primary-content">
                            <span class="mdl-list__item-sub-title">Total Sell Qty</span>
                            <span id="tsq_<?php echo $value; ?>"><?=  $redis_data['BD_total_sell_qty'] ?? " 0000"; ?></span>     
                        </span>
                    </div>   


                   <div class="mdl-list__item mdl-cell--6-col mdl-list__item--two-line lst-cell--1D-col" style="padding: 0px !important; ">
                        <span class="mdl-list__item-primary-content">
                            <span class="mdl-list__item-sub-title">Total Buy Qty</span>
                            <span id="tbq_<?php echo $value; ?>"><?=  $redis_data['BD_total_buy_qty'] ?? " 0000"; ?></span>
                            <!--<p id="lv_<?php //echo $value; ?>"></p> -->
                       </span>
                   </div> 



                    <div class="col s12 m12" style="padding: 0px !important; position:relative; ">
                        <div class="carddetailbtn" style="">
                            <i class="lnr lnr-chevron-down-circle" style=""></i>
                        </div>
                        <span class="mdl-list__item-primary-content carddetail"></span>
                    </div>   
                </div> 
             </div>
             <div class="mdl-card__menuM">
                <button class="mdl-button rcaction listview-btn" id="watch" data-rcaction="WATCH" style="font-weight:400; font-size:18px;" title="Add to Watch"><i class="lnr lnr-eye"></i></button>
            
             </div>
         </div>
     </div>
<?php } ?>


</div>













    

</div>

