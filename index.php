<!DOCTYPE html>
<html>
<head>
<title>Leap Gesture Recorder</title>
<style>
body {
  font-family: Helvetica, Arial, sans-serif;
  font-size: 15px;
  color: #eee;
  background: #222;
}
p {
  width: 500px;
}
.good {
  color: #0e2;
  font-weight: bold;
}
.bad {
  color: #f30;
  font-weight: bold;
}
pre {
  height: 400px;
  font-size: 10px;
}
</style>

<script src="jquery.min.js"></script>

</head>
<body>
<p>A utility for recording a few seconds of data from a Leap Motion sensor.</p>
<p><strong>Press the record button to begin recording a gesture.</strong><br/>Click it again to output the collected data as JSON text on the page.</p>
<p>You can use the data to visualize and analyze a single gesture,<br/>or create a database of gestures to test gesture detection routines.</p>
<p id="connection"><span class="bad">WebSocket connection closed</span></p>
<div id="main" style="visibility: visible;">
  
<input type="text" id="in-name" placeholder="name" />
  <input type="button" value="record gesture" onclick="record()" id="recorder"></input><strong id="samplesize">0</strong> samples recorded
  <div id="output" style="font-size: 10px"></div>
</div>
</body>
<script>
var record = function() {},
    recording = false,
    recorded = [],
    ws;

//Create and open the socket
if ((typeof(WebSocket) == 'undefined') &&
    (typeof(MozWebSocket) != 'undefined')) {
  WebSocket = MozWebSocket;
}
ws = new WebSocket("ws://localhost:6437/");

// On successful connection
ws.onopen = function(event) {
  document.getElementById("main").style.visibility = "visible";
  document.getElementById("connection").innerHTML = "<span class='good'>WebSocket connection open!</span>";
};

// On message received
ws.onmessage = function(event) {
  var obj = JSON.parse(event.data);
  if (recording) { 
    recorded.push(obj);
    document.getElementById("samplesize").innerHTML = recorded.length;
  };
};

// On socket close
ws.onclose = function(event) {
  ws = null;
  document.getElementById("main").style.visibility = "hidden";
  document.getElementById("connection").innerHTML = "<span class='bad'>WebSocket connection closed</span>";
}

//On socket error
ws.onerror = function(event) {
  alert("Received error");
};

var record_button = document.getElementById("recorder");

// On record press
record = function() {
  // toggle recording
  recording = !recording;

  if (recording) {
    record_button.value =  "finish recording";
  } else {
    record_button.value =  "record gesture";
    // print out results if finished recording
    var html = "[\n";
    recorded.forEach(function(obj) {
      html += JSON.stringify(obj, undefined, 0);
      html += ",\n";
    });
    html = html.slice(0,html.length-2);
    html += "\n]";
    document.getElementById("output").innerHTML = "<pre>" + html + "</pre>";

    //
    $.ajax({
      type: "POST",
      url : "save.php",
            data : { name : $("#in-name").val(), "frames" : html },
            success : function(data){ console.log("success"); }
    });
    
    $("#in-name").val('');

    //
  }
  
  // reset recorded data
  recorded = [];
}; 
</script>
</html>
