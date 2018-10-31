/*
This program reads and parses all lines from csv files countries2.csv into an array (countriesArray) of arrays; each nested array represents a country.
The initial file read is synchronous. The country records are kept in memory.
After the the initial read is performed, a function is invoked to publish a message to Kafka for the first country in the array. This function then uses a time out with a random delay 
to schedule itself to process the next country record in the same way. Depending on how the delays pan out, this program will publish country messages to Kafka every 3 seconds for about 10 minutes.
*/
var fs = require('fs');
var redis = require("redis");
// Kafka configuration
var kafka = require('kafka-node')
var Producer = kafka.Producer
// instantiate client with as connectstring host:port for  the ZooKeeper for the Kafka cluster
var client = new kafka.Client("localhost:2181/")

// name of the topic to produce to
var niftyTopic = "stockticks";

//Multicast Client receiving sent messages
var PORT = 2424;
var MCAST_ADDR = "237.1.2.82"; //same mcast address as Server
//var HOST = '237.1.2.82'; //this is your own IP
var HOST = '224.0.0.1'; 
var dgram = require('dgram');
var UDPClient = dgram.createSocket('udp4');
//var niftyStockCodeArr = [15073,1466,2820,15259,15261,1490,8900,1549,1614,16442,1681,1692,1753,1804,6307,1833,1836,1843,1851,1867,1862,17503,1902,1946,17393,1949,158,1971,9768,2046,9495,2078,9268,9686,2206,15028,2314,2361,2441,2482,2498,9563,14481,9562,9351,2367,2584,13671,2595];
//var niftyStockCodeArr = ['9077','797','2302','9336','9339','832','3877','907','979','10512','1055','1067','1150','1212','3061','1247','1250','1260','1271','1290','1283','54453','1324','1372','11573','1375','1387','1407','4267','1504','4075','1550','3936','4199','1703','9009','1839','1900','2017','2071','2094','4106','8197','4104','3999','1909','2211','7169','2224'];


KeyedMessage = kafka.KeyedMessage,
producer = new Producer(client),
km = new KeyedMessage('key', 'message'),
tickProducerReady = false ;

producer.on('ready', function () {
    console.log("Producer for tick by tick is ready");
    tickProducerReady = true;
});

producer.on('error', function (err) {
    console.error("Problem with producing Kafka message "+err);
})
//producer code ends here
//var redis = require("redis");
clientRedis = redis.createClient('6379','10.6.25.33');
clientRedis.on('connect', function() {
    console.log('Redis client connected');
});
clientRedis.on("error", function (err) {
    console.log("Error " + err);
});

UDPClient.on('listening', function () {
    var address = UDPClient.address();
    console.log('UDP Client listening on ' + address.address + ":" + address.port);
    UDPClient.setBroadcast(true)
    UDPClient.setMulticastTTL(128); 
    UDPClient.addMembership(MCAST_ADDR);
});


UDPClient.on('message', function (message, remote) {
	//var arrPacketData = message.toString().split(/(?:\r\n|\r|\n)/g);
	var arrData = message.toString().split('$');
	//console.log(arrData);
	var finalDataArray = new Array();
	for(var i=0; i<arrData.length; i++) {
		if(arrData[i].startsWith('^')) {
			if (arrData[i].charAt(1) == arrData[i].charAt(arrData[i].length-1)) {
				finalDataArray = arrData[i].toString().split(',');
				if (finalDataArray[0].endsWith('101') ) {
					//console.log('TOKEN >> '+ finalDataArray[1] + ' ----- LTP >> '+ finalDataArray[6]);
					handleTicks(finalDataArray[1], finalDataArray[2], finalDataArray[3], finalDataArray[4], 
						finalDataArray[5], finalDataArray[6], finalDataArray[13], finalDataArray[14], finalDataArray[19])
				}
				
			}
		}
	}
    //console.log('MCast Msg: From: ' + remote.address + ':' + remote.port +' - ' + message);
});


UDPClient.bind(PORT, HOST);
//listening data from UDP ends here

// handle the current coountry record
function handleTicks(wtoken, bestbuy_price, bestbuy_qty, bestsell_price, bestsell_qty, last_traded_price, total_sell_qty, total_buy_qty, lv_last_trd_time) { 
	
	var tickData = {"wtoken" : wtoken, "bestbuy_price" : (bestbuy_price/100).toFixed(2), "bestbuy_qty" : bestbuy_qty, "bestsell_price" : (bestsell_price/100).toFixed(2),
	"bestsell_qty" : bestsell_qty, "last_traded_price" : (last_traded_price/100).toFixed(2),"total_sell_qty": total_sell_qty, "total_buy_qty": total_buy_qty, 
	"lv_last_trd_time": lv_last_trd_time};
	insertDataRedis(tickData);
	produceTickMessage(tickData);
	
         
}

function insertDataRedis(tick_data){
	clientRedis.select(13, function(err,res){
		clientRedis.get(tick_data['wtoken'], function (error, result) {
	   	 	if (error) {
		 		console.log(error);
				throw error;
			}
			
			if(result != null){
			var dataRedis=JSON.parse(result);
			
			dataRedis['BD_bestbuy_price']=tick_data['bestbuy_price'];
			dataRedis['BD_bestbuy_qty']=tick_data['bestbuy_qty'];
			dataRedis['BD_bestsell_price']=tick_data['bestsell_price'];
			dataRedis['BD_bestsell_qty']=tick_data['bestsell_qty'];
			dataRedis['BD_last_traded_price']=tick_data['last_traded_price'];
			dataRedis['BD_total_sell_qty']=tick_data['total_sell_qty'];
			dataRedis['BD_total_buy_qty']=tick_data['total_buy_qty'];
			
			 clientRedis.set(tick_data['wtoken'], JSON.stringify(dataRedis));
			}
			 
			
			
		});
	});
}

function produceTickMessage(tickDataJson,dataRedis,tick_data) {
	KeyedMessage = kafka.KeyedMessage,
	tickDataKM = new KeyedMessage(tickDataJson.wtoken, JSON.stringify(tickDataJson)),
	payloads = [
		{ topic: niftyTopic, messages: tickDataKM, partition: 0 },
	];
	if (tickProducerReady) {
		producer.send(payloads, function (err, data) {
			console.log("Data After Send : ");console.log(data);

		});
	} else {
		// the exception handling can be improved, for example schedule this message to be tried again later on
		console.error("sorry, tickProducer is not ready yet, failed to produce message to Kafka.");
	}

}//produceTickMessage
