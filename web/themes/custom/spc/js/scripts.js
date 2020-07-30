(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.spc = {
        attach: function(context, settings) {

            if ($('.spc-categories').length){

                var subcat = $('.subcategory-list');

                subcat.each(function(i, item){

                    if ($(item).find('.subcategory-item').length){
                        $(item).siblings('.category-label').find('.toggle').addClass('icon');
                    }
                });

                $('.toggle.icon', context).on('click', function(e){
                    e.preventDefault();
                    $(this).toggleClass('down');
                    $(this).closest('.category-label').siblings('.subcategory-list').toggle();
                });
            }

            //Single Country page bihaviour.
            if ($('.spc-country-data').length || $('.spc-category-data').length){
                //view all action.
                $('#view-all').on('click', function(e){
                    e.preventDefault();
                    $(this).hide();
                    $('.dataset.hidden').removeClass('hidden');
                });

                //Search bihaviour.
                $('input#dataset-search', context).on('input', function(e){
                      const self = $(this);
                      let val = self.val().toLowerCase();

                      if ($('.view-all').length){
                          $('.view-all').addClass('hidden');
                      }

                      const dataset = $('.dataset').addClass('hidden');
                      let match = false;

                      dataset.each(function(){
                          //search in title.
                          let title = $(this).find('.dataset-title .title').text().toLowerCase();
                          if (title.indexOf(val) !== -1){
                              $(this).removeClass('hidden');
                              match = true;
                          }
                          //search in org.
                          let organisation = $(this).find('.dataset-org .tooltip').text().toLowerCase();
                          if (organisation.indexOf(val) !== -1){
                              $(this).removeClass('hidden');
                              match = true;
                          }
                          //search in category.
                          let category = $(this).find('.dataset-tags .tag-item').text().toLowerCase();
                          if (category.indexOf(val) !== -1){
                              $(this).removeClass('hidden');
                              match = true;
                          }
                          //search in category.
                          let countries = $(this).find('.dataset-countries .country-item').text().toLowerCase();
                          if (countries.indexOf(val) !== -1){
                              $(this).removeClass('hidden');
                              match = true;
                          }
                      });
                      
                      if (match === true){
                          $('.empty-search').addClass('hidden');
                      } else {
                          $('.empty-search').removeClass('hidden');
                      }
                });

                $('#ckan-search-form', context).on('submit', function(e){
                    e.preventDefault();
                });
            }
            
            //Timeline     
            if ($('#timeline-svg').find('svg').length == 0){
                
                if($('#timeline-svg').hasClass('country-data')){
                    var data = drupalSettings.country.timelineChart;
                    var isCountry = true;
                } else {
                    var data = drupalSettings.timelineChart;
                }
                
                var margin = {top: 20, right: 30, bottom: 0, left: 200},
                        
                    spanH = 20,
                    
                    width = 1000 - margin.left - margin.right,
                    height = Math.ceil((data.length + 0.5) * spanH) + margin.top + margin.bottom,
                    
                    xScale = d3.scaleBand(),
                    yScale = d3.scaleBand(),

                    xAxis = d3.axisTop(xScale),
                    yAxis = d3.axisLeft(yScale),

                    minX = d3.min(data, function(d) { return d['begin']; }),
                    maxX = d3.max(data, function(d) { return d['end']; });
            
                if (data.length > 1){
                    var maxYrange = height + margin.top;
                } else {
                    var maxYrange = height - margin.top;
                }
                  

                xScale.domain(d3.range(minX, maxX+1));
                xScale.rangeRound([0, width]);
                yScale.domain(data.map(function(d){ return d.plan })).range([0, maxYrange]);

                var spanX = function(d, i) { return xScale(d['begin']) +25; },
                    spanY = function(d) { return yScale(d.plan) + 5; },
                    spanW = function(d, i) { return xScale(d['end']) - xScale(d['begin']); }
            
                var chart = d3.select('#timeline-svg')
                    .append("svg")
                    .attr("viewBox", [0, 0, width + margin.left + margin.right, height + margin.top])
                    .append('g')
                    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
            
                var chartMobile = d3.select('#timeline-mobile-svg')
                    .append("svg")
                    .attr('width', width - 600)
                    .attr('height', height + margin.top)
                    .append('g')
                    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
            
                chartMobile.append("g")
                    .attr("class", "axis axis-y")
                    .attr("fill", "#000366")
                    .call(yAxis);

                // Add x axis
                chart.append("g")
                    .attr("class", "axis axis-x")
                    .attr("fill", "#000366")
                    .call(xAxis);

                // Add y axis
                chart.append("g")
                    .attr("class", "axis axis-y")
                    .attr("fill", "#000366")
                    .call(yAxis);
            
                function setToolBox(svg){
                    return svg
                        .append("rect") 
                        .attr("class", "tipbox")
                        .attr("height", 20)
                        .attr("rx", 4)
                        .attr("ry", 4)
                        .attr("fill", "#fff")
                        .attr('stroke', '#32D0F0')
                        .style("stroke-width", 1)
                        .style("opacity", 0);
                }
                
                function setToolText(svg, className){
                    return svg
                        .append("text")
                        .attr("class", className)
                        .attr("width", 30)
                        .attr("height", 30)
                        .attr("fill", "#000366")
                        .style("font-size", "12px")
                        .attr("text-anchor", "start")
                        .style("opacity", 0);
                }
                
                function setToolCircle(svg){
                    return svg
                        .append("circle")
                        .attr("fill", "#32D0F0")
                        .attr("r", 3)
                        .attr("cx", 100)
                        .attr("cy", 100)
                        .style("opacity", 0);
                }
                               

                // Add spans
                var svg = chart.selectAll('.chart-span')
                    .data(data) 
                    .enter().append('rect')
                    .attr('fill', '#7EE1F6')
                    .attr('class', function(d) { return 'code ' + d.country; })
                    .style("cursor", "pointer")
                    .attr("rx", 5)
                    .attr("ry", 5)
                    .attr('x', spanX)
                    .attr('y', spanY)
                    .attr('width', spanW)
                    .attr('height', spanH)
                    .on('mouseover', function(d){
                        d3.select(this).attr('fill', '#32D0F0');

                        if (!isCountry){
                            tooltip.style("opacity", 1)
                                .attr("x", xScale(d['begin']) +20)
                                .attr("y", yScale(d.plan) - 20)
                                .attr("width", d.countryName.length*9+10);

                            tooltext.style("opacity", 1)
                                .text(d.countryName)  
                                .attr("x", xScale(d['begin']) +25)
                                .attr("y", yScale(d.plan) - 5);
                        }    
                    
                        toolstart.style("opacity", 1)      
                            .text(d.begin)
                            .style("font-size", "10px")
                            .attr("x", xScale(d['begin']) -5)
                            .attr("y", yScale(d.plan) +18);
                    
                        toolend.style("opacity", 1)
                            .text(d.end)  
                            .style("font-size", "10px")
                            .attr("x", xScale(d['end']) +30)
                            .attr("y", yScale(d.plan) +18);
                    
                        toolCircleStart.style("opacity", 1)
                            .attr("cx", xScale(d['begin']) +25)
                            .attr("cy", yScale(d.plan) +15);
                    
                        toolCircleEnd.style("opacity", 1)
                            .attr("cx", xScale(d['end']) +25)
                            .attr("cy", yScale(d.plan) +15);
                    })
                    .on('mouseout', function(d){
                        d3.select(this).attr('fill', '#7EE1F6');
                
                        if (!isCountry){
                            tooltip.style("opacity", 0)
                                   .attr("width", 0)
                                   .attr("x", 0)
                                   .attr("y", 0);
                    
                            tooltext.style("opacity", 0)
                                    .attr("width", 0)
                                    .attr("x", 0)
                                    .attr("y", 0);
                        }
                        
                        toolstart.style("opacity", 0)
                                 .attr("x", 0)
                                 .attr("y", 0);
                         
                        toolend.style("opacity", 0)
                                 .attr("x", 0)
                                 .attr("y", 0);
                         
                        toolCircleStart.style("opacity", 0)
                                 .attr("x", 0)
                                 .attr("y", 0);
                         
                        toolCircleEnd.style("opacity", 0)
                                 .attr("x", 0)
                                 .attr("y", 0);
                    })
                    .on('click', function(d){
                        window.open(d.url, "_blank");
                    });
                
                if (!isCountry){
                    chart.selectAll('.chart-span')
                    .data(data) 
                    .enter().append('image')
                    .attr('href', function(d) { return '/themes/custom/spc/img/flags/'+ d.country +'.svg'; })
                    .attr("rx", 5)
                    .attr("ry", 5)
                    .attr('x', function(d, i) { return xScale(d['begin']) +25; })
                    .attr('y', function(d) { return yScale(d.plan) + 7; })
                    .attr('width', 25)
                    .attr('height', 15)
                    .style("cursor", "pointer")
                    .on('mouseover', function(d){
                        
                        tooltip.style("opacity", 1)
                            .attr("x", xScale(d['begin']) +20)
                            .attr("y", yScale(d.plan) - 20)
                            .attr("width", d.countryName.length*9+10);

                        tooltext.style("opacity", 1)
                            .text(d.countryName)  
                            .attr("x", xScale(d['begin']) +25)
                            .attr("y", yScale(d.plan) - 5);
                    
                        toolstart.style("opacity", 1)      
                            .text(d.begin)
                            .style("font-size", "10px")
                            .attr("x", xScale(d['begin']) -5)
                            .attr("y", yScale(d.plan) +18);
                    
                        toolend.style("opacity", 1)
                            .text(d.end)  
                            .style("font-size", "10px")
                            .attr("x", xScale(d['end']) +30)
                            .attr("y", yScale(d.plan) +18);
                    
                        toolCircleStart.style("opacity", 1)
                            .attr("cx", xScale(d['begin']) +25)
                            .attr("cy", yScale(d.plan) +15);
                    
                        toolCircleEnd.style("opacity", 1)
                            .attr("cx", xScale(d['end']) +25)
                            .attr("cy", yScale(d.plan) +15);
                     })
                    .on('mouseout', function(d){
                        tooltip.style("opacity", 0)
                               .attr("width", 0)
                               .attr("x", 0)
                               .attr("y", 0);

                        tooltext.style("opacity", 0)
                                .attr("width", 0)
                                .attr("x", 0)
                                .attr("y", 0);
                            
                        toolstart.style("opacity", 0)
                                 .attr("x", 0)
                                 .attr("y", 0);
                         
                        toolend.style("opacity", 0)
                                 .attr("x", 0)
                                 .attr("y", 0);
                         
                        toolCircleStart.style("opacity", 0)
                                 .attr("x", 0)
                                 .attr("y", 0);
                         
                        toolCircleEnd.style("opacity", 0)
                                 .attr("x", 0)
                                 .attr("y", 0);
                    })
                    .on('click', function(d){
                        window.open(d.url, "_blank");
                    });
                }

            
                let tooltip = setToolBox(chart);
                let tooltext = setToolText(chart, "tool-text");
                let toolstart = setToolText(chart, "tool-start");
                let toolend = setToolText(chart, "tool-end");
                let toolCircleStart = setToolCircle(chart);
                let toolCircleEnd = setToolCircle(chart);
                
                d3.selectAll('.axis-y .tick')
                 .data(data)
                 .on('mouseover', function(d){
                    tooltip.style("opacity", 1)
                        .attr("x", -180)
                        .attr("y", yScale(d.plan) - 20)
                        .attr("width", d.planName.length*7+10);

                    tooltext.style("opacity", 1)
                        .text(d.planName)  
                        .attr("x", -170)
                        .attr("y", yScale(d.plan) - 5);
                 })
                 .on('mouseout', function(d){
                    tooltip.style("opacity", 0)
                           .attr("width", 0)
                           .attr("x", 0)
                           .attr("y", 0);
                    tooltext.style("opacity", 0)
                            .attr("width", 0)
                            .attr("x", 0)
                            .attr("y", 0);
                 });
            }
            
            $('.show-full', context).on('click', function(e){
                e.preventDefault();
                $(this).siblings('#timeline-svg').toggleClass('full');
                $(this).find('i').toggleClass('fa-chevron-down');
                $(this).find('i').toggleClass('fa-chevron-up');
            }); 

        }
    };
})(jQuery, Drupal, drupalSettings);

