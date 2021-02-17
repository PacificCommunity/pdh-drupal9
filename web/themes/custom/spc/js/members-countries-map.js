(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.spcMembersCountriesMap = {
    attach: function (context, settings) {
      membersCountriesAmountAll = $(".datasets-count .amount").text();
      membersCountriesUrl = $(".datasets-link a");
      membersCountriesCount = $(".datasets-count .amount");
      $("#members-countries-list option").each(function () {
        let total = [];
        total["amount"] = $(this).data("amount");
        total["url"] = $(this).data("url");

        membersCountriesAmount[$(this).val()] = total;
      });

      membersCountriesSelect = $("#members-countries-list")
        .select2({
          minimumResultsForSearch: -1,
          dataset: "",
        })
        .on("select2:select", function (e) {
          let data = e.params.data;
          let id = data.id;

          if (id.toLowerCase().includes("ki")) {
            membersCountriesUrl
              .attr("href", membersCountriesAmount["KI"]["url"])
              .show();
            membersCountriesCount.text(membersCountriesAmount[id]["amount"]);
          } else {
            if (membersCountriesAmount[id]["url"]) {
              membersCountriesUrl
                .attr("href", membersCountriesAmount[id]["url"])
                .show();
              membersCountriesCount.text(membersCountriesAmount[id]["amount"]);
            } else {
              membersCountriesUrl
                .attr("href", membersCountriesAmount[id]["url"])
                .hide();
              membersCountriesCount.text(membersCountriesAmountAll);
            }
          }

          let map = membersCountriesMap;
          let polygons = membersCountriesPoligon;
          let kiribatiPolygons = membersCountriesKiribatiPolygons;
          let markers = membersCountriesMarkers;

          let bounds = new google.maps.LatLngBounds();
          if (id.toLowerCase().includes("ki")) {
            kiribatiPolygons.forEach(function (ki, i, arr) {
              ki.getPath().forEach(function (element, index) {
                bounds.extend(element);
              });
            });
            map.fitBounds(bounds);
          } else if (id.toLowerCase().includes("countries")) {
            map.setZoom(3);
            map.setCenter(
              new google.maps.LatLng(
                membersCountriesLatLng.lat,
                membersCountriesLatLng.lng
              )
            );
            bounds = false;
          } else {
            polygons[id].getPath().forEach(function (element, index) {
              bounds.extend(element);
            });
            map.fitBounds(bounds);
          }

          for (i = 0; i < markers.length; i++) {
            markers[i].setVisible(false);
          }

          // Setting new marker.
          if (bounds) {
            var marker_icon = {
              url: "/themes/custom/spc/img/markers/spc-marker.png",
              size: new google.maps.Size(24, 33),
            };

            var marker = new google.maps.Marker({
              position: bounds.getCenter(),
              map: map,
              animation: google.maps.Animation.DROP,
              icon: marker_icon,
            });
            markers.push(marker);
          }
        });
    },
  };
})(jQuery, Drupal, drupalSettings);

var membersCountriesMap;
var membersCountriesData;
var membersCountriesPoligon;
var membersCountriesKiribatiPolygons;
var membersCountriesSelect;
var membersCountriesMarkers;
var membersCountriesCount;
var membersCountriesUrl;
var membersCountriesAmountAll;
var membersCountriesAmount = [];
var membersCountriesLatLng = {
  lat: -15.4492793,
  lng: 167.595411,
};

