<!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>WEB GIS</title>

      <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

      <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
      crossorigin=""></script>


      <style>
          #map { height: 500px; }

          #marker-form {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            padding: 15px;
            z-index: 1000;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
          }
      </style>

  </head>
  <body>

    <div id="map"></div>

    <div id="marker-form" style="display:none;">
      <h3>Tambah Marker</h3>
      <form id="add-marker-form">
          <input type="hidden" id="marker-lat" name="latitude">
          <input type="hidden" id="marker-lng" name="longitude">
          <input type="hidden" id="marker-id" name="id">
          
          <label>Nama Lokasi:</label>
          <input type="text" id="marker-name" name="name" required><br>
          
          <label>Deskripsi:</label>
          <textarea id="marker-description" name="description"></textarea><br>
          
          <button type="submit">Simpan Marker</button>
          <button type="button" id="cancel-marker">Batal</button>

          <button type="button" id="update-marker" style="display:none;">Update Marker</button>
          <button type="button" id="delete-marker" style="display:none;">Hapus Marker</button>
      </form>
  </div>
  </body>

  <script>
    var map = L.map('map').setView([-8.704483, 115.2192535], 13);
    var markersLayer = L.layerGroup().addTo(map);


    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    });

    var osmHOT = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors, Tiles style by Humanitarian OSM Team'
    });

    var esriWorldImagery = L.tileLayer(
      'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics',
        maxZoom: 20
      });

    var cartoLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/">CARTO</a>',
      subdomains: 'abcd',
      maxZoom: 20
    });
    

    osm.addTo(map);

    var baseMaps = {
      "OpenStreetMap": osm,
      "OSM HOT": osmHOT,
      "Esri World Imagery": esriWorldImagery,
      "Carto Light": cartoLight
    };

    L.control.layers(baseMaps).addTo(map);

    var tempMarker = null;

    // Fungsi untuk menampilkan marker dari database
    // function loadMarkers() {
    //     fetch('/locations')
    //     .then(res => res.json())
    //     .then(data => {
    //         data.forEach(loc => {
    //             L.marker([loc.latitude, loc.longitude])
    //                 .addTo(map)
    //                 .bindPopup(`
    //                     <b>${loc.name || 'Unnamed Location'}</b><br>
    //                     ${loc.description || ''}<br>
    //                     Lat: ${loc.latitude.toFixed(5)}, Lng: ${loc.longitude.toFixed(5)}
    //                 `);
    //         });
    //     });
    // }

    // loadMarkers();

    // Ambil marker dari DB
    function loadMarkers() {
      markersLayer.clearLayers();
      fetch('/locations')
        .then(res => res.json())
        .then(data => {
          data.forEach(loc => {
            const marker = L.marker([loc.latitude, loc.longitude]).addTo(markersLayer);
            marker.on('click', function () {
              document.getElementById('marker-id').value = loc.id;
              document.getElementById('marker-name').value = loc.name;
              document.getElementById('marker-description').value = loc.description;
              document.getElementById('marker-lat').value = loc.latitude;
              document.getElementById('marker-lng').value = loc.longitude;

              document.getElementById('update-marker').style.display = 'inline';
              document.getElementById('delete-marker').style.display = 'inline';
              document.getElementById('marker-form').style.display = 'block';
            });
            marker.bindPopup(`
            <b>${loc.name}</b><br>
            ${loc.description}<br>
            Latitude: ${loc.latitude.toFixed(5)}<br>
            Longitude: ${loc.longitude.toFixed(5)}
        `);
         });
        });
    }
    loadMarkers();

