<?php
require('config.php');
?><!DOCTYPE html>
<html>
  <head>
    <title>Fun Flying Facts</title>
	<link href='https://fonts.googleapis.com/css?family=Aldrich' rel='stylesheet'>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
		#map {
			height: 100%;
		}
		/* Optional: Makes the sample page fill the window. */
		html, body {
			height: 100%;
			margin: 0;
			padding: 0;
			transform-style:preserve-3d;
			transform:perspective(auto) translateZ(-30vw) scale(1.4);
			perspective:1000;  
		}
		.eta {
			position: absolute;
			width: 200px;
			top: 10px;
			z-index: 1;
			height: 100px;
			background-color: #b5b4b4;
			left: calc(50% - 100px);
			border-radius: 10px;
			border: 2px solid whitesmoke;
			text-align: center;
			line-height: 1.5;
			font-size: 33px;
			font-family: Aldrich;
		}
		.plane-icon {
			position: absolute;
			/* transform: rotate(1.36rad); */
			left: calc(50% - 25px);
			top: calc(50% - 25px);
			z-index: 2;
			-webkit-transition: -webkit-transform .8s ease-in-out;
			-ms-transition: -ms-transform .8s ease-in-out;
			transition: transform .8s ease-in-out;  
		}
    </style>
  </head>
  <body>
	<div class="plane-icon"><img width="100px" src="/images/plane_icon.png"/></div>
	<div class="eta"></div>
    <div id="map"></div>
	  <script src="/js/jquery.min.js"></script>
	<script src="/js/md5.js"></script>
	  <script>
		  
		  
		var map;
		var markers = [];
		var info_windows = [];
		var iterator = 9;

		var start_airport = "DFW";
		var end_airport = "LON";
		var start_airport_latlng;
		var end_airport_latlng;
		
		var movement_step = 0.5;
		var arrival_tolerance = movement_step * 2;
		var time_interval = 1000; // ms
		var info_window_limit = 5;

		function shuffle_array(array) { // by ref
			for (var i = array.length - 1; i > 0; i--) {
				var j = Math.floor(Math.random() * (i + 1));
				var temp = array[i];
				array[i] = array[j];
				array[j] = temp;
			}
		}
		  
		function is_info_window_open(info_window){
			var map = info_window.getMap();
			return (map !== null && typeof map !== "undefined");
		}
		
		function display_visible_cities() {
			for (marker in markers) {
				if (map.getBounds().contains(markers[marker].getPosition(markers[marker])) && typeof info_windows[marker] === 'undefined') {
					info_windows[marker] = make_info_window(markers[marker].aa_info);
					//info_windows[marker].open(map, markers[marker]);
				} else if (!map.getBounds().contains(markers[marker].getPosition(markers[marker])) && typeof info_windows[marker] !== 'undefined') {
					//console.log('delete', info_windows[marker]);
					close_info_window(info_windows[marker]);
					delete(info_windows[marker]); // delete() always feels wrong, like using goto statements...
				}
			}
			
			/*
			var info_windows_in_viewarea = 0;
			var info_windows_in_viewarea_offset = [];
			var info_windows_in_viewarea_visible_offset = [];
			for (marker in markers) {
				if (typeof info_windows[marker] !== 'undefined') {
					//if (info_windows[marker].opened) {
						info_windows_in_viewarea++;
						info_windows_in_viewarea_offset.push(marker);
						console.log(info_windows[marker].opened);
					//}
				}
			}
			
			console.log("viewable info windows", info_windows_in_viewarea);
			
			if (info_windows_in_viewarea < markers_limit) {
				// add new markers
			}
			*/
			
			var opened_info_windows = 0;			
			for (info_window in info_windows) {
				if (typeof info_windows[info_window] !== 'undefined') {
					if (info_windows[info_window].opened) {
						opened_info_windows++;
					}
				}
			}
			console.log('opened_info_windows', opened_info_windows);
			
			if (opened_info_windows < info_window_limit) {
				var markers_in_viewarea = 0;
				var markers_in_viewarea_offset = [];
				var markers_in_viewarea_hidden_offset = [];
				for (marker in markers) {
					if (typeof info_windows[marker] !== 'undefined') {
						//if (info_windows[marker].opened) {
							markers_in_viewarea++;
							markers_in_viewarea_offset.push(marker);
							if (!info_windows[marker].opened) {
								markers_in_viewarea_hidden_offset.push(marker);
							}
							//console.log(info_windows[marker].opened);
						//}
					}
				}
				console.log('markers_in_viewarea', markers_in_viewarea);
				var needed_info_windows = info_window_limit - opened_info_windows;
				console.log('needed info windows', needed_info_windows);
				
				shuffle_array(markers_in_viewarea_hidden_offset);
				for (i = 0; i < needed_info_windows; i++) {
					if (typeof info_windows[markers_in_viewarea_hidden_offset[i]] !== 'undefined') {
						open_info_window(info_windows[markers_in_viewarea_hidden_offset[i]], map, markers[markers_in_viewarea_hidden_offset[i]]);
					}
				}
				
			}
			
			/*if (markers_in_viewarea > markers_limit) {
				for (marker in markers_in_viewarea_offset) {
					//console.log(markers_in_viewarea_offset[marker], info_windows[markers_in_viewarea_offset[marker]]);
					var info_window = info_windows[markers_in_viewarea_offset[marker]];
					//console.log(markers_in_viewarea_offset[marker], info_window.content, info_window, info_window.getMap());
					//console.log(.isOpen, info_windows[markers_in_viewarea_offset[marker]].getMap());
					if (info_window.opened) {
						markers_in_viewarea_visible_offset.push(markers_in_viewarea_visible_offset);
						markers_in_viewarea_offset = markers_in_viewarea_offset.splice(0, marker+1).concat(markers_in_viewarea_offset.splice(marker+2)); // remove already visible info windows
					}
				}
				if (markers_in_viewarea_visible_offset.length > markers_limit) {
					console.log(markers_limit + ' already open');
				} else {
					console.log('choose from these', markers_in_viewarea_offset.length, markers_in_viewarea_visible_offset.length);			
				}
			}*/
		}
		  
		function make_info_window(content) {
			//google.maps.InfoWindow.prototype.opened = false;
			/*var _content = [content[0], content[1], content[2]];
			if (content[2] == "US" || content[2] == "GB") {
				content[2] = "";
			} else {
				content[2] = ', ' + content[2];
			}*/
			//console.log(content, _content);
			var hash = $.md5(content[0] + content[1] + content[2]);
			var info_window = new google.maps.InfoWindow({ content: '<div id="' + hash + '"><img width="50px" src="https://www.shopthemarketplace.com/images/spinner.gif"/></div>', disableAutoPan: true });
                        info_window.opened = false;
			info_window.hash = hash;
			info_window.content = content;
			/*
			setTimeout(function() {
			$.get('/api/all.php?city=' + encodeURIComponent(content[0]) + "&state=" + encodeURIComponent(content[1]) + "&country_code=" + encodeURIComponent(_content[2]) + "&hash=" + hash, function(data) {
				var result = "";
				var temp = "";
				var hash = data[3];
				try {
					temp = data[0];
					result = data[1];
					console.log(hash, 'success', content);
				} catch (err) {
					// do nothing
					conole.log(err);
				}
				//console.log(data);
				var name = content[0] + ', ' + ((content[1]) ? content[1] : content[2]).replace(', ', '');
				if (typeof result == 'undefined' || !result) {
					result = '<em>No information found for <strong>' + content[0] + ', ' + name + '</strong>.</em>'
				} else {
					if (document.getElementById(hash)) {
						document.getElementById(hash).innerHTML = '<strong>' + name + ' (' + Math.round(temp, 2) + ' F)</strong><br>' + result;
					} else {
						console.log('hash not found', hash, content, document.getElementById(hash), $("#" + hash).length);
					}
				}
			});
			}, 500);
			*/
			//var info_window = new google.maps.InfoWindow({ content: '<div id="' + hash + '"><img width="50px" src="https://www.shopthemarketplace.com/images/spinner.gif"/></div>', disableAutoPan: true });
			//info_window.opened = false;

			return info_window;
		}

		function open_info_window(info_window, map, marker) {
			info_window.opened = true;

			var content = info_window.content;
			var hash = info_window.hash;

			//setTimeout(function() {
                        	$.get('/api/all.php?city=' + encodeURIComponent(content[0]) + "&state=" + encodeURIComponent(content[1]) + "&country_code=" + encodeURIComponent(content[2]) + "&hash=" + hash + "&lat=" + content[3] + "&lng=" + content[4], function(data) {
					var result = "";
                        	        var temp = "";
                        	        var hash = data[3];
                        	        try {
                        	                temp = data[0];
                        	                result = data[1];
                        	                console.log('success, got info for ', content);
                        	        } catch (err) {
                        	                // do nothing
                        	                conole.log(err);
                        	        }
                        	        //console.log(data);
                        	        var name = content[0] + ', ' + ((content[1]) ? content[1] : content[2]).replace(', ', '');
                        	        if (typeof result == 'undefined' || !result) {
                        	                result = '<em>No information found.</em>'
                        	        }
                       	                if (document.getElementById(hash)) {
                       	                        document.getElementById(hash).innerHTML = '<strong>' + name + ' (' + Math.round(temp, 2) + ' F)</strong><br>' + result;
                       	                } else {
                       	                        console.log('error, hash not found', hash);
                       	                }
                        	});
                        //}, 500);

			info_window.open(map, marker);
		}

		function close_info_window(info_window, map, marker) {
			info_window.opened = false;
			info_window.close();
		}

		function initMap() {
			$.get('https://raw.githubusercontent.com/AmericanAirlines/AA-Mock-Engine/master/mock/airports.json', function(data) {
				data = JSON.parse(data);
				map = new google.maps.Map(document.getElementById('map'), {
					center: {lat: 0, lng: 0},
					zoom: 7,
					mapTypeId: 'satellite'
				});

				google.maps.InfoWindow.prototype.opened = false;
				google.maps.InfoWindow.prototype.hash = null;
				google.maps.InfoWindow.prototype.content = [];
				google.maps.Marker.prototype.aa_info = false;

				google.maps.event.addListenerOnce(map, 'idle', function() {
					var i = 0;
					for (airport in data) {
						if (data[airport].code == start_airport) {
							map.setCenter(new google.maps.LatLng(data[airport].latitude, data[airport].longitude));
							start_airport_latlng = { lat: data[airport].latitude, lng: data[airport].longitude };
						}
						
						if (data[airport].code == end_airport) {
							end_airport_latlng = { lat: data[airport].latitude, lng: data[airport].longitude };
							console.log('goto', end_airport_latlng);
						}
						
						var marker = new google.maps.Marker({ position: {lat: data[airport].latitude, lng: data[airport].longitude}, map: map, title: data[airport].city });
						marker.aa_info = [data[airport].city, data[airport].stateCode, data[airport].countryCode, data[airport].latitude, data[airport].longitude];
						markers.push(marker);
						if (map.getBounds().contains(marker.getPosition(marker))) {
							if (i < info_window_limit) {
								info_windows[markers.length-1] = make_info_window(marker.aa_info);
								open_info_window(info_windows[markers.length-1], map, marker);
								i++;
							}
						}
					}
					
					var current_lat = map.getCenter().lat();
					var current_lng = map.getCenter().lng();
					
					var distance = { x: Math.abs(current_lat - end_airport_latlng.lat), y: Math.abs(current_lng - end_airport_latlng.lng) };
					
					var bearing_from_origin;
					if (distance.x > distance.y) {
						// x is the adjacent angle
						// y is the opposite angle
						bearing_from_origin = Math.atan(distance.x/distance.y);
					} else {
						// y is the adjacent angle
						// x is the opposite angle
						bearing_from_origin = Math.atan(distance.y/distance.x);
					}
					$(".plane-icon").css('transform', 'rotate(' + bearing_from_origin + 'rad)');
					console.log('bearing_from_origin', bearing_from_origin);
					console.log('distance', distance);
					
					var fly = setInterval(function() {
						
						distance = { x: Math.abs(current_lat - end_airport_latlng.lat), y: Math.abs(current_lng - end_airport_latlng.lng) };
						var steps = Math.sqrt((Math.pow(distance.x, 2) + Math.pow(distance.y, 2))) / movement_step;
						//if (distance.x > distance.y) {
						//	var steps = distance.x / movement_step;
						//} else {
						//	var steps = distance.y / movement_step;
						//}
						
						if (distance.x >= arrival_tolerance) {
							if (current_lat > end_airport_latlng.lat) {
								current_lat -= movement_step * Math.cos(bearing_from_origin);
							} else {
								current_lat += movement_step * Math.cos(bearing_from_origin);
							}
						}
						
						if (distance.y >= arrival_tolerance) {
							if (current_lng > end_airport_latlng.lng) {
								current_lng -= movement_step * Math.sin(bearing_from_origin);
							} else {
								current_lng += movement_step * Math.sin(bearing_from_origin);
							}
						}
										  
						var ETA = steps * time_interval / 1000; // s
						$(".eta").html('Remaining:<br><strong>' + Math.floor(ETA % 3600 / 60) + ' m ' + Math.floor(ETA % 3600 % 60) + ' s</strong>');
						
						map.setCenter(new google.maps.LatLng(current_lat, current_lng));
						
						console.log(current_lat, current_lng, ETA);
						//clearInterval(fly);
						if (Math.abs(current_lat - end_airport_latlng.lat) < arrival_tolerance && Math.abs(current_lng - end_airport_latlng.lng) < arrival_tolerance) {
							//clearInterval(fly);
							map.setCenter(new google.maps.LatLng(end_airport_latlng.lat, end_airport_latlng.lng));
							$(".plane-icon").css('transform', 'rotate(0rad)');
							$(".eta").text('Welcome to ' + end_airport);
							console.log('arrived');
						}
						
						display_visible_cities();
						
						
					}, time_interval);
				});
				
				
			});
		}
	  </script>
	  <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_JAVASCRIPT_MAPS_API_KEY; ?>&callback=initMap"
    async defer></script>
  </body>
</html>