function initMap() {
  const mapDiv = document.getElementById("members-countries-map");
  if (!mapDiv) {
    return;
  }
  const map = (membersCountriesMap = new google.maps.Map(mapDiv, {
    center: {
      lat: membersCountriesLatLng.lat,
      lng: membersCountriesLatLng.lng,
    },
    zoom: 3,
    disableDefaultUI: true,
    styles: [
      { elementType: "geometry", stylers: [{ color: "#ffffff" }] },
      { elementType: "labels.text.stroke", stylers: [{ color: "#ffffff" }] },
      { elementType: "labels.text.fill", stylers: [{ color: "#000000" }] },
      {
        featureType: "administrative",
        elementType: "geometry.fill",
        stylers: [{ color: "#000000" }],
      },
      {
        featureType: "administrative.province",
        elementType: "geometry.stroke",
        stylers: [{ color: "#000000" }],
      },
      {
        featureType: "administrative.locality",
        elementType: "labels.text.fill",
        stylers: [{ color: "#000000" }],
      },
      {
        featureType: "road",
        elementType: "geometry",
        stylers: [{ color: "#38414e" }],
      },
      {
        featureType: "road",
        elementType: "geometry.stroke",
        stylers: [{ color: "#212a37" }],
      },
      {
        featureType: "road",
        elementType: "labels.text.fill",
        stylers: [{ color: "#9ca5b3" }],
      },
      {
        featureType: "road.highway",
        elementType: "geometry",
        stylers: [{ color: "#746855" }],
      },
      {
        featureType: "road.highway",
        elementType: "geometry.stroke",
        stylers: [{ color: "#1f2835" }],
      },
      {
        featureType: "road.highway",
        elementType: "labels.text.fill",
        stylers: [{ color: "#f3d19c" }],
      },
      {
        featureType: "transit",
        elementType: "geometry",
        stylers: [{ color: "#000000" }],
      },
      {
        featureType: "transit.station",
        elementType: "labels.text.fill",
        stylers: [{ color: "#000000" }],
      },
      {
        featureType: "water",
        elementType: "geometry",
        stylers: [{ color: "#33c5ec" }],
      },
      {
        featureType: "water",
        elementType: "labels.text.fill",
        stylers: [{ color: "#515c6d" }],
      },
      {
        featureType: "water",
        elementType: "labels.text.stroke",
        stylers: [{ color: "#17263c" }],
      },
    ],
  }));

  var controlDiv, controlWrapper, zoomInButton, zoomOutButton;

  controlDiv = document.createElement("div");
  controlDiv.className = "zoom__controls";

  controlWrapper = document.createElement("div");
  controlWrapper.className = "controls__wrapper";

  zoomInButton = document.createElement("div");
  zoomInButton.className = "controls--zoom-in";
  zoomInButton.textContent = "+";

  zoomOutButton = document.createElement("div");
  zoomOutButton.className = "controls--zoom-out";
  zoomOutButton.textContent = "-";

  controlDiv.appendChild(controlWrapper);
  controlWrapper.appendChild(zoomInButton);
  controlWrapper.appendChild(zoomOutButton);

  google.maps.event.addDomListener(zoomInButton, "click", function () {
    map.setZoom(map.getZoom() + 1);
  });

  google.maps.event.addDomListener(zoomOutButton, "click", function () {
    map.setZoom(map.getZoom() - 1);
  });

  map.controls[google.maps.ControlPosition.TOP_RIGHT].push(controlDiv);

  fetch("/modules/custom/spc_main/data/members-countries.json")
    .then((res) => res.json())
    .then((data) => {
      let markers = (membersCountriesMarkers = []);
      let polygons = [];

      let kiribatiPolygons = [];
      let kiribatiMarkers = [];

      data.forEach(function (country, i, arr) {
        var coordinates = country.geometry.coordinates;
        var tag = country.properties.ISO_Ter1;
        var polygonData = [];
        var id = country.properties.MRGID;

        var x = country.properties.x_1;
        var y = country.properties.y_1;

        coordinates.forEach(function (point, i, arr) {
          point.forEach(function (item, i, arr) {
            polygonData.push(new google.maps.LatLng(item[1], item[0]));
          });

          if (id.toLowerCase().includes("ki")) {
            tag = "KI";
          }

          markers[id] = new google.maps.Marker({
            position: new google.maps.LatLng(y, x),
            map: map,
            label: {
              text: tag,
              color: "#fff",
              fontWeight: "800",
              fontSize: "12px",
            },
            icon: {
              url: " ",
            },
            visible: false,
          });

          if (id.toLowerCase().includes("ki")) {
            kiribatiMarkers.push(markers[id]);
          }
        });

        polygons[id] = new google.maps.Polygon({
          paths: polygonData,
          strokeColor: "#e6f0f6",
          strokeOpacity: 0.8,
          strokeWeight: 2,
          fillColor: "#e6f0f6",
          fillOpacity: 0.35,
          country: tag.toLowerCase(),
          id: id.toLowerCase(),
        });

        polygons[id].setMap(map);

        if (id.toLowerCase().includes("ki")) {
          kiribatiPolygons.push(polygons[id]);
        }

        google.maps.event.addListener(polygons[id], "mouseover", function () {
          this.setOptions({ fillColor: "#ccc" });
          markers[id].setVisible(true);

          if (id.toLowerCase().includes("ki")) {
            kiribatiPolygons.forEach(function (ki, i, arr) {
              ki.setOptions({ fillColor: "#ccc" });
            });
            kiribatiMarkers.forEach(function (ki, i, arr) {
              ki.setVisible(true);
            });
          }
        });

        google.maps.event.addListener(polygons[id], "mouseout", function () {
          this.setOptions({ fillColor: "#e6f0f6" });
          markers[id].setVisible(false);

          if (id.toLowerCase().includes("ki")) {
            kiribatiPolygons.forEach(function (ki, i, arr) {
              ki.setOptions({ fillColor: "#e6f0f6" });
            });
            kiribatiMarkers.forEach(function (ki, i, arr) {
              ki.setVisible(false);
            });
          }
        });

        google.maps.event.addListener(markers[id], "click", polygonClick);
        google.maps.event.addListener(polygons[id], "click", polygonClick);

        function polygonClick() {
          var bounds = new google.maps.LatLngBounds();
          if (id.toLowerCase().includes("ki")) {
            kiribatiPolygons.forEach(function (ki, i, arr) {
              ki.getPath().forEach(function (element, index) {
                bounds.extend(element);
              });
            });
          } else {
            polygons[id].getPath().forEach(function (element, index) {
              bounds.extend(element);
            });
          }
          map.fitBounds(bounds);

          for (i = 0; i < markers.length; i++) {
            markers[i].setVisible(false);
          }

          // Setting new marker.
          if (bounds) {
            var marker_icon = {
              url: "/themes/custom/spc/img/markers/spc-marker.png",
              size: new google.maps.Size(24, 33),
            };

            var marker = new google.maps.Marker({
              position: bounds.getCenter(),
              map: map,
              animation: google.maps.Animation.DROP,
              icon: marker_icon,
            });
            markers.push(marker);
          }

          if (id.toLowerCase().includes("ki")) {
            membersCountriesSelect.val("KI").trigger("change");
            membersCountriesCount.text(membersCountriesAmount["KI"]["amount"]);
            membersCountriesUrl
              .attr("href", membersCountriesAmount["KI"]["url"])
              .show();
          } else {
            membersCountriesSelect.val(id).trigger("change");
            membersCountriesCount.text(membersCountriesAmount[id]["amount"]);
            membersCountriesUrl
              .attr("href", membersCountriesAmount[id]["url"])
              .show();
          }
        }
      });

      membersCountriesData = data;
      membersCountriesPoligon = polygons;
      membersCountriesKiribatiPolygons = kiribatiPolygons;
    })
    .catch((err) => console.error(err));
}
