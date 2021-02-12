(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.spcMbd = {
    attach: function(context, settings) {
      window.addEventListener('message', e => {
        
        console.log(drupalSettings.spcMbd);
        
        let map = {};
        let iframe = {};
        let origin = {};
        
        let zonesGeoJson  = {};
        let borersGeoJson = {};
        let shelfGeoJson = {};
        let limitsGeoJson = {};
        
        let baselineGeoJson = {};
        let seelimGeoJson = {};
        let marineGeoJson = {};
        let contiguousGeoJson = {};

        const mapData = drupalSettings.spcMbd.map;
        const countryCode = drupalSettings.spcMbd.countryCode;

        if (e.data === 'ready') {
          map = document.getElementById('terria-map');
          iframe = map.contentWindow;
          origin = map.src;
          
          window.setTimeout(function(){
            $('.globe').removeClass('loader');
          }, 50000);
          
          fetch('/sites/default/files/mbd/boundaries-' + countryCode + '.json')
            .then(res => res.json())
            .then((data) => {      
              if (data) {
                borersGeoJson = data;
                window.setTimeout(function(){
                  iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: borersGeoJson}, origin);

                  borersGeoJson.forEach(function(item){
                    console.log(item.id)
                    iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
                  });

                  iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
                }, 20000);
              }
          });
          
          fetch('/sites/default/files/mbd/limits-' + countryCode + '.json')
            .then(res => res.json())
            .then((data) => {   
              if (data) {
              limitsGeoJson = data;
              window.setTimeout(function(){
                iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: limitsGeoJson}, origin);
      
                limitsGeoJson.forEach(function(item){
                  console.log(item.id)
                  iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
                });
      
                iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
              }, 20000);
            }
          });
          
          fetch('/sites/default/files/mbd/eez-' + countryCode + '.json')
          .then(res => res.json())
          .then((data) => {
            if (data) {
            zonesGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: zonesGeoJson}, origin);

              zonesGeoJson.forEach(function(item){
                console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 20000);
          }
          });          

          fetch('/sites/default/files/mbd/shelf-' + countryCode + '.json')
          .then(res => res.json())
          .then((data) => {
            if (data) {
            shelfGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: shelfGeoJson}, origin);

              shelfGeoJson.forEach(function(item){
                console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 20000);
            }
          }); 

          fetch('/sites/default/files/mbd/contiguous-' + countryCode + '.json')
          .then(res => res.json())
          .then((data) => {
            if (data) {
            contiguousGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: contiguousGeoJson}, origin);

              contiguousGeoJson.forEach(function(item){
                console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 21000);
            }
          });
          
          fetch('/sites/default/files/mbd/seelim-' + countryCode + '.json')
          .then(res => res.json())
          .then((data) => {
            if (data) {
            seelimGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: seelimGeoJson}, origin);

              seelimGeoJson.forEach(function(item){
                console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 22000);
            }
          });          
          
          fetch('/sites/default/files/mbd/baseline-' + countryCode + '.json')
          .then(res => res.json())
          .then((data) => {
            if (data) {
            baselineGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: baselineGeoJson}, origin);

              baselineGeoJson.forEach(function(item){
                console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 23000);
            }
          });
          
          fetch('/sites/default/files/mbd/marine-' + countryCode + '.json')
          .then(res => res.json())
          .then((data) => {
            if (data) {
            marineGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: marineGeoJson}, origin);

              marineGeoJson.forEach(function(item){
                console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 24000);
            }
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
          width: 560,
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

          let clickedZoneId = null;
          let target = {};
          
          if (e.data.ids.length == 1){
            clickedZoneId = e.data.ids[0];
          } else if (e.data.ids.length > 1){
            for (let i = 0; i < e.data.ids.length; i++){
              if (e.data.ids[i].includes('limit-') || e.data.ids[i].includes('shelf-') || e.data.ids[i].includes('boundary-')){
                clickedZoneId = e.data.ids[i];
                break;
              } else if (e.data.ids[i].includes('eez-')){
                clickedZoneId = e.data.ids[i];
              }
            }
          }
          
          console.log(e.data.ids);
          console.log(clickedZoneId);

          if (clickedZoneId && clickedZoneId.includes('eez-') && clickedZoneId.includes(countryCode)){
            
            if (clickedZoneId.includes('eez-KI')){
              target = mapData.eez['eez-KI'];
            } else {
              target = mapData.eez[clickedZoneId];
            }

            eezPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'" target="_blank">' + target.country.name +' ('+target.country.code +')</a>' );
            eezPopup.find('.area .value').text(target.area);
            eezPopup.find('.treaties .value').text(target.treaties);
            eezPopup.find('.pockets .value').text(target.pockets);
            eezPopup.find('.shelf .value').text(target.shelf);
            eezPopup.find('.ecs .value').text(target.ecs);
            eezPopup.find('.deposited .value').text(target.deposited);
            eezPopup.find('.date .value').text(target.date);
            eezPopup.find('.url .value').html('<a href="'+target.url+'" target="_blank">'+ target.url.substring(0, 30) +'</a>');
            eezPopup.find('.related-datasets').html(datasets_html(target));
            eezPopup.dialog( 'open' );
            
          } else if (clickedZoneId && clickedZoneId.includes('limit-')){
            target = mapData.limits[clickedZoneId];
            limitPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'" target="_blank">' + target.country.name +' ('+target.country.code +')</a>' );
            limitPopup.find('.deposited .value').text(target.deposited);
            limitPopup.find('.date .value').text(target.date);
            limitPopup.find('.url .value').html('<a href="'+target.url+'" target="_blank">'+target.url.substring(0, 30)+'</a>');
            limitPopup.find('.related-datasets').html(datasets_html(target));
            limitPopup.dialog( 'open' );
            
          } else if (clickedZoneId && clickedZoneId.includes('shelf-')){
            target = mapData.shelf[clickedZoneId];
            shelfPopup.find('.name .value').text(target.name);
            shelfPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'" target="_blank">' + target.country.name +' ('+target.country.code +')</a>' );
            shelfPopup.find('.submission-done .value').text(target.submission_done);
            shelfPopup.find('.submission-complied .value').text(target.submission_complied);
            shelfPopup.find('.defence-year .value').text(target.defence_year);
            shelfPopup.find('.established-year .value').text(target.established_year);
            shelfPopup.find('.joint-submission .value').text(target.joint_submission);
            shelfPopup.find('.recommendation .value').text(target.recommendation); 
            shelfPopup.find('.date .value').text(target.date);
            shelfPopup.find('.related-datasets').html(datasets_html(target));
            shelfPopup.dialog( 'open' );
            
          }  else if (clickedZoneId && clickedZoneId.includes('boundary-')){
            target = mapData.boundary[clickedZoneId];
            boundaryPopup.find('.country .value .one').html('<img src="'+target.country_one.flag+'"><a href="'+target.country_one.url+'" target="_blank">' + target.country_one.name +' ('+target.country_one.code +')</a>' );;
            boundaryPopup.find('.country .value .two').html('<img src="'+target.country_two.flag+'"><a href="'+target.country_two.url+'" target="_blank">' + target.country_two.name +' ('+target.country_two.code +')</a>' );;
            boundaryPopup.find('.signed .value').text(target.signed);
            boundaryPopup.find('.year-signed .value').text(target.year_signed);
            boundaryPopup.find('.force .value').text(target.force); 
            boundaryPopup.find('.date .value').text(target.date);
            boundaryPopup.find('.url .value').html('<a href="'+target.url+'" target="_blank">'+target.url.substring(0, 30)+'</a>');
            boundaryPopup.find('.related-datasets').html(datasets_html(target));          
            boundaryPopup.dialog( 'open' );
          }     
          
          console.log(target);
          //iframe.postMessage({interactiveLayer: true, type: 'zone.hide', id: clickedZoneId}, origin);
        }
        
        function datasets_html(target){
          let html = '';
          if (target.hasOwnProperty('datasets')){

            target.datasets.forEach(function(dataset, key){
              html += '<div data-key="'+ key +'" class="dataset">';
                html += '<div class="dataset-title">';
                  html += '<a class="title" href="'+ dataset.url +'" target="_blank" title="'+ dataset.title +'">'+ dataset.title +'</a>';
                html += '</div>';
                html += '<div class="dataset-org">';
                  html += '<span>Organization:</span>';
                  html += '<div class="tooltip">'+ dataset.organization.title +'</div>';
                  html += '<a href="'+ dataset.organization.url +'" target="_blank" title="'+ dataset.organization.title +'">';
                      html += '<img src="'+dataset.organization.img +'" alt="'+dataset.organization.title +'">';
                  html += '</a>';
                html += '</div>';
                html += '<div class="metadata clearfix">';
                    html += '<div class="dataset-formats right">';
                    if (dataset.resources){
                      dataset.resources.forEach(function(resource, key){
                          html += '<a href="'+ dataset.url +'" target="_blank" aria-label="'+ resource.format.toLowerCase() +'">'
                              html += '<div class="res-formats res-format-'+ resource.format.toLowerCase() +'"></div>'
                          html += '</a>';
                      });
                    }
                  html += '</div>';
                  html += '<div class="dataset-date left">';
                      html += '<a class="view-dataset-btn" href="'+ dataset.url +'" target="_blank" >View dataset</a>';
                  html += '</div>';
                html += '</div>';
              html+= '</div>';
            });
          } else {
            html += '<p>No related datasets.</p>';
          }  
          
          return html;
        }
        
        $('#legend-popup').dialog({
          modal: true,
          autoOpen: false,
          draggable: false,
          resizable: false,
          width: 300,
          dialogClass: "legend-popup",
          open: function(event, ui){ 
            $('.ui-widget-overlay').bind('click', function(){ 
                $('#legend-popup').dialog('close'); 
            }); 
          }
        });
        
        $('#legend-button').on('click', function(){
          $('#legend-popup').parent().css({position:"fixed"}).end().dialog( 'open' );
        });

      });
    }
  };
})(jQuery, Drupal, drupalSettings);
