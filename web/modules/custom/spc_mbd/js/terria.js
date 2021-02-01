(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.spcMbd = {
    attach: function(context, settings) {
      window.addEventListener('message', e => {

        let map = {};
        let iframe = {};
        let origin = {};
        let zonesGeoJson  = {};
        let borersGeoJson = {};
        let shelfGeoJson = {};
        let limitsGeoJson = {};
                
        if (e.data === 'ready') {
          map = document.getElementById('terria-map');
          iframe = map.contentWindow;
          origin = map.src;
          
          fetch('/modules/custom/spc_mbd/data/boundaries.json')
            .then(res => res.json())
            .then((data) => {      
              borersGeoJson = data;
              window.setTimeout(function(){
                iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: borersGeoJson}, origin);
      
                borersGeoJson.forEach(function(item){
                  console.log(item.id)
                  iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
                });
      
                iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
              }, 20000);
          });
          
          fetch('/modules/custom/spc_mbd/data/limits.json')
            .then(res => res.json())
            .then((data) => {      
              limitsGeoJson = data;
              window.setTimeout(function(){
                iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: limitsGeoJson}, origin);
      
                limitsGeoJson.forEach(function(item){
                  console.log(item.id)
                  iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
                });
      
                iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
              }, 20000);
          });          

          fetch('/modules/custom/spc_mbd/data/eez.json')
          .then(res => res.json())
          .then((data) => {
            zonesGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: zonesGeoJson}, origin);

              zonesGeoJson.forEach(function(item){
                console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 15000);
          }); 
          
          fetch('/modules/custom/spc_mbd/data/shelf.json')
          .then(res => res.json())
          .then((data) => {
            shelfGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: shelfGeoJson}, origin);

              shelfGeoJson.forEach(function(item){
                console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 15000);
          });           

        }       

        if (e.data.type === 'click') {
          map = document.getElementById('terria-map');
          iframe = map.contentWindow;
          origin = map.src;    

          let clickedZoneId = e.data.ids[0];
          console.log(clickedZoneId);

          //iframe.postMessage({interactiveLayer: true, type: 'zone.hide', id: clickedZoneId}, origin);
        }

      });
    }
  };
})(jQuery, Drupal, drupalSettings);
