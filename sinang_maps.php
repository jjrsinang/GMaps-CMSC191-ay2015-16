<!DOCTYPE html>
<html>
<head>
<style>
html {
	height: 100%;
}
body {
	height: 100%;
	margin: 0;
	padding: 0;
}
#map-canvas {
	height: 100%;
}
#iw_container .iw_title {
	font-size: 16px;
	font-weight: bold;
}
.iw_content {
	padding: 15px 15px 15px 0;
}
</style>
<script src="http://maps.googleapis.com/maps/api/js?key=API_KEY"></script>
</head>

<body>

<?php
	
	$conn = mysqli_connect("localhost","root","","googlemaps");

	// Check connection
	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$sql = "SELECT * FROM markers";
	$result = $conn->query($sql);
	
	$data = array();
	
	if ($result->num_rows > 0) {
		// output data of each row
		$i = 1;
		while($row = $result->fetch_assoc()) {
			// put data to DOM for easy javascript access
			$data[] = $row;
			echo '<div id="marker-'.$i.'-id" style="display: none;" >'.$row["id"].'</div>';
			echo '<div id="marker-'.$i.'-name" style="display: none;" >'.$row["name"].'</div>';
			echo '<div id="marker-'.$i.'-address" style="display: none;" >'.$row["address"].'</div>';
			echo '<div id="marker-'.$i.'-lat" style="display: none;" >'.$row["lat"].'</div>';
			echo '<div id="marker-'.$i.'-lng" style="display: none;" >'.$row["lng"].'</div>';
			echo '<div id="marker-'.$i.'-type" style="display: none;" >'.$row["type"].'</div>';
			$i++;
		}
	} else {
		echo "0 results";
	}
	
	
	$conn->close();
?>

<div id="map-canvas" style="width:1500px;height:1500px;"></div>
</body>
<script>
//var myUPLB = new google.maps.LatLng(, );
//var myVegaCenter = new google.maps.LatLng(, );
//var myIRRI = new google.maps.LatLng(, );
// necessary variables
var map;
var infoWindow;
var count = <?php echo count($data); ?>;

// markersData variable stores the information necessary to each marker
var markersData = new Array();

// get data from DOM
for (var i = 1; i <= count; i++) {
	var marker = {};
	marker.id = document.getElementById("marker-"+i+"-id").textContent;
	marker.name = document.getElementById("marker-"+i+"-name").textContent;
	marker.address1 = document.getElementById("marker-"+i+"-address").textContent;
	marker.lat = document.getElementById("marker-"+i+"-lat").textContent;
	marker.lng = document.getElementById("marker-"+i+"-lng").textContent;
	marker.type = document.getElementById("marker-"+i+"-type").textContent;
	markersData.push(marker);
}

function initialize() {
   var mapOptions = {
      center: new google.maps.LatLng(14.167525,121.243368),
      zoom: 12,
      mapTypeId: 'roadmap',
   };

   map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

   // a new Info Window is created
   infoWindow = new google.maps.InfoWindow();

   // Event that closes the Info Window with a click on the map
   google.maps.event.addListener(map, 'click', function() {
      infoWindow.close();
   });

   // Finally displayMarkers() function is called to begin the markers creation
   displayMarkers();
}
google.maps.event.addDomListener(window, 'load', initialize);


// This function will iterate over markersData array
// creating markers with createMarker function
function displayMarkers(){

   // this variable sets the map bounds according to markers position
   var bounds = new google.maps.LatLngBounds();
   
   // for loop traverses markersData array calling createMarker function for each marker 
   for (var i = 0; i < markersData.length; i++){
	  
	  var id = markersData[i].id;
      var latlng = new google.maps.LatLng(markersData[i].lat, markersData[i].lng);
      var name = markersData[i].name;
      var address1 = markersData[i].address1;
      var address2 = markersData[i].address2;
      var postalCode = markersData[i].postalCode;
	  var type = markersData[i].type;

      createMarker(id, latlng, name, address1, address2, postalCode, type);

      // marker position is added to bounds variable
      bounds.extend(latlng);  
   }

   // Finally the bounds variable is used to set the map bounds
   // with fitBounds() function
   map.fitBounds(bounds);
   
   // Add the circle for SM Calamba
   var cityCircle = new google.maps.Circle({
      strokeColor: '#FF0000',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#FF0000',
      fillOpacity: 0.35,
      map: map,
      center: {lat: 14.202888, lng: 121.155655},
      radius: 250
    });
	
	// connect the malls
	var flightPlanCoordinates = [
      {lat: 14.167870, lng: 121.243820},
      {lat: 14.177290, lng: 121.242157},
      {lat: 14.179185, lng: 121.239067},
      {lat: 14.202888, lng: 121.155655}
    ];
    var flightPath = new google.maps.Polyline({
      path: flightPlanCoordinates,
      geodesic: true,
      strokeColor: '#FF0000',
      strokeOpacity: 1.0,
      strokeWeight: 2
    });

    flightPath.setMap(map);

}

// This function creates each marker and it sets their Info Window content
function createMarker(id, latlng, name, address1, address2, postalCode, type){
	
  // select custom image according to type
  var url = null;
  if (type == 'Restaurant') {
	  url = 'markers/yellow_MarkerR.png';
  } else if (type == 'Auditorium') {
	  url = 'markers/brown_MarkerA.png';
  } else if (type == 'Mall') {
	  url = 'markers/darkgreen_MarkerM.png';
  } else if (type == 'Inn') {
	  url = 'markers/orange_MarkerI.png';
  } else if (type == 'Bank') {
	  url = 'markers/paleblue_MarkerB.png';
  } else if (type == 'Municipal Hall') {
	  url = 'markers/pink_MarkerH.png';
  } else if (type == 'Resort') {
	  url = 'markers/blue_MarkerR.png';
  } else if (type == 'Amusement Park') {
	  url = 'markers/brown_MarkerA.png';
  }
  
  // Marker sizes are expressed as a Size of X,Y where the origin of the image
  // (0,0) is located in the top left of the image.

  // Origins, anchor positions and coordinates of the marker increase in the X
  // direction to the right and in the Y direction down.
  var image = {
    url: url,
    // This marker is 20 pixels wide by 32 pixels high.
    size: new google.maps.Size(20, 32),
    // The origin for this image is (0, 0).
    origin: new google.maps.Point(0, 0),
    // The anchor for this image is the base of the flagpole at (0, 32).
    anchor: new google.maps.Point(10, 32)
  };

  // actual creation of marker
   var marker = new google.maps.Marker({
      map: map,
      position: latlng,
      title: name,
	  animation: google.maps.Animation.DROP,
	  icon: image
   });
   
   // animations
   marker.addListener('click', toggleBounce);
   function toggleBounce() {
	if (marker.getAnimation() !== null) {
	  marker.setAnimation(null);
    } else {
	marker.setAnimation(google.maps.Animation.BOUNCE);
    }
   }

   // This event expects a click on a marker
   // When this event is fired the Info Window content is created
   // and the Info Window is opened.
   google.maps.event.addListener(marker, 'click', function() {
      
      // Creating the content to be inserted in the infowindow
      var iwContent = '<div id="iw_container">' +
            '<div class="iw_title">' + name + '</div>' +
         '<div class="iw_content">' + address1 + '<br />' +
         postalCode + '</div></div>';
      
      // including content to the Info Window.
      infoWindow.setContent(iwContent);

      // opening the Info Window in the current map and at the current marker location.
      infoWindow.open(map, marker);
   });
}
</script>
</html>
