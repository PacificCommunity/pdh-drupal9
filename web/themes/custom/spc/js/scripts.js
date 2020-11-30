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
                  "UNUSED",
                  "None, or insufficient country data",
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
})(jQuery, Drupal, drupalSettings);
