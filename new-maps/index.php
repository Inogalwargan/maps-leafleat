<?php
// Create database connection using config file
include_once("config.php");

// Fetch all users data from database
$result = mysqli_query($mysqli, "SELECT * FROM provinsi ORDER BY id DESC");

$outlet = mysqli_query($mysqli, "SELECT * FROM outlet ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
	
	<title>Indonesia Choropleth Maps- Leaflet</title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" type="text/css" href="leafleat/leaflet.css">

	<script type="text/javascript" src="leafleat/leaflet.js"></script>


	<style>
		html, body {
			height: 100%;
			margin: 0;
		}
		#map {
			width: 600px;
			height: 400px;
		}
	</style>

	<style>#map { width: 80%; height: 80%; }
	.info { padding: 6px 8px; font: 14px/16px Arial, Helvetica, sans-serif; background: white; background: rgba(255,255,255,0.8); box-shadow: 0 0 15px rgba(0,0,0,0.2); border-radius: 5px; } .info h4 { margin: 0 0 5px; color: #777; }
.legend { text-align: left; line-height: 18px; color: #555; } .legend i { width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.7; }</style>
</head>
<body>

	<div id='map'></div>

	<!-- <script type="text/javascript" src="us-states.js"></script> -->
	<script type="text/javascript" src="indonesia-prov.js"></script>
	<!-- <script type="text/javascript" src="kalimantan-sob.js"></script> -->

	<script type="text/javascript">
		// var map = L.map('map').setView([-2.548926, 118.0148634], 5);
		
		var cities = L.layerGroup();
		var outlet = L.layerGroup();

		var LeafIcon = L.Icon.extend({
			options: {
				shadowUrl: 'leaf-shadow.png',
				iconSize:     [38, 95],
				shadowSize:   [50, 64],
				iconAnchor:   [22, 94],
				shadowAnchor: [4, 62],
				popupAnchor:  [-3, -76]
			}
		});
	var greenIcon = new LeafIcon({iconUrl: 'leaf-green.png'}); //({iconUrl: 'shopping.png'});
	<?php

	$js = '';
	$js_outlet = '';

	while($user_data = mysqli_fetch_array($result)) {          
		$js .= 'L.marker(['.$user_data['longtitude'].', '.$user_data['latitude'].'], {icon: greenIcon}).bindPopup("<b>'.$user_data['keterangan'].'</b>").addTo(cities);';
	}

	while($out = mysqli_fetch_array($outlet)) {          
		$js .= 'L.marker(['.$out['longtitude'].', '.$out['latitude'].']).bindPopup("<b>'.$out['nama_outlet'].'</b>").addTo(outlet);';
	}

	echo $js; //print lokasi kota
	echo $js_outlet; //print lokasi outlet
	?>

	var mbAttr = 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
	'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
	'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
	mbUrl = 'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw';

	var grayscale   = L.tileLayer(mbUrl, {id: 'mapbox/light-v9', tileSize: 512, zoomOffset: -1, attribution: mbAttr}),
	streets  = L.tileLayer(mbUrl, {id: 'mapbox/streets-v11', tileSize: 512, zoomOffset: -1, attribution: mbAttr}),
	navigation = L.tileLayer(mbUrl, {id: 'mapbox/navigation-guidance-night-v4', tileSize: 512, zoomOffset: -1, attribution: mbAttr}),
	satelite = L.tileLayer(mbUrl, {id: 'mapbox/satellite-streets-v11', tileSize: 512, zoomOffset: -1, attribution: mbAttr});


	var map = L.map('map', {
		center: [-1.548926, 117.0148634],
		zoom: 5,
			layers: [grayscale, cities] //jika ingin muncul (centang) semua tulis outlet & cities
		});



	var baseLayers = {
		"Grayscale": grayscale,
		"Streets": streets,
		"Navigation": navigation,
		"Satelite": satelite
	};

	var overlays = {
		"Ibu Kota Provinsi": cities,
		"Outlet": outlet
	};

	L.control.layers(baseLayers, overlays).addTo(map);

	



	L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
		'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
		'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		id: 'mapbox/light-v9',
		tileSize: 512,
		zoomOffset: -1
	}).addTo(map);




	// control that shows state info on hover
	var info = L.control();
	info.onAdd = function (map) {
		this._div = L.DomUtil.create('div', 'info');  // create a div with a class "info"
		this.update();
		return this._div;
	};

	var a = 100;

	// method that we will use to update the control based on feature properties passed
	info.update = function (props) {
		this._div.innerHTML = '<h4>Peta Indonesia Berdasarkan Provinsi</h4>' +  (props ?
			'<b>' + props.Propinsi + '</b><br /> Kode Provinsi : ' + props.kode
			+ '</b><br />SUMBER : ' + props.SUMBER + '</b><br />Omset Wismilak : ' + props.omset
			: 'Sorot Provinsi di Indonesia untuk Zoom Peta & Menampilkan Informasi');
	};

	info.addTo(map);


	// get color depending on population density value
	function getColor(d) {
		return d > 1000  ? '#800026' :
		d > 500  ? '#BD0026' :
		d > 200  ? '#E31A1C' :
		d > 100  ? '#FC4E2A' :
		d > 50   ? '#FD8D3C' :
		d > 20   ? '#FEB24C' :
		d > 10   ? '#FED976' :
		'#FFEDA0';
	}

	function style(feature) {
		return {
			weight: 2,
			opacity: 1,
			color: 'white', //garis peta
			dashArray: '3',
			fillOpacity: 0.7, //kecerahan peta
			fillColor: getColor(feature.properties.omset) //pengelompokan warna berdasarkan populasi
		};
	}

	function highlightFeature(e) {
		var layer = e.target;

		layer.setStyle({
			weight: 5,
			color: '#2ecc71', //warna garis pembatas saat di sorot
			dashArray: '',
			fillOpacity: 0.7
		});

		if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
			layer.bringToFront();
		}

		info.update(layer.feature.properties);
	}

	var geojson;

	function resetHighlight(e) {
		geojson.resetStyle(e.target);
		info.update();
	}

	function zoomToFeature(e) {
		map.fitBounds(e.target.getBounds());
	}

	function onEachFeature(feature, layer) {
		layer.on({
			mouseover: highlightFeature, //untuk menampilkan highlight (keterangan pd provinsi)
			mouseout: resetHighlight, //untuk reset highlight (keterangan pd provinsi)
			click: zoomToFeature //untuk zoom ketika di klik
		});
	}

	geojson = L.geoJson(statesData, { //statesData: variable pada file indonesia-prov.js yg berisi GEOJSON
		style: style, //panggil fungsi style
		onEachFeature: onEachFeature //panggil fungsi onEachFeature
	}).addTo(map); //memasukan semua fungsi dan gejson pd maps

	//Populasi data 

	// map.attributionControl.addAttribution('Population data &copy; <a href="http://census.gov/">US Census Bureau</a>');


	var legend = L.control({position: 'bottomleft'}); // posisi legend

	legend.onAdd = function (map) {

		var div = L.DomUtil.create('div', 'info legend'),
		grades = [0, 10, 20, 50, 100, 200, 500, 1000],
		labels = [],
		from, to;

		for (var i = 0; i < grades.length; i++) {
			from = grades[i];
			to = grades[i + 1];

			labels.push(
				'<i style="background:' + getColor(from + 1) + '"></i> ' +
				from + (to ? '&ndash;' + to : '+'));
		}

		div.innerHTML = labels.join('<br>');
		return div;
	};

	legend.addTo(map);

</script>



</body>
</html>
