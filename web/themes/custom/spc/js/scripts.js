(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.spc = {
        attach: function(context, settings) {

            // Secondary Datasep pages header dropdown
            $('.pane-dashboard-for-sdp .button').unbind().click(function() {
                let dropdown = $('.pane-dashboard-for-sdp .dropdown-menu');
                if (dropdown.hasClass('hidden')) {
                    dropdown.removeClass('hidden');
                    $('#block-spc-local-tasks').css('z-index', '-9999');
                }
                else {
                    dropdown.addClass('hidden');
                    $('#block-spc-local-tasks').css('z-index', '9999');
                }
            });

            //Search input box onEnter event
            $('.global-search-form #ckan-search').unbind().on('keypress',function(e) {
                if(e.which == 13 || e.which == 124) {
                    e.preventDefault();
                    window.location.href = "https://pacificdata.org/data/dataset?extras_thematic_area_string&q="+ $(this).val();
                }
            });
            
            $('#read-more-btn', context).on('click', function(e){
               e.preventDefault();
               if ($(this).siblings('.read-more').hasClass('hidden')){
                    $(this).siblings('.read-more').removeClass('hidden');
                    $(this).addClass('open').text('Read less');
                } else {
                    $(this).siblings('.read-more').addClass('hidden');
                    $(this).removeClass('open').text('Read more');
                }
            });
            
            $('.step-state .level', context).on('mouseover', function(e){
                $(this).find('.tooltip').show();
            });
            $('.step-state .level', context).on('mouseout', function(e){
                $(this).find('.tooltip').hide();
            });
            
            $('.collapsed', context).on('click', function(e){
                e.preventDefault();
                $(this).toggleClass('close')
                let collapsedId = $(this).data('id');
                $('#' + collapsedId).toggle();
            });

            //Dashboard Transform Chart script.
            if ($("#sdgChart").length && $("#sdgChart svg").length == 0){
                
                var modulePath = "/" + drupalSettings.spcMainModulePath;

                var valuesDescription = [
                  "None, or insufficient country data",
                  "Tier 3 indicator. No established methodology",
                  "No achievement against the goal",
                  "Minimal achievement",
                  "Some achievement",
                  "Average Progress",
                  "Good Progress",
                  "Goal is fully achieved"
                ];
                
                var countriesData = drupalSettings.spcMainCountriesData;

                var width = 768,
                  height = 768,
                  radius = (Math.min(width, height) / 2) - 10,
                  domainWidth = 25,
                  goalWidth = 80;

                var domains_arc = d3.arc()
                  .innerRadius(radius - domainWidth)
                  .outerRadius(radius);

                var goals_arc = d3.arc()
                  .outerRadius(radius - domainWidth)
                  .innerRadius(radius - goalWidth);

                var pie = d3.pie()
                  .value(function(d) { return d.value ? d.value : d.barsData.length; })
                  .sort(null);

                var chartBlock = d3.select("#sdgChart");

                var svg = chartBlock
                    .append("svg")
                    .attr("preserveAspectRatio", "xMinYMin meet")
                    .attr("viewBox", "-225 0 1220 800")
                    .append("g")
                    .attr("transform", "translate(" + width / 2 + "," + (height / 2) + ")");
             
                var domainsDataPath = drupalSettings.spcMainDomainsDataPath;
                d3.json(domainsDataPath, function(data) {

                  var path = svg.selectAll("path.domain")
                      .data(pie(data))
                      .enter()
                    .append("path")
                      .attr("class", "domain")
                      .attr("id", function(d,i) { return "domain_"+i; })
                      .attr("d", domains_arc)
                      .style("fill", "#4184be")
                      .style("stroke", "#fff")
                      .style("stroke-width", "5px");

                  svg.selectAll(".domain-text")
                      .data(data)
                      .enter()
                    .append("text")
                      .attr("class", "domain-text")
                      .attr("x", 15)
                      .attr("dy", 18) 
                    .append("textPath")
                      .attr("xlink:href",function(d,i){return "#domain_"+i;})
                      .text(function(d){return d.name;})
                      .style("fill", "#fff");
                });

                var goalsDataPath = drupalSettings.spcMainGoalsDataPath;
                d3.json(goalsDataPath, function(data) {

                  var select = d3.select("#sdgChartCountries");

                  select.selectAll("option")
                      .data(Object.keys(countriesData))
                      .enter()
                    .append("option")
                      .text(function (d) { return d; })
                      .each(function(d) {
                        var option = d3.select(this);
                        if (d == "Pacific Regional excl Aust and NZ") {
                          option.attr("selected", "selected");
                        }
                      });

                  $('#sdgChartCountries').select2({
                    width: '100%'
                  });

                  $("#sdgChartCountries").on("select2:select", function(e) {
                    var selectValue = jQuery("#sdgChartCountries").val();
                    var i = 0;
                    data.forEach(function(element) {
                      element.barsData.forEach(function(item) {
                        item["value"] = countriesData[selectValue][i];
                        i += 1;
                      })
                    })
                    updateBars(data);
                  });

                  var barTooltip = d3.select("body").append("div")	
                    .attr("class", "tooltip chart-tooltip")				
                    .style("opacity", 0);

                  var goalTooltip = d3.select("body").append("div")	
                    .attr("class", "tooltip chart-tooltip goals-t")				
                    .style("opacity", 0);

                  var goals = svg.selectAll(".goal")
                      .data(pie(data))
                      .enter()
                    .append("a")
                      .attr("xlink:href", function(d) {return d.data.link;})
                      .attr("target", "_self")
                      .attr("aria-label", function(d) { return "Go to " + d.data.title })
                      .on("mouseover", function(d) {		
                        goalTooltip.transition()		
                          .duration(200)		
                          .style("opacity", .9);		
                        goalTooltip.html("Go to " + d.data.title)	
                          .style("left", (d3.event.pageX) + "px")		
                          .style("top", (d3.event.pageY - 28) + "px")
                        })
                      .on("mouseout", function(d) {		
                        goalTooltip.transition()		
                          .duration(500)
                          .style("opacity", 0)
                      });

                  goals.append("path")
                    .attr("class", "goal")
                    .attr("id", function(d,i) { return "goal_"+i; })
                    .attr("d", goals_arc)
                    .style("fill", function(d) { return d.data.color; })
                    .style("stroke", "#fff")
                    .style("stroke-width", "5px");

                  goals.append("image")
                    .attr("xlink:href",function(d,i){return modulePath+d.data.icon;})
                    .attr("x", function(d){
                      return (goals_arc.centroid(d)[0] - 15);
                    })
                    .attr("y", function(d){
                      return (goals_arc.centroid(d)[1] - 15);
                    })
                    .attr("width", "30")
                    .attr("height", "30");

                  chartBlock.append("div")
                    .attr("class", "center-image")
                    .style("background", "#71a2d6 url("+modulePath+"/images/center-image.svg) no-repeat 50% 50%");

                  // Inner bar chart
                  var barHeight = 275;

                  var barsNames = [];
                  var i = 0;
                  data.forEach(function(element) {
                    element.barsData.forEach(function(item) {
                      barsNames.push(item.name);
                      item["color"] = element.color;
                      item["value"] = countriesData["Pacific Regional excl Aust and NZ"][i];
                      i += 1;
                    })
                  });

                  var numBarsData = barsNames.length;

                  var barScale = d3.scaleLinear()
                    .domain([0, 7])
                    .range([0, barHeight]);

                  var x = d3.scaleLinear()
                    .domain([0, 7])
                    .range([0, -barHeight]);

                  function countRotate(i) {
                    var coef = 360 / numBarsData;
                    data[i]['rotate'] = (i === 0 ? 0 : (data[i-1].barsData.length * coef + data[i-1]['rotate']));
                    return (i === 0 ? 0 : (data[i-1].barsData.length * coef + data[i-1]['rotate']))
                  }

                  var arc = d3.arc()
                    .startAngle(function(d,i) { return (i * 1 * Math.PI) / (numBarsData/2); })
                    .endAngle(function(d,i) { return ((i+1) * 1 * Math.PI) / (numBarsData/2); })
                    .innerRadius(75)
                    .padAngle(.01);

                  var goal = svg.selectAll(".bar")
                      .data(data)
                      .enter()
                    .append("g")
                      .attr("class", "bar")
                      .attr("transform", function(d,i) { return "rotate(" + (countRotate(i)) + ")"; });

                  var segments = goal.selectAll("path")
                      .data(function(d) { return d.barsData; })
                      .enter()
                    .append("a")
                      .attr("xlink:href", function(d) {return d.link;})
                      .attr("target", "_self")
                      .attr("aria-label", function(d) { return d.description });

                    segments.append("path")
                      .each(function(d) { d.outerRadius = 0; })
                      .attr("d", arc)
                      .on("mouseover", function(d) {		
                        barTooltip.transition()		
                          .duration(200)		
                          .style("opacity", .9);		
                        barTooltip.html(d.description + "<br><b><i>" + valuesDescription[d.value] + "</i></b>")
                          .style("left", (d3.event.pageX) + "px")
                          .style("top", (d3.event.pageY - 28) + "px")
                        })					
                      .on("mouseout", function(d) {		
                        barTooltip.transition()		
                          .duration(500)
                          .style("opacity", 0)
                      });

                  var updateBars = function(data) {
                      var goal = svg.selectAll(".bar").data(data);
                      goal.exit().remove();

                      goal.enter()
                        .append("g")
                          .attr("class", "bar")
                          .attr("transform", function(d,i) { return "rotate(" + (countRotate(i)) + ")"; });

                      var segments = goal.selectAll("path")
                          .data(function(d) { return d.barsData; });

                      segments.exit().remove();
                      segments.enter()
                        .append("a")
                          .attr("xlink:href", function(d) {return d.link;})
                          .attr("target", "_self")
                          .attr("aria-label", function(d) { return d.description });

                      segments.enter()
                        .append("path")
                          .each(function(d) { d.outerRadius = 0; })
                          .attr("d", arc)
                          .on("mouseover", function(d) {		
                            barTooltip.transition()		
                              .duration(200)
                              .style("opacity", .9);
                            barTooltip.html(d.description + "<br><b><i>" + valuesDescription[d.value] + "</i></b>")
                              .style("left", (d3.event.pageX) + "px")
                              .style("top", (d3.event.pageY - 28) + "px")
                            })					
                          .on("mouseout", function(d) {		
                            barTooltip.transition()
                              .duration(500)
                              .style("opacity", 0)
                          });

                      segments.transition().duration(1000)
                        .attrTween("d", function(d,index) {
                          var i = d3.interpolate(d.outerRadius, barScale(d.value === 0 || d.value === 1 || d.value === 2 ? 7 : d.value));
                          return function(t) { d.outerRadius = i(t); return arc(d,index); };
                        })
                        .style('fill', function (d) { return d.value === 1 ? "#e3e3e3" : (d.value === 0 ? "transparent" : (d.value === 2 ? "#fff" : d.color)); })
                        .style("stroke", function (d) {return d.value === 0 ? "#b4b5b4" : ""; })
                        .style("stroke-width", function (d) {return d.value === 0 ? "2px" : ""; })
                        .style("stroke-dasharray", function (d) {return d.value === 0 ? "2,2" : ""; });
                  }

                  updateBars(data);

                  svg.selectAll("circle")
                      .data(x.ticks(5))
                      .enter()
                  .append("circle")
                    .attr("r", function(d) {return barScale(d);})
                    .style("fill", "none")
                    .style("stroke", "black")
                    .style("stroke-dasharray", "2,2")
                    .style("stroke-width",".5px");

                  var labelRadius = barHeight * 1.025;
                  var labels = svg.append("g")
                    .classed("labels", true);

                  labels.append("def")
                    .append("path")
                    .attr("id", "label-path")
                    .attr("d", "m0 " + -labelRadius + " a" + labelRadius + " " + labelRadius + " 0 1,1 -0.01 0");

                  labels.selectAll("text")
                      .data(barsNames)
                      .enter()
                    .append("text")
                      .attr("class", "bar-label")
                      .style("text-anchor", "middle")
                      .style("fill", function(d, i) {return "#3e3e3e";})
                    .append("textPath")
                      .attr("xlink:href", "#label-path")
                      .attr("startOffset", function(d, i) {return i * 100 / numBarsData + 50 / numBarsData + '%';})
                      .text(function(d) {return d; });
                });
            }
        }
    };
    
    Drupal.behaviors.advancedSearchForm = {
      attach: function (context, settings) {
        if ($('form#ckan-search-form').length){

            const Form = $('form#ckan-search-form');
            
            Form.find('select').each(function(){
                $(this).tokenize2({
                    placeholder: 'Select ' + $(this).attr('data-title'),
                    dropdownMaxItems: 100,
                    displayNoResultsMessage: true,
                    noResultsMessageText: 'No matching options.',
                });
            });

            Form.find('.fieldset-legend').on('click', function(e){
              e.preventDefault();
              Form.find('.fieldset-wrapper').toggle();
            });

            Form.find('select').on('tokenize:select', function(container){
                if ($(this).attr('disabled') != 'disabled') {
                    $(this).tokenize2().trigger('tokenize:search', [$(this).tokenize2().input.val()]);
                }
            });
            
            Form.find('select').on('tokenize:dropdown:show', function(e){
                Form.find('.fieldset-wrapper').addClass('blured');
                $(this).closest('.filter-wrapp').append('<li class="angledown"></li>');
            });
            
            Form.find('select').on('tokenize:dropdown:hide', function(e){
                Form.find('.fieldset-wrapper').removeClass('blured');
                Form.find('.tokenize').removeClass('focus');
                $(this).closest('.filter-wrapp').find('.angledown').remove();
            });
            
            $('.angledown').on('click', function(e){
                e.preventDefault();
                if ($(this).siblings('.tokenize').hsaClass('focus')){
                    Form.find('.tokenize').removeClass('focus');
                    Form.find('select').each(function(){
                        $(this).tokenize2().trigger('tokenize:dropdown:hide');
                    });
                }
            });    

            Form.find('select').on('tokenize:dropdown:fill', function(e, items){
                let input = $(this).next().find('.token-search input');
                if(items.length == 0 && input.val().length > 0) {
                    input.val(input.val().slice(0,-1));
                    $(this).tokenize2().trigger('tokenize:search', input.val());
                }
            });
            
            Form.find('select').on('tokenize:tokens:add tokenize:tokens:remove ', function(e, items){
                Form.find('.fieldset-wrapper').addClass('blured spinner');
                
                const name = $(this).attr('data-name');
                const value = this.value;
                
                const selected = [];
                $('form#ckan-search-form select').each(function(){
                    if ($(this).val().length > 0){
                        selected[$(this).attr('data-name')] = $(this).val();
                    }
                });

                const request_url = drupalSettings.spc_home_banner.dataBaseUrl + '/data/api/action/package_search?facet.field=';
                let request_params = '["organization",+"tags",+"res_format",+"license_id",+"type",+"member_countries",+"topic"]&fq=';

                for (let select in selected){
                    request_params += '+' + select + ':' + selected[select].join(',');
                }

                const countriesMapping = drupalSettings.spc_home_banner.mappingConfig.mapping.member_countries;
                
                $.ajax({
                    url: request_url + request_params.split(" ").join("+"),
                    type: 'GET',
                    
                    success: function(data) {
                        if (data.success){
                            const search_facets = data.result.search_facets;
                            for (let facet_name in search_facets) {
                                let facet_items = search_facets[facet_name].items;
                                let facet_select = Form.find('select[data-name='+facet_name+']');
                                facet_select.empty();
                                
                                if (facet_items.length){
                                    facet_select.removeAttr('disabled');
                                    $('.filter-wrapp.' + facet_name).find('.tokens-container').removeClass('disabled');
                                    for (let option in facet_items) {
                                        if (facet_name == 'member_countries'){
                                            let CountryCode = facet_items[option].name;

                                            if(selected[facet_name] && selected[facet_name].includes(facet_items[option].name)){
                                                  facet_select.append($("<option></option>")
                                                  .attr("selected", "selected")
                                                  .attr("value", facet_items[option].name)
                                                  .text(countriesMapping[CountryCode]));
                                              } else {
                                                  facet_select.append($("<option></option>")
                                                  .attr("value", facet_items[option].name)
                                                  .text(countriesMapping[CountryCode]));
                                              }
                                        } else {
                                            if(selected[facet_name] && selected[facet_name].includes(facet_items[option].name)){
                                                facet_select.append($("<option></option>")
                                                .attr("selected", "selected")
                                                .attr("value", facet_items[option].name)
                                                .text(facet_items[option].display_name));
                                            } else {
                                                facet_select.append($("<option></option>")
                                                .attr("value", facet_items[option].name)
                                                .text(facet_items[option].display_name));
                                            }                                        
                                        }
                                    }
                                } else {
                                    if (selected[facet_name]){
                                        $('.filter-wrapp.' + facet_name).find('.tokens-container').removeClass('disabled');
                                        selected[facet_name].forEach(function(entry) {
                                            facet_select.append($("<option></option>")
                                            .attr("selected", "selected")
                                            .attr("value", entry)
                                            .text(entry));
                                        });
                                    }
                                }
                            }
                            Form.find('.fieldset-wrapper').removeClass('blured spinner');
                        }
                    },
                    
                    error: function(error) {
                      Form.find('.fieldset-wrapper').removeClass('blured spinner');
                      console.log('Error:');
                      console.log(error);
                    }
                });
            });
            
            $('.fieldset-wrapper legend.inner').on('click', function(e){
                e.preventDefault();
                $('fieldset.collapsible').addClass('collapsed');
                $('.fieldset-wrapper').hide();
            });
            
            $('a#adv-search-submit').on('click', function(e){
                e.preventDefault();
                Form.submit();
            });
            
        }
      }
    };
    
    Drupal.behaviors.homePage = {
      attach: function (context, settings) {
        
        $('.topics-list').slick({
            slidesToShow: 4,
            slidesToScroll: 4,            
            dots: true,
            infinite: false,
            responsive: [
              {
                breakpoint: 768,
                settings: {
                  slidesToShow: 1,
                  arrows: false
                }
              }
            ]
        }); 
        
        // Home page datasets switcher.
        $('#nav-recent-datasets-tab', context).on('click', function(e){
          e.preventDefault();
          $('.ckan-dataset-tabs-tab').removeClass('active');
          $(this).closest('.ckan-dataset-tabs-tab').addClass('active');
          $('#nav-popular-datasets').fadeOut();          
          $('#nav-recent-datasets').fadeIn();
        });
        $('#nav-popular-datasets-tab', context).on('click', function(e){
          e.preventDefault();
          $('.ckan-dataset-tabs-tab').removeClass('active');
          $(this).closest('.ckan-dataset-tabs-tab').addClass('active');          
          $('#nav-recent-datasets').fadeOut();
          $('#nav-popular-datasets').fadeIn();
        });
        
        $('.ckan-dataset-tab-container .carusel-of-items').slick({
          dots: false,
          infinite: true,
          speed: 600,
          arrows: false,
          slidesToShow: 1,
          slidesToScroll: 1,
          centerMode: true,
        });

        $('#nav-popular-datasets-tab').on('click', function(){
          var slide_center = $('#nav-popular-datasets .ckan-dataset-tab-container .carusel-of-items').find('.slick-center').first();
          if (slide_center.length == 1 && slide_center.width() < 0) {
            $('#nav-popular-datasets .ckan-dataset-tab-container .carusel-of-items').slick('refresh');
          }
        });
        
        //Home page stories slider.
        $('.latest-stories-slider .field-item').each(function(i) {
          let title_block = $(this).find('.views-field-title');
          if (title_block.length > 0) {
            let num = i + 1;
            title_block.prepend('<div class="slide-num">' + (num <= 9 ? '0'+num : num) + '</div>')
          }
        });        
        
        $('#latest-stories-block .stories-list').slick({
          slidesToShow: 3,
            dots: true,
            responsive: [
              {
                breakpoint: 768,
                settings: {
                  slidesToShow: 1,
                  arrows: false
                }
              }
            ]
        });

        if ($('.latest-stories-slider').length > 0 && $('.latest-stories-slider .slick-dots li').length > 0) {
          let slides_num_stories = $('.latest-stories-slider .slick-dots li').length;
          let slide = $('.latest-stories-slider .slick-dots .slick-active button').text();
          $('.latest-stories-slider').append('<div class="slide-number"><strong>'+ slide +'</strong> of <strong>' + slides_num_stories + '</strong></div>');
          $('.latest-stories-slider .slick-arrow').on('click', function(){
            slide = $('.latest-stories-slider .slick-dots .slick-active button').text();
            $('.latest-stories-slider .slide-number').html('<strong>' + slide + '</strong> of <strong>' + slides_num_stories + '</strong>');
          });
        };
        
        //Home page Dashboards toggle. 
        $('.dashboards-home .show-more a', context).on('click', function(e){
            e.preventDefault();
            $(this).toggleClass('open');
            $('.dashboards-home .collapsible').toggle();
            
            if ($(this).hasClass('open')){
                $(this).text('show less');
            } else {
                $(this).text('show more');
            }
        });
        
        //Home page Dashboards slider. 
        $('.dashboard-list').slick({
          slidesToShow: 2,
            dots: true,
            responsive: [
              {
                breakpoint: 768,
                settings: {
                  slidesToShow: 1,
                  arrows: false
                }
              }
            ]
        });
          
      }
    };
    
})(jQuery, Drupal, drupalSettings);
