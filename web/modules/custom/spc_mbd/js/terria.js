
window.addEventListener('message', e => {
  
  let map = {};
  let iframe = {};
  let origin = {};
  let zonesGeoJson  = {};
  let borersGeoJson = {};
  
  if (e.data === 'ready') {
    map = document.getElementById('terria-map');
    iframe = map.contentWindow;
    origin = map.src;
    
    fetch('/modules/custom/spc_mbd/js/eez.json')
    .then(res => res.json())
    .then((data) => {
      zonesGeoJson = data;

      window.setTimeout(function(){
        iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: zonesGeoJson}, origin);
        
        zonesGeoJson.forEach(function(item){
          iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
        });

        iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
      }, 1000);
     
    });  
    
    fetch('/modules/custom/spc_mbd/js/eez-borders.json')
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

        }, 1000);
     
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