// Klik peta -> form tambah marker
            map.on('click', function(e) {
              if (tempMarker) map.removeLayer(tempMarker);
              tempMarker = L.marker(e.latlng).addTo(map);

              document.getElementById('marker-form').style.display = 'block';
              document.getElementById('marker-id').value = '';
              document.getElementById('marker-name').value = '';
              document.getElementById('marker-description').value = '';
              document.getElementById('marker-lat').value = e.latlng.lat;
              document.getElementById('marker-lng').value = e.latlng.lng;

              document.getElementById('update-marker').style.display = 'none';
              document.getElementById('delete-marker').style.display = 'none';
            });

            // Simpan marker baru
            document.getElementById('add-marker-form').addEventListener('submit', function(e) {
              e.preventDefault();
              var id = document.getElementById('marker-id').value;
              var data = {
                name: document.getElementById('marker-name').value,
                description: document.getElementById('marker-description').value,
                latitude: parseFloat(document.getElementById('marker-lat').value),
                longitude: parseFloat(document.getElementById('marker-lng').value)
              };

              fetch('/locations', {
                method: id ? 'PUT' : 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(id ? {...data, id: id} : data)
              })
              .then(res => res.json())
              .then(data => {
                alert(data.message);
                this.reset();
                document.getElementById('marker-form').style.display = 'none';
                if (tempMarker) { map.removeLayer(tempMarker); tempMarker = null; }
                loadMarkers();
              });
            });

            // Update marker
            document.getElementById('update-marker').addEventListener('click', function () {
            document.getElementById('add-marker-form').dispatchEvent(new Event('submit'));
            });

            // Hapus marker
            document.getElementById('delete-marker').addEventListener('click', function () {
              const id = document.getElementById('marker-id').value;
              if (confirm('Yakin ingin menghapus marker ini?')) {
                fetch(`/locations/${id}`, {
                  method: 'DELETE',
                  headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                  }
                })
                .then(res => res.json())
                .then(data => {
                  alert(data.message);
                  document.getElementById('add-marker-form').reset();
                  document.getElementById('marker-form').style.display = 'none';
                  loadMarkers();
                });
              }
            });


  //   map.on('click', function(e) {
  //       var latlng = e.latlng;
        
  //       // Hapus marker sementara jika ada
  //       if (tempMarker) {
  //           map.removeLayer(tempMarker);
  //       }

  //       // Tambahkan marker sementara
  //       tempMarker = L.marker([latlng.lat, latlng.lng]).addTo(map);

  //       // Tampilkan form
  //       document.getElementById('marker-lat').value = latlng.lat;
  //       document.getElementById('marker-lng').value = latlng.lng;
  //       document.getElementById('marker-form').style.display = 'block';
  //   });

  //   // Event listener untuk form
  //   document.getElementById('add-marker-form').addEventListener('submit', function(e) {
  //     e.preventDefault();

  //     // Ambil nilai dari input tersembunyi
  //     var latitude = document.getElementById('marker-lat').value;
  //     var longitude = document.getElementById('marker-lng').value;
  //     var name = document.getElementById('marker-name').value;
  //     var description = document.getElementById('marker-description').value;

  //     // Buat objek data yang akan dikirim
  //     var jsonData = {
  //         name: name,
  //         description: description,
  //         latitude: parseFloat(latitude),
  //         longitude: parseFloat(longitude)
  //     };

  //     fetch('/locations', {
  //         method: 'POST',
  //         headers: {
  //             'Content-Type': 'application/json',
  //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
  //                 ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  //                 : ''
  //         },
  //         body: JSON.stringify(jsonData)
  //     })
  //     .then(res => res.json())
  //     .then(data => {
  //         alert(data.message);
          
  //         // Sembunyikan form
  //         document.getElementById('marker-form').style.display = 'none';
          
  //         // Reset form
  //         this.reset();

  //         // Hapus marker sementara
  //         if (tempMarker) {
  //             map.removeLayer(tempMarker);
  //             tempMarker = null;
  //         }

  //         // Muat ulang marker
  //         loadMarkers();
  //     })
  //     .catch(error => {
  //         console.error('Error:', error);
  //         alert('Gagal menyimpan marker');
  //     });
  // });

    // Tombol batal
    document.getElementById('cancel-marker').addEventListener('click', function() {
        // Hapus marker sementara
        if (tempMarker) {
            map.removeLayer(tempMarker);
            tempMarker = null;
        }

        // Sembunyikan form
        document.getElementById('marker-form').style.display = 'none';
    });

    // fetch('/locations')
    // .then(res => res.json())
    // .then(data => {
    //   data.forEach(loc => {
    //     L.marker([loc.latitude, loc.longitude])
    //       .addTo(map)
    //       .bindPopup(loc.name);
    //   });
    // });

      
    // map.on('click', function(e) {
    //   var latlng = e.latlng;
      
    //   const marker = L.marker([latlng.lat, latlng.lng])
    //     .addTo(map)
    //     .bindPopup("Titik baru di sini:<br>" + latlng.lat.toFixed(5) + ", " + latlng.lng.toFixed(5))
    //     .openPopup();

    //   fetch('/locations', {
    //     method: 'POST',
    //     headers: {
    //       'Content-Type': 'application/json',
    //       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    //     },
    //     body: JSON.stringify({
    //       latitude: latlng.lat,
    //       longitude: latlng.lng,
    //       name: 'Titik Baru'
    //     })
    //   })
    //   .then(res => res.json())
    //   .then(data => {
    //     alert(data.message);
    //   });
    // });

  </script>
</html>