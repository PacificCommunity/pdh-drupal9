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
        const mapData = drupalSettings.spcMbd.map;

        if (e.data === 'ready') {
          map = document.getElementById('terria-map');
          iframe = map.contentWindow;
          origin = map.src;
          
          fetch('/sites/default/files/mbd/boundaries.json')
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
          
          fetch('/sites/default/files/mbd/limits.json')
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

          fetch('/sites/default/files/mbd/eez.json')
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
          
          fetch('/sites/default/files/mbd/shelf.json')
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
        
        
        let eezPopup = $('#eez-popup');
        let limitPopup = $('#limit-popup');
        let shelfPopup = $('#shelf-popup');
        let boundaryPopup = $('#boundary-popup');
        
        const dialogConfig = {
          modal: true,
          autoOpen: false,
          draggable: false,
          resizable: false,
          width: 600,
          open: function(event, ui){ 
            $('.ui-widget-overlay').bind('click', function(){ 
                eezPopup.dialog('close'); 
                limitPopup.dialog('close'); 
                shelfPopup.dialog('close'); 
                boundaryPopup.dialog('close'); 
            }); 
          }
        };

        eezPopup.dialog(dialogConfig);
        limitPopup.dialog(dialogConfig);
        shelfPopup.dialog(dialogConfig);
        boundaryPopup.dialog(dialogConfig);
        
        if (e.data.type === 'click') {
          map = document.getElementById('terria-map');
          iframe = map.contentWindow;
          origin = map.src; 

          let clickedZoneId = e.data.ids[0];
          let target = {};
          
          console.log(clickedZoneId);

          if (clickedZoneId.includes('eez-')){
            target = mapData.eez[clickedZoneId];

            eezPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'">' + target.country.name +' ('+target.country.code +')</a>' );
            eezPopup.find('.area .value').text(target.area);
            eezPopup.find('.treaties .value').text(target.treaties);
            eezPopup.find('.pockets .value').text(target.pockets);
            eezPopup.find('.shelf .value').text(target.shelf);
            eezPopup.find('.ecs .value').text(target.ecs);
            eezPopup.find('.deposited .value').text(target.deposited);
            eezPopup.find('.date .value').text(target.date);
            eezPopup.find('.url .value').html('<a href="'+target.url+'">'+target.url+'</a>');
            eezPopup.dialog( 'open' );
            
          } else if (clickedZoneId.includes('limit-')){
            target = mapData.limits[clickedZoneId];
            limitPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'">' + target.country.name +' ('+target.country.code +')</a>' );
            limitPopup.find('.deposited .value').text(target.deposited);
            limitPopup.find('.date .value').text(target.date);
            limitPopup.find('.url .value').html('<a href="'+target.url+'">'+target.url+'</a>');
            limitPopup.dialog( 'open' );
            
          } else if (clickedZoneId.includes('shelf-')){
            target = mapData.shelf[clickedZoneId];
            shelfPopup.find('.name .value').text(target.name);
            shelfPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'">' + target.country.name +' ('+target.country.code +')</a>' );
            shelfPopup.find('.submission-done .value').text(target.submission_done);
            shelfPopup.find('.submission-complied .value').text(target.submission_complied);
            shelfPopup.find('.defence-year .value').text(target.defence_year);
            shelfPopup.find('.established-year .value').text(target.established_year);
            shelfPopup.find('.joint-submission .value').text(target.joint_submission);
            shelfPopup.find('.recommendation .value').text(target.recommendation); 
            shelfPopup.find('.date .value').text(target.date);
            shelfPopup.dialog( 'open' );
            
          }  else if (clickedZoneId.includes('boundary-')){
            target = mapData.boundary[clickedZoneId];
            boundaryPopup.find('.country .value.one').html('<img src="'+target.country_one.flag+'"><a href="'+target.country_one.url+'">' + target.country_one.name +' ('+target.country_one.code +')</a>' );;
            boundaryPopup.find('.country .value.two').html('<img src="'+target.country_two.flag+'"><a href="'+target.country_two.url+'">' + target.country_two.name +' ('+target.country_two.code +')</a>' );;
            boundaryPopup.find('.signed .value').text(target.signed);
            boundaryPopup.find('.year-signed .value').text(target.year_signed);
            boundaryPopup.find('.force .value').text(target.force); 
            boundaryPopup.find('.date .value').text(target.date);
            boundaryPopup.find('.url .value').html('<a href="'+target.url+'">'+target.url+'</a>');
            boundaryPopup.dialog( 'open' );
          }     
          
          console.log(target);

          //iframe.postMessage({interactiveLayer: true, type: 'zone.hide', id: clickedZoneId}, origin);
        }

      });
    }
  };
})(jQuery, Drupal, drupalSettings);
