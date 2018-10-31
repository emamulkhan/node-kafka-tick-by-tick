/*
 
This program consumes Kafka messages from topic Top3CountrySizePerContinent to which the Running Top3 (size of countries by continent) is produced.
 
This program reports: top 3 largest countries per continent (periodically, with a configurable interval) 
*/
var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var kafka = require('kafka-node');
var Consumer = kafka.Consumer
var client = new kafka.Client("localhost:2181/")
 
//var countriesTopic = "Top3CountrySizePerContinent";
var niftyTopic = "stockticks";
var reportingIntervalInSecs = 4;
 const myQueue = [];
var consumer = new Consumer(
  client,
  [],
  {fromOffset: true}
);

let interval;
io.on("connection", socket=> {
	socket.on('requesttoken', function(data) {
		input=JSON.parse(data.inputtoken);
			 getApiAndEmit(socket,input);
		

	//io.emit('getNifty50Data', JSON.stringify(responseData));
});

 socket.on('disconnect', function() {
        console.log("disconnect: ", socket.id);
    });
});


 const getApiAndEmit = async (socket,data) => {
  try {
	  //console.log(data);
			consumer.on('message', function (message) {
			var responseData = JSON.parse(message.value);
			if(input.indexOf(responseData['wtoken']) > -1 ){
				socket.emit('getNifty50Data', JSON.stringify(responseData));
			}
			//if(data.indexOf(responseData['wtoken']) > -1 ){
			//var compressedJSON = JSONC.compress( responseData );
			//	socket.emit('getNifty50Data', responseData);
			//}
		//
			
		});

    //socket.emit("getNifty50Data", JSON.stringify(message)); // Emitting a new message. It will be consumed by the client
  } catch (error) {
    console.error(`Error: ${error.code}`);
  }
};

/*
async function callEmit(socket,input){
	const message = myQueue.shift();
	 io.emit('getNifty50Data', JSON.stringify(message));
}



//added by jitu 
/*async function consumeQueue(socket,input){
	//const message = myQueue.shift();
	input.foreach(async (item) => {
		await callEmit(socket,item);
	});
    /*if(!message){
        await sleep(300);
    } else {
        // ... decode your message
        io.emit('getNifty50Data', JSON.stringify(message));
    }

    //consumeQueue();
}
*/
consumer.addTopics([
			  { topic: niftyTopic, partition: 0, offset: 0}
			], () => console.log("topic "+niftyTopic+" added to consumer for listening"));
//consumeQueue();
function sleep(ms){
    return new Promise(resolve => setTimeout(resolve, ms));
}
//code added by jitu ends here
/*
io.on('connection', function (socket) {
	
	socket.on('requesttoken', function(data) {
		input=JSON.parse(data.inputtoken);
		consumeQueue(socket,input);
	/*	consumer.on('message', function (message) {

			var responseData = JSON.parse(message.value);
			if(input.indexOf(responseData['wtoken']) > -1 ){
				socket.emit('getNifty50Data', JSON.stringify(responseData));
			}

		});
    });
	socket.on('disconnect', function() {
        console.log("disconnect: ", socket.id);
    });
});*/
http.listen(3000, function(){
    console.log('listening on *:3000');
});
