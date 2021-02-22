(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.spcHdb = {
    attach: function(context, settings) {
        //start context
        
        $('.more-less', context).on('click', function(){
            if ($(this).hasClass('show-more')){
                $(this).closest('.health-home-description').find('.dots').hide(); 
                $(this).closest('.health-home-description').find('.more').show();
                $(this).removeClass('show-more').addClass('show-less').text('Read less');                
            } else {
                $(this).closest('.health-home-description').find('.dots').show(); 
                $(this).closest('.health-home-description').find('.more').hide();
                $(this).removeClass('show-less').addClass('show-more').text('Read more');                
            }

        });
        
        // Health dashboard summary chart.
        if ($('.stacked-chart-global', context).length){

            const data = drupalSettings.spc_hdb.summary_chart;
            
            if(data[0].indicator == 'Leadership and governance'){
                data[0].indicator = 'Leadership';
            }

            let dataset = d3.layout.stack()(["present", "under-development", "not-present"].map(function(item) {
              return data.map(function(d) {
                return {x: d.indicator, y: +d[item]};
              });
            }));

            function detectIEEdge() {
                var ua = window.navigator.userAgent;

                var msie = ua.indexOf('MSIE ');
                if (msie > 0) {
                    // IE 10 or older => return version number
                    return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
                }

                var trident = ua.indexOf('Trident/');
                if (trident > 0) {
                    // IE 11 => return version number
                    var rv = ua.indexOf('rv:');
                    return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
                }

                var edge = ua.indexOf('Edge/');
                if (edge > 0) {
                   // Edge => return version number
                   return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
                }

                // other browser
                return false;
            }

            function setToolText(svg, className){
                return svg
                    .append("text")
                    .attr("class", className)
                    .attr("width", 30)
                    .attr("height", 30)
                    .attr("fill", "#000")
                    .attr("text-anchor", "start")
                    .style("opacity", 0);
            }

            function setToolBox(svg){
                return svg
                    .append("rect")   
                    .attr("class", "tipbox")
                    .attr("height", 40)
                    .attr("rx", 10)
                    .attr("ry", 10)
                    .attr("fill", "#fff")
                    // .attr("filter", "url(#dropshadow)")
                    .style("opacity", 0);
            }

            const width = 900,
                  height = 400;

            // Set x, y and colors
            let x = d3.scale.ordinal()
              .domain(dataset[0].map(function(d) { return d.x; }))
              .rangeRoundBands([10, width-10], 0.02);

            let y = d3.scale.linear()
              .domain([0, d3.max(dataset, function(d) {  return d3.max(d, function(d) { return d.y0 + d.y; });  })])
              .range([height, 0]);

            let colors = [];
            const border = ['#92D050',  '#FFC000', '#FF0000' ];
            const range = ["Present", "Under Development", "Not Present"];
            const rangeWidth = [95, 170, 120];

            const isIEEdge = detectIEEdge();
            let svg = {};
            if (isIEEdge){
               svg = d3.select(".stacked-chart-global")
                .append("svg")
                .attr("viewBox", [0, 0, width, height])
                if (isIEEdge <= 11) {
                    svg.attr("height", height)
                }
                svg.append("g");

              colors = ["rgb(146,208,80)", "rgb(255,192,0)", "rgb(255,0,0)" ];
            } else {
              svg = d3.select(".stacked-chart-global")
                .append("svg")
                .attr("viewBox", [0, 0, width, height])
                .append("g");
                colors = ["#92D050", "#FFC000", "#FF0000" ];
            } 

            let defs = svg.append("defs");

            let filter = defs.append("filter")
                .attr("id", "dropshadow")

            filter.append("feGaussianBlur")
                .attr("in", "SourceAlpha")
                .attr("stdDeviation", 4)
                .attr("result", "blur");

            filter.append("feOffset")
                .attr("in", "blur")
                .attr("dx", 2)
                .attr("dy", 2)
                .attr("result", "offsetBlur");

            let feMerge = filter.append("feMerge");

            feMerge.append("feMergeNode")
                .attr("in", "offsetBlur")
            feMerge.append("feMergeNode")
                .attr("in", "SourceGraphic");  

            let tooltext = setToolText(svg, "tooltip");
            let tooltip = setToolBox(svg);

            // Define and draw axes
            let yAxis = d3.svg.axis()
              .scale(y)
              .orient("left")
              .ticks(5)
              .tickSize(-width, 0, 0)
              .tickFormat( function(d) { return d } );

            let xAxis = d3.svg.axis()
              .scale(x)
              .orient("bottom");

            svg.append("g")
              .attr("class", "y axis")
              .call(yAxis);

            svg.append("g")
              .attr("class", "x axis")
              .attr("transform", "translate(0," + height + ")")
              .style("font-size", '11')
              .call(xAxis);

            svg.selectAll(".x .domain").style("opacity", 0);

            // Create groups for each series, rects for each segment 
            let groups = svg.selectAll("g.cost")
                .data(dataset)
                .enter()
                .append("g")
                .attr("class", "cost")
                .attr('range', function(d, i) { return range[i]; })
                .attr('range-width', function(d, i) { return rangeWidth[i]; })
                .attr('stroke', function(d, i) { return border[i]; })
                .attr('def-fill', function(d, i) { return colors[i]; })
                .style("stroke-width", 1)
                .style("fill", function(d, i) { return colors[i]; });

            groups.selectAll("rect")
                .data(function(d) { return d; })
                .enter()
                .append("rect")
                .attr("rx", 5)
                .attr("ry", 5)
                .attr('value', function(d) { return d.y; })
                .attr("x", function(d) { return x(d.x)+40; })
                .attr("y", function(d) { return y(d.y0 + d.y); })
                .attr("height", function(d) { 
                    var h = y(d.y0) - y(d.y0 + d.y);
                    return h > 5 ? (y(d.y0) - y(d.y0 + d.y))-5 : 0;                        
                })
                .attr("width", 30)
                .on('mouseover', function(d){

                    document
                        .querySelector(".stacked-chart-global svg")
                        .appendChild(document.querySelector(".tipbox"));

                    document
                        .querySelector(".stacked-chart-global svg")
                        .appendChild(document.querySelector(".tooltip"));     

                    let hovRange = d3.select(this.parentNode).attr('range');
                    let hovRangeWidth = d3.select(this.parentNode).attr('range-width');
                    let hovFill = d3.select(this.parentNode).attr('stroke');
                    d3.select(this).attr('fill', hovFill);

                    tooltip.style("opacity", 1)
                        .attr("x", x(d.x)+75)
                        .attr("y", y(d.y0 + d.y))
                        .attr("width", hovRangeWidth);

                    tooltext.style("opacity", 1)      
                        .text(hovRange + ' ' + Math.round(d.y) + '%')  
                        .attr("x", x(d.x)+85)
                        .attr("y", y(d.y0 + d.y)+23);

                })
                .on('mouseout', function(d){
                    let defFill = d3.select(this.parentNode).attr('def-fill');
                    d3.select(this).attr('fill', defFill);

                    tooltip.style("opacity", 0)
                    tooltext.style("opacity", 0)
                });

            $(window).on('load resize', function(){
                let Width = $(window).width(); 
                let svgGlobe = d3.select(".stacked-chart-global svg");

                if (Width < 767){
                    svgGlobe.selectAll('.x text').attr("y", "-7");
                    svgGlobe.selectAll('.x text').attr("x", "10");
                } else {
                    svgGlobe.selectAll('.x text').attr("y", "9");
                    svgGlobe.selectAll('.x text').attr("x", "-7");
                }                    
            });    


        }        
      
        //end context
        }
    };
})(jQuery, Drupal, drupalSettings);