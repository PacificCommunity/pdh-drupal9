(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.spcMbd = {
    attach: function(context, settings) {
      window.addEventListener('message', e => {
        
        //console.log(drupalSettings.spcMbd);
        
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
        let archipelagicGeoJson = {};

        const mapData = drupalSettings.spcMbd.map;
        const countryCode = drupalSettings.spcMbd.countryCode;

        if (e.data === 'ready') {
          map = document.getElementById('terria-map');
          iframe = map.contentWindow;
          origin = map.src;
          
          window.setTimeout(function(){
            $('.globe').removeClass('loader');
          }, 50000);
          
          fetch('/sites/default/files/mbd/eez-' + countryCode + '.json')
          .then(res => res.json())
          .then((data) => {
            if (data) {
            zonesGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: zonesGeoJson}, origin);

              zonesGeoJson.forEach(function(item){
                //console.log(item.id)
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
                //console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 20000);
            }
          }); 
          
          fetch('/sites/default/files/mbd/boundaries-' + countryCode + '.json')
            .then(res => res.json())
            .then((data) => {      
              if (data) {
                borersGeoJson = data;
                window.setTimeout(function(){
                  iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: borersGeoJson}, origin);

                  borersGeoJson.forEach(function(item){
                    //console.log(item.id)
                    iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
                  });

                  iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
                }, 15000);
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
                  //console.log(item.id)
                  iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
                });
      
                iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
              }, 15000);
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
                //console.log(item.id)
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
                //console.log(item.id)
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
                //console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 23000);
            }
          });
         
          fetch('/sites/default/files/mbd/archipelagic-' + countryCode + '.json')
          .then(res => res.json())
          .then((data) => {
            if (data) {
            archipelagicGeoJson = data;
            window.setTimeout(function(){
              iframe.postMessage({interactiveLayer: true, type: 'zone.add', items: archipelagicGeoJson}, origin);

              archipelagicGeoJson.forEach(function(item){
                //console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 24000);
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
                //console.log(item.id)
                iframe.postMessage({interactiveLayer: true, type: 'zone.show', id: item.id}, origin);
              });

              iframe.postMessage({interactiveLayer: true, type: 'layer.enable'}, origin);
            }, 25000);
            }
          });
 
        }  

        let eezPopup = $('#eez-popup');
        let limitPopup = $('#limit-popup');
        let shelfPopup = $('#shelf-popup');
        let boundaryPopup = $('#boundary-popup');
        let additionalPopup = $('#additional-popup');
        
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
                additionalPopup.dialog('close');
            }); 
          }
        };

        eezPopup.dialog(dialogConfig);
        limitPopup.dialog(dialogConfig);
        shelfPopup.dialog(dialogConfig);
        boundaryPopup.dialog(dialogConfig);
        additionalPopup.dialog(dialogConfig);
        
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
              if (e.data.ids[i].includes('limit-') || e.data.ids[i].includes('boundary-')){
                clickedZoneId = e.data.ids[i];
                break;
              } else if (e.data.ids[i].includes('archipelagic-') || e.data.ids[i].includes('marine-') || e.data.ids[i].includes('contiguous-') || e.data.ids[i].includes('baseline-') || e.data.ids[i].includes('seelim-')){

                switch (true) {
                  case e.data.ids.includes('baseline-' + countryCode):
                      clickedZoneId = 'baseline-' + countryCode;
                      break;
                  case e.data.ids.includes('archipelagic-' + countryCode):
                      clickedZoneId = 'archipelagic-' + countryCode;
                      break;                      
                  case e.data.ids.includes('seelim-' + countryCode):
                      clickedZoneId = 'seelim-' + countryCode;   
                      break;
                  case e.data.ids.includes('contiguous-' + countryCode):
                      clickedZoneId = 'contiguous-' + countryCode;
                      break;
                  case e.data.ids.includes('marine-' + countryCode):
                      clickedZoneId = 'marine-' + countryCode;
                      break;                  
                } 
                
                break;
              } else if (e.data.ids[i].includes('shelf-')){
                clickedZoneId = e.data.ids[i];
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

            if (target.country.status == 1){
              eezPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'" target="_blank">' + target.country.name +' ('+target.country.code +')</a>' );
            } else {
              eezPopup.find('.country .value').html('<img src="'+target.country.flag+'"><span>' + target.country.name +' ('+target.country.code +')</span>' );
            }
            eezPopup.find('.area .value').text(target.area);
            eezPopup.find('.treaties .value').text(target.treaties);
            eezPopup.find('.pockets .value').text(target.pockets);
            eezPopup.find('.shelf .value').text(target.shelf);
            eezPopup.find('.ecs .value').text(target.ecs);
            eezPopup.find('.deposited .value').text(target.deposited);
            eezPopup.find('.date .value').text(target.date);
            
            let url = target.url;
            if (target.url && target.url.length > 30){
              let url = target.url.substring(0, 30) + '...';
            }            
            
            eezPopup.find('.url .value').html('<a href="'+target.url+'" target="_blank">'+ url +'</a>');
            eezPopup.find('.related-datasets').html(datasets_html(target));
            eezPopup.dialog( 'open' );
            
          } else if (clickedZoneId && clickedZoneId.includes('limit-')){
            target = mapData.limits[clickedZoneId];
            if (target.country.status == 1){
              limitPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'" target="_blank">' + target.country.name +' ('+target.country.code +')</a>' );
            }  else {
              limitPopup.find('.country .value').html('<img src="'+target.country.flag+'"><span>' + target.country.name +' ('+target.country.code +')</span>' );
            } 
            limitPopup.find('.deposited .value').text(target.deposited);
            limitPopup.find('.date .value').text(target.date);
            
            let url = target.url;
            if (target.url && target.url.length > 30){
              url = target.url.substring(0, 30) + '...';
            }            
            
            limitPopup.find('.url .value').html('<a href="'+target.url+'" target="_blank">'+ url +'</a>');
            limitPopup.find('.related-datasets').html(datasets_html(target));
            limitPopup.dialog( 'open' );
            
          } else if (clickedZoneId && clickedZoneId.includes('shelf-')){
            target = mapData.shelf[clickedZoneId];
            shelfPopup.find('.name .value').text(target.name);
            if (target.country.status == 1){
              shelfPopup.find('.country .value').html('<img src="'+target.country.flag+'"><a href="'+target.country.url+'" target="_blank">' + target.country.name +' ('+target.country.code +')</a>' );
            }  else {
              shelfPopup.find('.country .value').html('<img src="'+target.country.flag+'"><span>' + target.country.name +' ('+target.country.code +')</span>' );
            }
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
            
            if (target.country_one.status == 1){
              if (target.country_one.code == countryCode) {
                boundaryPopup.find('.country .value .one').html('<img src="'+target.country_one.flag+'"><a href="'+target.country_one.url+'" target="_blank">' + target.country_one.name +' ('+target.country_one.code +')</a>' );
              } else {
                boundaryPopup.find('.country .value .two').html('<img src="'+target.country_one.flag+'"><a href="'+target.country_one.url+'" target="_blank">' + target.country_one.name +' ('+target.country_one.code +')</a>' );
              }

            }  else {
              if (target.country_one.code == countryCode) {
                boundaryPopup.find('.country .value .one').html('<img src="'+target.country_one.flag+'"><span>' + target.country_one.name +' ('+target.country_one.code +')</span>' );
              } else {
                boundaryPopup.find('.country .value .two').html('<img src="'+target.country_one.flag+'"><span>' + target.country_one.name +' ('+target.country_one.code +')</span>' );
              }

            }
            if (target.country_two.status == 1){
              if (target.country_two.code == countryCode) {
                boundaryPopup.find('.country .value .one').html('<img src="'+target.country_two.flag+'"><a href="'+target.country_two.url+'" target="_blank">' + target.country_two.name +' ('+target.country_two.code +')</a>' );
              }  else {
                boundaryPopup.find('.country .value .two').html('<img src="'+target.country_two.flag+'"><a href="'+target.country_two.url+'" target="_blank">' + target.country_two.name +' ('+target.country_two.code +')</a>' );
              }
            }  else {
              if (target.country_two.code == countryCode) {
                boundaryPopup.find('.country .value .one').html('<img src="'+target.country_two.flag+'"><span>' + target.country_two.name +' ('+target.country_two.code +')</span>' );
              } else {
                boundaryPopup.find('.country .value .two').html('<img src="'+target.country_two.flag+'"><span>' + target.country_two.name +' ('+target.country_two.code +')</span>' );
              }
            }
            
            boundaryPopup.find('.signed .value').text(target.signed);
            boundaryPopup.find('.year-signed .value').text(target.year_signed);
            boundaryPopup.find('.force .value').text(target.force); 
            boundaryPopup.find('.date .value').text(target.date);
            
            let url = target.url;
            if (target.url && target.url.length > 30){
              url = target.url.substring(0, 30) + '...';
            }
            
            boundaryPopup.find('.url .value').html('<a href="'+target.url+'" target="_blank">'+ url +'</a>');
            boundaryPopup.find('.related-datasets').html(datasets_html(target));          
            boundaryPopup.dialog( 'open' );
            
          } else if (
            clickedZoneId && (
            clickedZoneId.includes('archipelagic-') || 
            clickedZoneId.includes('marine-') || 
            clickedZoneId.includes('seelim-') || 
            clickedZoneId.includes('baseline-') || 
            clickedZoneId.includes('contiguous-'))
          ){

            switch (true) {
                case clickedZoneId.includes('marine-'):
                    target = mapData.additional.marine;
                    additionalPopup.find('.title').not('.data').text('Marine Protected Area (MPA)');
                    break;
                case clickedZoneId.includes('seelim-'):
                    target = mapData.additional.seelim;
                    additionalPopup.find('.title').not('.data').text('Territorial Seas Limit (12M)');
                    break;
                case clickedZoneId.includes('baseline-'):
                    target = mapData.additional.baseline;                  
                    additionalPopup.find('.title').not('.data').text('Territorial Seas Baseline (Normal/Straight)');
                    break;
                case clickedZoneId.includes('contiguous-'):
                    target = mapData.additional.contiguous;                  
                    additionalPopup.find('.title').not('.data').text('Contiguous Zone (24M)');
                    break;
                case clickedZoneId.includes('archipelagic-'):
                    target = mapData.additional.archipelagic;                  
                    additionalPopup.find('.title').not('.data').text(' Archipelagic Baseline');
                    break;                  
            }

            let country = mapData.country;
            
            additionalPopup.find('.country .value').html('<img src="'+country.flag+'"><span>' + country.name +' ('+country.code +')</span>' );
            additionalPopup.find('.area .value').text(target.area);
            
            additionalPopup.find('.legislated .value').text(target.legislated);
            additionalPopup.find('.legislated-date .value').text(target.legislatedDate); 
            
            additionalPopup.find('.deposited .value').text(target.deposited);
            additionalPopup.find('.deposited-date .value').text(target.depositedDate);
            
            if (target.url !== null && target.url.length > 30){
              let url = target.url;
              url = target.url.substring(0, 30) + '...';
              additionalPopup.find('.url .value').html('<a href="'+target.url+'" target="_blank">'+ url +'</a>');              
            } else {
              additionalPopup.find('.url .value').html('-'); 
            }

            additionalPopup.find('.related-datasets').html(datasets_html(target));          
            additionalPopup.dialog( 'open' );
          }
          
          //console.log(target);
          //iframe.postMessage({interactiveLayer: true, type: 'zone.hide', id: clickedZoneId}, origin);
        }
        
        function datasets_html(target){
          console.log(target);
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
