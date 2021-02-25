(function ($) {
  var initialized = false;
  Drupal.behaviors.educationDashboard = {
    attach: function (context, settings) {
      if (initialized) {
        return;
      }
      initialized = true;

      //chart block description toggle.
      $(".toggle").on("click", function () {
        let active = $(this).siblings("p").hasClass("hidden");

        $(this).closest(".description").find("p").addClass("hidden");
        $(this).closest(".description").find(".arrow").removeClass("down");

        if (active) {
          $(this).siblings("p").removeClass("hidden");
          $(this).find(".arrow").toggleClass("down");
        }
      });

      //read more
      $(".more-less").on("click", function () {
        if ($(this).hasClass("show-more")) {
          $(this).siblings(".dots").hide();
          $(this).siblings(".more").show();
          $(this)
            .removeClass("show-more")
            .addClass("show-less")
            .text("Read less");
        } else {
          $(this).siblings(".dots").show();
          $(this).siblings(".more").hide();
          $(this)
            .removeClass("show-less")
            .addClass("show-more")
            .text("Read more");
        }
      });

      // Download solution
      function updateDownloadURL(id) {
        let d3svgClone = $(".chart-" + id + " svg")
          .clone()
          .appendTo("#chart-clone-" + id)
          .hide();
        let d3svg = d3.select("#chart-clone-" + id + " svg");

        d3svg.selectAll("text.sample").remove();

        let viewBox = d3svg.attr("viewBox").split(",");
        let width = viewBox[2];
        let height = viewBox[3];

        d3svg.attr("width", width).attr("height", height).attr("viewBox", null);

        if (id == 12) {
          d3svg.selectAll("text.thresh-green").attr("y", 10);
        }

        let svg = document.querySelector("#chart-clone-" + id + " svg");
        let source = svg.parentNode.innerHTML;

        let canvas;
        let image = d3
          .select("body")
          .append("img")
          .style("display", "none")
          .attr("width", width)
          .attr("height", height)
          .node();

        image.onload = function () {
          canvas = d3
            .select("body")
            .append("canvas")
            .style("display", "none")
            .attr("width", width)
            .attr("height", height)
            .node();

          let ctx = canvas.getContext("2d");
          ctx.drawImage(image, 0, 0);

          d3.selectAll([canvas, image]).remove();

          d3svg
            .attr("width", null)
            .attr("height", null)
            .attr("viewBox", [0, 0, width, height]);

          d3svgClone.remove();
        };

        image.src = "data:image/svg+xml," + encodeURIComponent(source);

        return image.src;
      }

      //Exporting chart to PDF.
      $(".education-pdf").on("click", function (e) {
        e.preventDefault();

        let chartId = $(this).attr("data-chart-id");

        var img = new Image();
        img.src = updateDownloadURL(chartId);

        //creating PDF
        let pdf = new jsPDF("p", "pt", "letter");

        toDataUrl(img.src, function (base64Img) {
          let title = $("#pdf-" + chartId + " .title-text").text();
          pdf.text(60, 60, title);

          let subTitle = $("#pdf-" + chartId + " .subtitle").text();
          pdf.setFontSize(10);
          pdf.text(60, 80, subTitle);

          pdf.addImage(base64Img, "PNG", 60, 100, 500, 250);
          pdf.setFontSize(8);
          pdf.text(60, 370, "*Sample of countries from the pacific region.");
          pdf.setFontSize(10);

          let descriptionTitle = $(
            "#pdf-" + chartId + " .definition h5"
          ).text();
          let descriptionBody = $("#pdf-" + chartId + " .definition p").text();

          let strHeight = 400;
          let blockSpasing = 25;
          let titleSpasing = 20;

          if (descriptionBody.length > 0) {
            let descriptionBodyTosize = pdf.splitTextToSize(
              descriptionBody,
              500
            );

            pdf.setFontSize(14);
            pdf.text(60, strHeight, descriptionTitle);
            strHeight += titleSpasing;

            pdf.setFontSize(10);
            pdf.setFontType("normal");
            pdf.text(60, strHeight, descriptionBodyTosize);
            strHeight += descriptionBodyTosize.length * 10;
          }

          strHeight += blockSpasing;

          let thresholdTitle = $("#pdf-" + chartId + " .threshold h5").text();
          let thresholdValue = $(
            "#pdf-" + chartId + " .threshold .values"
          ).text();
          let thresholdBody = $(
            "#pdf-" + chartId + " .threshold .description"
          ).text();

          if (thresholdValue.length > 0 || thresholdBody.length > 0) {
            let thresholdBodyTosize = pdf.splitTextToSize(thresholdBody, 500);
            let thresholdValueTosize = pdf.splitTextToSize(thresholdValue, 500);

            pdf.setFontSize(14);
            pdf.text(60, strHeight, thresholdTitle);
            strHeight += titleSpasing;

            pdf.setFontSize(10);
            pdf.setFontType("normal");

            pdf.text(60, strHeight, thresholdValueTosize);
            strHeight += thresholdValueTosize.length * 10;

            pdf.text(60, strHeight, thresholdBodyTosize);
            strHeight += thresholdBodyTosize.length * 10;
          }

          strHeight += blockSpasing;

          let rationaleTitle = $("#pdf-" + chartId + " .rationale h5").text();
          let rationaleBody = $("#pdf-" + chartId + " .rationale p").text();

          if (rationaleBody.length > 0) {
            let rationaleBodyTosize = pdf.splitTextToSize(rationaleBody, 500);

            pdf.setFontSize(14);
            pdf.text(60, strHeight, rationaleTitle);
            strHeight += titleSpasing;

            pdf.setFontSize(10);
            pdf.setFontType("normal");
            pdf.text(60, strHeight, rationaleBodyTosize);
          }

          pdf.save("chart-" + chartId + ".pdf");
        });
      });

      function toDataUrl(src, callback, outputFormat) {
        var img = new Image();

        img.crossOrigin = "Anonymous";
        img.onload = function () {
          var canvas = document.createElement("CANVAS");
          var ctx = canvas.getContext("2d");
          var dataURL;

          canvas.height = this.naturalHeight;
          canvas.width = this.naturalWidth;
          ctx.drawImage(this, 0, 0);

          dataURL = canvas.toDataURL(outputFormat);
          callback(dataURL);

          canvas = null;
        };

        img.src = src;
        if (img.complete || img.complete === undefined) {
          img.src = src;
        }
      }

      /*
       * Charts configurations and global functions
       */
      const red = "#D84774";
      const orange = "#F79663";
      const green = "#00ACB3";
      const grey = "#ccc";
      const black = "#000";
      const xAxisText = "*Sample of countries from the pacific region.";
      const xAxisTextColor = grey;
      const xAxisClass = "sample";

      function addColorsToData(data, threshold) {
        const thdGreen = threshold.dots.green;
        const thdOrange = threshold.dots.orange;
        const thdRed = threshold.dots.red;

        switch (threshold.type) {
          case "normal":
            data.map(function (item, i, arr) {
              if (item.percentage >= thdGreen) {
                item.color = green;
              } else if (
                item.percentage < thdGreen &&
                item.percentage >= thdOrange
              ) {
                item.color = orange;
              } else if (item.percentage < thdOrange) {
                item.color = red;
              }
            });
            break;
          case "revers":
            data.map(function (item, i, arr) {
              if (item.percentage <= thdGreen) {
                item.color = green;
              } else if (
                item.percentage > thdGreen &&
                item.percentage <= thdOrange
              ) {
                item.color = orange;
              } else if (item.percentage > thdOrange) {
                item.color = red;
              }
            });
            break;
          case "advanced":
            const overlap = threshold.dots.overlap;
            data.map(function (item, i, arr) {
              if (item.percentage >= thdGreen && item.percentage <= overlap) {
                item.color = green;
              } else if (
                (item.percentage < thdGreen && item.percentage >= thdOrange) ||
                item.percentage > overlap
              ) {
                item.color = orange;
              } else if (item.percentage < thdOrange) {
                item.color = red;
              }
            });
            break;
        }

        return data;
      }

      function setThreshold(svg, threshold, x, y, width, height, symbol, type) {
        const thdGreen = threshold.dots.green;
        const thdOrange = threshold.dots.orange;
        const thdRed = threshold.dots.red;

        let textX = 0;
        if (type == "gender") {
          textX = -25;
        }

        svgSetLine(svg, 30, y(thdGreen), width, y(thdGreen) + 1, green);
        svgSetText(
          svg,
          textX,
          y(thdGreen) + 4,
          thdGreen + symbol,
          green,
          "thresh-green"
        );

        if (thdGreen !== thdOrange) {
          svgSetLine(svg, 30, y(thdOrange), width, y(thdOrange) + 1, orange);
          svgSetText(
            svg,
            textX,
            y(thdOrange) + 4,
            thdOrange + symbol,
            orange,
            "thresh-orange"
          );
        }

        if (threshold.dots.overlap) {
          svgSetLine(
            svg,
            30,
            y(threshold.dots.overlap),
            width,
            y(threshold.dots.overlap) + 1,
            orange
          );
          svgSetText(
            svg,
            textX,
            y(threshold.dots.overlap) + 4,
            threshold.dots.overlap + symbol,
            orange,
            "thresh-overlap"
          );
        }

        svgSetText(svg, textX, height, "0", grey, "thresh-zero");
      }

      function setNegativeThreshold(
        svg,
        threshold,
        x,
        y,
        width,
        height,
        symbol
      ) {
        svgSetLine(svg, 30, 190, 580, 191, green);
        svgSetText(svg, 0, 194, "1%", green);

        svgSetLine(svg, 30, 200, 580, 201, grey);
        svgSetText(svg, 0, 204, "0", grey);

        svgSetLine(svg, 30, 210, 580, 211, orange);
        svgSetText(svg, 0, 214, "-1%", orange);
      }

      function setToolText(svg, className) {
        return svg
          .append("text")
          .attr("class", className)
          .attr("width", 30)
          .attr("height", 30)
          .attr("fill", "#fff")
          .attr("text-anchor", "middle")
          .style("opacity", 0);
      }

      function setToolBox(svg) {
        return svg
          .append("rect")
          .attr("class", "tipbox")
          .attr("width", 40)
          .attr("height", 40)
          .attr("rx", 10)
          .attr("ry", 10)
          .style("opacity", 0);
      }

      function appendTolltip(id) {
        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".tooltip-" + id));
      }

      function setX(width) {
        return d3.scale.ordinal().rangeRoundBands([0, width], 0.1);
      }

      function setXg() {
        return d3.scale.ordinal();
      }

      function setY(height) {
        return d3.scale.linear().range([height, 0]);
      }

      function setChartSvg(chart, width, height, className) {
        let svg = {};
        const isIE11 = !!window.MSInputMethodContext && !!document.documentMode;
        if (isIE11) {
          svg = d3
            .select(chart)
            .append("svg")
            .attr("version", 1.1)
            .attr("xmlns", "http://www.w3.org/2000/svg")
            .attr("class", className)
            .attr("height", height)
            .attr("viewBox", [0, 0, width, height])
            .append("g");
        } else {
          svg = d3
            .select(chart)
            .append("svg")
            .attr("version", 1.1)
            .attr("xmlns", "http://www.w3.org/2000/svg")
            .attr("class", className)
            .attr("viewBox", [0, 0, width, height])
            .append("g");
        }

        return svg;
      }

      function setSvgDomains(data, x, y) {
        x.domain(
          data.map(function (d) {
            return d.country;
          })
        );
        y.domain([
          0,
          d3.max(data, function (d) {
            return d.percentage;
          }),
        ]);
      }

      function setCartBars(
        svg,
        data,
        x,
        y,
        width,
        height,
        tooltip,
        tooltext,
        attrX,
        attrY,
        attrH,
        typY,
        id
      ) {
        svg
          .selectAll(".bar")
          .data(data)
          .enter()
          .append("rect")
          .attr("width", 20)
          .attr("class", "bar")
          .attr("country", function (data) {
            return data.country;
          })
          .attr("percentage", function (data) {
            return data.percentage;
          })
          .style("fill", function (data) {
            return data.color;
          })
          .attr("rx", 10)
          .attr("ry", 10)
          .attr("x", attrX)
          .attr("y", function (data) {
            return y(0);
          })
          .attr("height", function (data) {
            return height - y(0);
          })
          .on("mouseover", function (data) {
            tooltip
              .style("opacity", 1)
              .attr("width", 40)
              .attr("height", 40)
              .attr("x", x(data.country) + 10)
              .attr("y", typY(data))
              .attr("fill", data.color);

            tooltext
              .style("opacity", 1)
              .text(Number(data.percentage.toFixed(1)))
              .attr("x", x(data.country) + 30)
              .attr("y", typY(data) + 25);
          })
          .on("mouseout", function (d) {
            tooltip.style("opacity", 0);
            tooltext.style("opacity", 0);
          });

        svg
          .selectAll(".bar")
          .transition()
          .delay(function () {
            return Math.random() * 1000;
          })
          .duration(1000)
          .attr("y", function (data) {
            if (data.percentage < 0.5) {
              if (id == 3) {
                return y(1);
              }
              return y(0.5);
            }
            return y(data.percentage);
          })
          .attr("height", function (data) {
            if (data.percentage < 0.5) {
              if (id == 3) {
                return height - y(1);
              }
              return height - y(0.5);
            } else {
              return height - y(data.percentage);
            }
          });
      }

      function setCartBarsReverse(
        svg,
        data,
        x,
        y,
        width,
        height,
        tooltip,
        tooltext,
        attrX,
        attrY,
        attrH,
        typY
      ) {
        svg
          .selectAll(".bar")
          .data(data)
          .enter()
          .append("rect")
          .attr("width", 20)
          .attr("class", "bar")
          .attr("country", function (data) {
            return data.country;
          })
          .attr("percentage", function (data) {
            return data.percentage;
          })
          .style("fill", function (data) {
            return data.color;
          })
          .attr("rx", 10)
          .attr("ry", 10)
          .attr("x", attrX)
          .attr("y", 200)
          .attr("height", 0)
          .on("mouseover", function (data) {
            tooltip
              .style("opacity", 1)
              .attr("width", 40)
              .attr("height", 40)
              .attr("x", x(data.country) + 10)
              .attr("y", typY(data))
              .attr("fill", data.color);

            tooltext
              .style("opacity", 1)
              .text(Number(data.percentage.toFixed(1)))
              .attr("x", x(data.country) + 30)
              .attr("y", typY(data) + 25);
          })
          .on("mouseout", function (d) {
            tooltip.style("opacity", 0);
            tooltext.style("opacity", 0);
          });

        svg
          .selectAll(".bar")
          .transition()
          .delay(function () {
            return Math.random() * 1000;
          })
          .duration(1000)
          .attr("y", attrY)
          .attr("height", attrH);
      }

      function svgSetLine(svg, x1, y1, x2, y2, stroke) {
        svg
          .append("line")
          .attr("x1", x1)
          .attr("y1", y1)
          .attr("x2", x2)
          .attr("y2", y2)
          .attr("stroke", stroke)
          .attr("stroke-width", 1)
          .attr("stroke-dasharray", "10");
      }

      function svgSetText(svg, x, y, text, fill, className) {
        svg
          .append("text")
          .attr("x", x)
          .attr("y", y)
          .text(text)
          .attr("font-size", "12px")
          .attr("class", className)
          .attr("fill", fill);
      }

      function barSort(data, delta) {
        if (data.percentage === delta.percentage) {
          return 0;
        } else {
          return data.percentage < delta.percentage ? -1 : 1;
        }
      }

      function barSortGroup(data, delta) {
        if (data.values[0].value === delta.values[0].value) {
          return 0;
        } else {
          return data.values[0].value < delta.values[0].value ? -1 : 1;
        }
      }

      function setGenderChartDomains(
        data,
        categoriesNames,
        rateNames,
        x0,
        x1,
        y
      ) {
        x0.domain(categoriesNames);
        x1.domain(rateNames).rangeRoundBands([0, x0.rangeBand()]);
        y.domain([
          0,
          d3.max(data, function (country) {
            return d3.max(country.values, function (d) {
              return d.value;
            });
          }),
        ]);
      }

      function setGenderChartSvg(id, width, height, margin) {
        let svg = {};
        const isIE11 = !!window.MSInputMethodContext && !!document.documentMode;

        if (isIE11) {
          svg = d3
            .select(".chart-" + id)
            .append("svg")
            .attr("version", 1.1)
            .attr("xmlns", "http://www.w3.org/2000/svg")
            .attr("height", height + margin.top + margin.bottom)
            .attr("viewBox", [
              0,
              0,
              width + margin.left + margin.right,
              height + margin.top + margin.bottom,
            ])
            .append("g")
            .attr(
              "transform",
              "translate(" + margin.left + "," + margin.top + ")"
            );
        } else {
          svg = d3
            .select(".chart-" + id)
            .append("svg")
            .attr("version", 1.1)
            .attr("xmlns", "http://www.w3.org/2000/svg")
            .attr("viewBox", [
              0,
              0,
              width + margin.left + margin.right,
              height + margin.top + margin.bottom,
            ])
            .append("g")
            .attr(
              "transform",
              "translate(" + margin.left + "," + margin.top + ")"
            );
        }

        return svg;
      }

      function setSvgGenderBarData(
        svg,
        data,
        x0,
        x1,
        y,
        width,
        height,
        tooltip,
        tooltext,
        boyWrap
      ) {
        var slice = svg
          .selectAll(".slice")
          .data(data)
          .enter()
          .append("g")
          .attr("class", "group")
          .attr("x", function (d) {
            return x0(d.country);
          })
          .attr("transform", function (d) {
            return "translate(" + x0(d.country) + ",0)";
          });

        slice
          .selectAll("rect")
          .data(function (d) {
            return d.values;
          })
          .enter()
          .append("rect")
          .attr("width", 20)
          .attr("x", function (d) {
            return x1(d.rate);
          })
          .style("fill", function (d) {
            if (d.rate == "M" || d.rate == "T") {
              return setFill(d.value);
            }
            return "#fff";
          })
          .style("stroke-width", 3)
          .style("stroke", function (d) {
            return setFill(d.value);
          })
          .attr("rx", 10)
          .attr("ry", 10)
          .attr("y", function (d) {
            return y(0);
          })
          .attr("height", function (d) {
            return height - y(0);
          })
          .on("mouseover", function (d) {
            let xGroup = 0;
            d3.select(this).attr("Xgroup", function () {
              xGroup = $(this).closest(".group").attr("x");
              return xGroup;
            });

            tooltip
              .style("opacity", 1)
              .attr("x", setXdelta(xGroup, d.rate) + 40)
              .attr("y", y(d.value) - 10)
              .style("stroke-width", 3)
              .style("stroke", setFill(d.value))
              .attr("fill", setTipFill(d));

            tooltext
              .style("opacity", 1)
              .text(Number(d.value.toFixed(1)))
              .attr("x", setXdelta(xGroup, d.rate) + 60)
              .attr("y", y(d.value) + 25)
              .attr("fill", setTipTextFill(d));

            boyWrap
              .style("opacity", 1)
              .attr("x", setXdelta(xGroup, d.rate) + 55)
              .attr("y", y(d.value) - 5)
              .attr("xlink:href", showGender(d));
          })
          .on("mouseout", function (d) {
            tooltip.style("opacity", 0);
            tooltext.style("opacity", 0);
            boyWrap.style("opacity", 0);
          });

        slice
          .selectAll("rect")
          .transition()
          .delay(function (d) {
            return Math.random() * 1000;
          })
          .duration(1000)
          .attr("y", function (d) {
            if (d.value == 0) {
              return y(2);
            }
            return y(d.value);
          })
          .attr("height", function (d) {
            if (d.value == 0) {
              return height - y(2);
            }
            return height - y(d.value);
          });
      }

      //GPD expenditure chart
      if ($(".chart-1").length) {
        const id = "1";
        const chart1Thd = settings.spc_education_dashboard.threshold1;
        let chart1data = settings.spc_education_dashboard.chart1[0].data;

        chart1data = addColorsToData(chart1data, chart1Thd);

        let height = 300;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);

        setSvgDomains(chart1data, x, y);
        setThreshold(svg, chart1Thd, x, y, width, height, "%");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);

        appendTolltip(id);

        const attrX = function (d) {
          return x(d.country) + 20;
        };
        const attrY = function (d) {
          return y(d.percentage);
        };
        const attrH = function (d) {
          return y(0) - y(d.percentage);
        };
        const tipY = function (d) {
          return y(d.percentage) - 30;
        };

        setCartBars(
          svg,
          chart1data,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY
        );
        svgSetText(svg, 0, 20, "18%", green);
        svgSetText(svg, 0, 230, "3.8%", red);
      }

      //Out of School Children chart
      if ($(".chart-2").length) {
        const id = "2";
        const chart2Thd = settings.spc_education_dashboard.threshold2;
        let chart2data = settings.spc_education_dashboard.chart2[0].data;

        chart2data = addColorsToData(chart2data, chart2Thd);

        let height = 450;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);

        setSvgDomains(chart2data, x, y);
        setThreshold(svg, chart2Thd, x, y, width, height, "%");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);

        appendTolltip(id);

        const attrX = function (d) {
          return x(d.country) + 20;
        };
        const attrY = function (d) {
          return y(d.percentage);
        };
        const attrH = function (d) {
          return y(0) - y(d.percentage);
        };
        const tipY = function (d) {
          return y(d.percentage) - 30;
        };

        setCartBars(
          svg,
          chart2data,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY
        );
      }

      //Over age students chart
      if ($(".chart-3").length) {
        const id = "3";
        let chart3data = settings.spc_education_dashboard.chart3[0].data;
        const chart3Thd = settings.spc_education_dashboard.threshold3;

        chart3data = addColorsToData(chart3data, chart3Thd);

        let height = 450;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);

        setSvgDomains(chart3data, x, y);
        setThreshold(svg, chart3Thd, x, y, width, height, "%");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);

        appendTolltip(id);

        const attrX = function (d) {
          return x(d.country) + 20;
        };
        const attrY = function (d) {
          return y(d.percentage);
        };
        const attrH = function (d) {
          return y(0) - y(d.percentage);
        };
        const tipY = function (d) {
          return y(d.percentage) - 30;
        };

        setCartBars(
          svg,
          chart3data,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY,
          id
        );
      }

      //Learning outcomes
      if ($(".chart-4").length) {
        const id = "4";
        let year = 4;
        let option = "literacy";

        $("#export-chart-" + id).attr("data-chart-mode", "numeracy-four");

        let chart4yearLiteracy =
          settings.spc_education_dashboard.chart4[0].data;
        let chart4yearNumeracy =
          settings.spc_education_dashboard.chart4[1].data;
        let chart6yearLiteracy =
          settings.spc_education_dashboard.chart4[2].data;
        let chart6yearNumeracy =
          settings.spc_education_dashboard.chart4[3].data;

        chart4yearLiteracy.sort(barSort);
        chart4yearNumeracy.sort(barSort);
        chart6yearLiteracy.sort(barSort);
        chart6yearNumeracy.sort(barSort);

        const chart4Thd = settings.spc_education_dashboard.threshold4;

        chart4yearLiteracy = addColorsToData(chart4yearLiteracy, chart4Thd);
        chart4yearNumeracy = addColorsToData(chart4yearNumeracy, chart4Thd);
        chart6yearLiteracy = addColorsToData(chart6yearLiteracy, chart4Thd);
        chart6yearNumeracy = addColorsToData(chart6yearNumeracy, chart4Thd);

        let height = 400;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);
        setSvgDomains(chart4yearLiteracy, x, y);
        setNegativeThreshold(svg, chart4Thd, x, y, width, height, "%");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);
        appendTolltip(id);

        let attrX = function (chart4yearLiteracy) {
          return x(chart4yearLiteracy.country) + 20;
        };

        let attrY = function (chart4yearLiteracy) {
          let pos = 200;
          if (chart4yearLiteracy.percentage >= 0) {
            pos = y(chart4yearLiteracy.percentage) / 2;
          }
          return pos;
        };

        let attrH = function (chart4yearLiteracy) {
          if (chart4yearLiteracy.percentage < 0) {
            return (y(0) - y(chart4yearLiteracy.percentage * -1)) / 2;
          } else if (chart4yearLiteracy.percentage == 0) {
            return 6;
          }
          return (y(0) - y(chart4yearLiteracy.percentage)) / 2;
        };

        let tipY = function (chart4yearLiteracy) {
          let pos = 0;
          if (chart4yearLiteracy.percentage >= 0) {
            pos = y(chart4yearLiteracy.percentage) / 2 - 30;
          } else {
            pos = y(chart4yearLiteracy.percentage) / 2 - 10;
          }
          return pos;
        };

        setCartBarsReverse(
          svg,
          chart4yearLiteracy,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY
        );

        //enable switcher
        $.switcher("input.slider");

        $(".swch-4 .ui-switcher").on("click", function (e) {
          e.preventDefault();
          let newData = [];

          $(this)
            .closest(".switch-wrapper")
            .find(".labels span")
            .toggleClass("checked");

          if ($(this).attr("aria-checked") == "true") {
            year = 6;
            if (option == "literacy") {
              newData = chart6yearLiteracy;
              $("#export-chart-" + id).attr("data-chart-mode", "literacy-six");
            } else {
              newData = chart6yearNumeracy;
              $("#export-chart-" + id).attr("data-chart-mode", "numeracy-six");
            }
          } else {
            year = 4;
            if (option == "literacy") {
              newData = chart4yearLiteracy;
              $("#export-chart-" + id).attr("data-chart-mode", "literacy-four");
            } else {
              newData = chart4yearNumeracy;
              $("#export-chart-" + id).attr("data-chart-mode", "numeracy-four");
            }
          }

          d3.select(".chart-" + id + " svg").remove();
          svg = setChartSvg(".chart-" + id, width, height);
          setSvgDomains(newData, x, y);
          setNegativeThreshold(svg, chart4Thd, x, y, width, height, "%");

          tooltext = setToolText(svg, "tooltip-" + id);
          tooltip = setToolBox(svg);
          appendTolltip(id);

          setCartBarsReverse(
            svg,
            newData,
            x,
            y,
            width,
            height,
            tooltip,
            tooltext,
            attrX,
            attrY,
            attrH,
            tipY
          );
        });

        $(".sw-4 a").on("click", function (e) {
          e.preventDefault();
          let newData = [];

          $(this)
            .closest(".switchers")
            .find(".switcher a")
            .removeClass("checked");
          $(this).toggleClass("checked");

          if ($(this).attr("id") == "numeracy") {
            option = "numeracy";
            if (year == 6) {
              newData = chart6yearNumeracy;
              $("#export-chart-" + id).attr("data-chart-mode", "numeracy-six");
            } else {
              newData = chart4yearNumeracy;
              $("#export-chart-" + id).attr("data-chart-mode", "numeracy-four");
            }
          } else {
            option = "literacy";
            if (year == 4) {
              newData = chart4yearLiteracy;
              $("#export-chart-" + id).attr("data-chart-mode", "literacy-four");
            } else {
              newData = chart6yearLiteracy;
              $("#export-chart-" + id).attr("data-chart-mode", "literacy-six");
            }
          }

          d3.select(".chart-" + id + " svg").remove();
          svg = setChartSvg(".chart-" + id, width, height);
          setSvgDomains(newData, x, y);
          setNegativeThreshold(svg, chart4Thd, x, y, width, height, "%");

          tooltext = setToolText(svg, "tooltip-" + id);
          tooltip = setToolBox(svg);
          appendTolltip(id);

          setCartBarsReverse(
            svg,
            newData,
            x,
            y,
            width,
            height,
            tooltip,
            tooltext,
            attrX,
            attrY,
            attrH,
            tipY
          );
        });
      }

      //Net enrolment rate
      if ($(".chart-5").length) {
        const id = "5";
        let chart5ece = settings.spc_education_dashboard.chart5[0].data;
        let chart5primary = settings.spc_education_dashboard.chart5[1].data;
        let chart5secondary = settings.spc_education_dashboard.chart5[2].data;
        const chart5Thd = settings.spc_education_dashboard.threshold5;

        $("#export-chart-" + id).attr("data-chart-mode", "ece");

        chart5ece.sort(barSort);
        chart5primary.sort(barSort);
        chart5secondary.sort(barSort);

        chart5ece = addColorsToData(chart5ece, chart5Thd);
        chart5primary = addColorsToData(chart5primary, chart5Thd);
        chart5secondary = addColorsToData(chart5secondary, chart5Thd);

        let height = 450;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);
        setSvgDomains(chart5ece, x, y);
        setThreshold(svg, chart5Thd, x, y, width, height, "%");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);
        appendTolltip(id);

        const attrX = function (d) {
          return x(d.country) + 20;
        };
        const attrY = function (d) {
          return y(d.percentage);
        };
        const attrH = function (d) {
          return y(0) - y(d.percentage);
        };
        const tipY = function (d) {
          return y(d.percentage) - 30;
        };

        setCartBars(
          svg,
          chart5ece,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY
        );

        $(".sw-5 a").on("click", function (e) {
          e.preventDefault();
          let newData = [];

          $(this)
            .closest(".switchers")
            .find(".switcher a")
            .removeClass("checked");
          $(this).toggleClass("checked");

          if ($(this).attr("id") == "secondary") {
            newData = chart5secondary;
            $("#export-chart-" + id).attr("data-chart-mode", "secondary");
          } else if ($(this).attr("id") == "primary") {
            newData = chart5primary;
            $("#export-chart-" + id).attr("data-chart-mode", "primary");
          } else if ($(this).attr("id") == "ece") {
            newData = chart5ece;
            $("#export-chart-" + id).attr("data-chart-mode", "ece");
          }

          d3.select(".chart-" + id + " svg").remove();
          svg = setChartSvg(".chart-" + id, width, height);
          setSvgDomains(newData, x, y);
          setThreshold(svg, chart5Thd, x, y, width, height, "%");

          tooltext = setToolText(svg, "tooltip-" + id);
          tooltip = setToolBox(svg);
          appendTolltip(id);

          setCartBars(
            svg,
            newData,
            x,
            y,
            width,
            height,
            tooltip,
            tooltext,
            attrX,
            attrY,
            attrH,
            tipY
          );
        });
      }

      //Gross enrolment ratio
      if ($(".chart-6").length) {
        const id = "6";
        let chart6ece = settings.spc_education_dashboard.chart6[0].data;
        let chart6primary = settings.spc_education_dashboard.chart6[1].data;
        let chart6secondary = settings.spc_education_dashboard.chart6[2].data;
        const chart6Thd = settings.spc_education_dashboard.threshold6;

        $("#export-chart-" + id).attr("data-chart-mode", "ece");

        chart6ece.sort(barSort);
        chart6primary.sort(barSort);
        chart6secondary.sort(barSort);

        chart6ece = addColorsToData(chart6ece, chart6Thd);
        chart6primary = addColorsToData(chart6primary, chart6Thd);
        chart6secondary = addColorsToData(chart6secondary, chart6Thd);

        let height = 450;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);
        setSvgDomains(chart6ece, x, y);
        setThreshold(svg, chart6Thd, x, y, width, height, "%");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);
        appendTolltip(id);

        const attrX = function (d) {
          return x(d.country) + 20;
        };
        const attrY = function (d) {
          return y(d.percentage);
        };
        const attrH = function (d) {
          return y(0) - y(d.percentage);
        };
        const tipY = function (d) {
          return y(d.percentage) - 30;
        };

        setCartBars(
          svg,
          chart6ece,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY
        );

        $(".sw-6 a").on("click", function (e) {
          e.preventDefault();
          let newData = [];

          $(this)
            .closest(".switchers")
            .find(".switcher a")
            .removeClass("checked");
          $(this).toggleClass("checked");

          if ($(this).attr("id") == "secondary") {
            newData = chart6secondary;
            $("#export-chart-" + id).attr("data-chart-mode", "secondary");
          } else if ($(this).attr("id") == "primary") {
            newData = chart6primary;
            $("#export-chart-" + id).attr("data-chart-mode", "primary");
          } else if ($(this).attr("id") == "ece") {
            newData = chart6ece;
            $("#export-chart-" + id).attr("data-chart-mode", "ece");
          }

          d3.select(".chart-" + id + " svg").remove();
          svg = setChartSvg(".chart-" + id, width, height);
          setSvgDomains(newData, x, y);
          setThreshold(svg, chart6Thd, x, y, width, height, "%");

          tooltext = setToolText(svg, "tooltip-" + id);
          tooltip = setToolBox(svg);
          appendTolltip(id);

          setCartBars(
            svg,
            newData,
            x,
            y,
            width,
            height,
            tooltip,
            tooltext,
            attrX,
            attrY,
            attrH,
            tipY
          );
        });
      }

      //Primary completion rate
      if ($(".chart-7").length) {
        const id = "7";

        let data = settings.spc_education_dashboard.chart7[0].data;
        data.sort(barSortGroup);

        const chart7Thd = settings.spc_education_dashboard.threshold7;
        const threshold = chart7Thd.dots;

        let height = 400;
        let width = 800;

        const margin = {
          top: 20,
          right: 20,
          bottom: 30,
          left: 40,
        };

        let x0 = setX(width);
        let x1 = setXg();
        let y = setY(height);

        let svg = setGenderChartSvg(id, width, height, margin);

        var categoriesNames = data.map(function (d) {
          return d.country;
        });
        var rateNames = data[0].values.map(function (d) {
          return d.rate;
        });

        setGenderChartDomains(data, categoriesNames, rateNames, x0, x1, y);

        svg
          .select(".y")
          .transition()
          .duration(500)
          .delay(1300)
          .style("opacity", "1");

        let tooltext = setToolText(svg, "tooltip-" + id);

        let tooltip = svg
          .append("rect")
          .attr("class", "tipbox-" + id)
          .attr("width", 40)
          .attr("height", 40)
          .attr("rx", 10)
          .attr("ry", 10)
          .style("opacity", 0);

        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".tipbox-" + id));

        let boyWrap = svg
          .append("image")
          .style("opacity", 0)
          .attr("class", "gimage-" + id)
          .attr("width", 12)
          .attr("height", 16);

        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".gimage-" + id));

        function setFill(percentage) {
          let fill = "";
          if (
            percentage >= threshold.green &&
            percentage <= threshold.overlap
          ) {
            fill = green;
          } else if (
            (percentage < threshold.green && percentage >= threshold.orange) ||
            percentage > threshold.overlap
          ) {
            fill = orange;
          } else if (percentage < threshold.orange) {
            fill = red;
          }
          return fill;
        }

        function setTipFill(data) {
          let fill = "#fff";
          if (data.rate == "M") {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              fill = orange;
            } else if (data.value < threshold.orange) {
              fill = red;
            }
          }
          return fill;
        }

        function setTipTextFill(data) {
          let fill = "#fff";
          if (data.rate == "F") {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              fill = orange;
            } else if (data.value < threshold.orange) {
              fill = red;
            }
          }
          return fill;
        }

        function setXdelta(xGroup, rate) {
          if (rate == "M") {
            return xGroup - 9;
          } else {
            return parseInt(xGroup, 10) + 23;
          }
        }

        function showGender(data) {
          let path = "/modules/custom/spc_education_dashboard/img/";
          if (data.rate == "M") {
            path = path + "boys.png";
          } else {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              path = path + "girls_green.png";
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              path = path + "girls_orange.png";
            } else if (data.value < threshold.orange) {
              path = path + "girls_red.png";
            }
          }
          return path;
        }

        appendTolltip(id);

        setThreshold(svg, chart7Thd, x0, y, width, height, "%", "gender");

        var slice = svg
          .selectAll(".slice")
          .data(data)
          .enter()
          .append("g")
          .attr("class", "group")
          .attr("x", function (d) {
            return x0(d.country);
          })
          .attr("transform", function (d) {
            return "translate(" + x0(d.country) + ",0)";
          });

        slice
          .selectAll("rect")
          .data(function (d) {
            return d.values;
          })
          .enter()
          .append("rect")
          .attr("width", 20)
          .attr("x", function (d) {
            return x1(d.rate);
          })
          .style("fill", function (d) {
            if (d.rate == "M") {
              return setFill(d.value);
            }
            return "#fff";
          })
          .style("stroke-width", 3)
          .style("stroke", function (d) {
            return setFill(d.value);
          })
          .attr("rx", 10)
          .attr("ry", 10)
          .attr("y", function (d) {
            return y(0);
          })
          .attr("height", function (d) {
            return height - y(0);
          })
          .on("mouseover", function (d) {
            let xGroup = 0;
            d3.select(this).attr("Xgroup", function () {
              xGroup = $(this).closest(".group").attr("x");
              return xGroup;
            });

            tooltip
              .style("opacity", 1)
              .attr("x", setXdelta(xGroup, d.rate) + 40)
              .attr("y", y(d.value) - 10)
              .style("stroke-width", 3)
              .style("stroke", setFill(d.value))
              .attr("fill", setTipFill(d));

            tooltext
              .style("opacity", 1)
              .text(Number(d.value.toFixed(1)))
              .attr("x", setXdelta(xGroup, d.rate) + 60)
              .attr("y", y(d.value) + 25)
              .attr("fill", setTipTextFill(d));

            boyWrap
              .style("opacity", 1)
              .attr("x", setXdelta(xGroup, d.rate) + 55)
              .attr("y", y(d.value) - 5)
              .attr("xlink:href", showGender(d));
          })
          .on("mouseout", function (d) {
            tooltip.style("opacity", 0);
            tooltext.style("opacity", 0);
            boyWrap.style("opacity", 0);
          });

        slice
          .selectAll("rect")
          .transition()
          .delay(function (d) {
            return Math.random() * 1000;
          })
          .duration(1000)
          .attr("y", function (d) {
            return y(d.value);
          })
          .attr("height", function (d) {
            return height - y(d.value);
          });
      }

      //Progression to secondary school
      if ($(".chart-8").length) {
        const id = "8";

        let data = settings.spc_education_dashboard.chart8[0].data;
        data.sort(barSortGroup);

        const chart8Thd = settings.spc_education_dashboard.threshold8;
        const threshold = chart8Thd.dots;

        let height = 300;
        let width = 500;

        const margin = {
          top: 20,
          right: 20,
          bottom: 30,
          left: 40,
        };

        let x0 = setX(width);
        let x1 = setXg();
        let y = setY(height);

        let svg = setGenderChartSvg(id, width, height, margin);

        var categoriesNames = data.map(function (d) {
          return d.country;
        });
        var rateNames = data[0].values.map(function (d) {
          return d.rate;
        });

        setGenderChartDomains(data, categoriesNames, rateNames, x0, x1, y);

        svg
          .select(".y")
          .transition()
          .duration(500)
          .delay(1300)
          .style("opacity", "1");
        let tooltext = setToolText(svg, "tooltip-" + id);

        let tooltip = svg
          .append("rect")
          .attr("class", "tipbox-" + id)
          .attr("width", 40)
          .attr("height", 40)
          .attr("rx", 10)
          .attr("ry", 10)
          .style("opacity", 0);

        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".tipbox-" + id));

        let boyWrap = svg
          .append("image")
          .style("opacity", 0)
          .attr("class", "gimage-" + id)
          .attr("width", 12)
          .attr("height", 16);

        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".gimage-" + id));

        function setFill(percentage) {
          let fill = green;
          if (
            percentage >= threshold.green &&
            percentage <= threshold.overlap
          ) {
            fill = green;
          } else if (
            (percentage < threshold.green && percentage >= threshold.orange) ||
            percentage > threshold.overlap
          ) {
            fill = orange;
          } else if (percentage < threshold.orange) {
            fill = red;
          }
          return fill;
        }

        function setTipFill(data) {
          let fill = "#fff";
          if (data.rate == "M") {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              fill = orange;
            } else if (data.value < threshold.orange) {
              fill = red;
            }
          }
          return fill;
        }

        function setTipTextFill(data) {
          let fill = "#fff";
          if (data.rate == "F") {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              fill = orange;
            } else if (data.value < threshold.orange) {
              fill = red;
            }
          }
          return fill;
        }

        function setXdelta(xGroup, rate) {
          if (rate == "M") {
            return xGroup - 9;
          } else {
            return parseInt(xGroup, 10) + 23;
          }
        }

        function showGender(data) {
          let path = "/modules/custom/spc_education_dashboard/img/";
          if (data.rate == "M") {
            path = path + "boys.png";
          } else {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              path = path + "girls_green.png";
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              path = path + "girls_orange.png";
            } else if (data.value < threshold.orange) {
              path = path + "girls_red.png";
            }
          }
          return path;
        }

        appendTolltip(id);
        setThreshold(svg, chart8Thd, x0, y, width, height, "%", "gender");

        var slice = svg
          .selectAll(".slice")
          .data(data)
          .enter()
          .append("g")
          .attr("class", "group")
          .attr("x", function (d) {
            return x0(d.country);
          })
          .attr("transform", function (d) {
            return "translate(" + x0(d.country) + ",0)";
          });

        slice
          .selectAll("rect")
          .data(function (d) {
            return d.values;
          })
          .enter()
          .append("rect")
          .attr("width", 20)
          .attr("x", function (d) {
            return x1(d.rate);
          })
          .style("fill", function (d) {
            if (d.rate == "M" || d.rate == "T") {
              return setFill(d.value);
            }
            return "#fff";
          })
          .style("stroke-width", 3)
          .style("stroke", function (d) {
            return setFill(d.value);
          })
          .attr("rx", 10)
          .attr("ry", 10)
          .attr("y", function (d) {
            return y(0);
          })
          .attr("height", function (d) {
            return height - y(0);
          })
          .on("mouseover", function (d) {
            let xGroup = 0;
            d3.select(this).attr("Xgroup", function () {
              xGroup = $(this).closest(".group").attr("x");
              return xGroup;
            });

            tooltip
              .style("opacity", 1)
              .attr("x", setXdelta(xGroup, d.rate) + 40)
              .attr("y", y(d.value) - 10)
              .style("stroke-width", 3)
              .style("stroke", setFill(d.value))
              .attr("fill", setTipFill(d));

            tooltext
              .style("opacity", 1)
              .text(Number(d.value.toFixed(1)))
              .attr("x", setXdelta(xGroup, d.rate) + 60)
              .attr("y", y(d.value) + 25)
              .attr("fill", setTipTextFill(d));

            boyWrap
              .style("opacity", 1)
              .attr("x", setXdelta(xGroup, d.rate) + 55)
              .attr("y", y(d.value) - 5)
              .attr("xlink:href", showGender(d));
          })
          .on("mouseout", function (d) {
            tooltip.style("opacity", 0);
            tooltext.style("opacity", 0);
            boyWrap.style("opacity", 0);
          });

        slice
          .selectAll("rect")
          .transition()
          .delay(function (d) {
            return Math.random() * 1000;
          })
          .duration(1000)
          .attr("y", function (d) {
            return y(d.value);
          })
          .attr("height", function (d) {
            return height - y(d.value);
          });

        svgSetText(svg, -20, height + 30, xAxisText, xAxisTextColor);
      }

      //Transition rate
      if ($(".chart-9").length) {
        const id = "9";
        let chart9data = settings.spc_education_dashboard.chart9[0].data;
        const chart9Thd = settings.spc_education_dashboard.threshold9;

        chart9data.sort(barSort);
        chart9data = addColorsToData(chart9data, chart9Thd);

        let height = 300;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);
        setSvgDomains(chart9data, x, y);
        setThreshold(svg, chart9Thd, x, y, width, height, "%");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);
        appendTolltip(id);

        const attrX = function (d) {
          return x(d.country) + 20;
        };
        const attrY = function (d) {
          return y(d.percentage);
        };
        const attrH = function (d) {
          return y(0) - y(d.percentage);
        };
        const tipY = function (d) {
          return y(d.percentage) - 30;
        };

        setCartBars(
          svg,
          chart9data,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY
        );
      }

      // Lower secondary completion rate
      if ($(".chart-10").length) {
        const id = "10";

        let data = settings.spc_education_dashboard.chart10[0].data;
        data.sort(barSortGroup);

        const chart10Thd = settings.spc_education_dashboard.threshold10;
        const threshold = chart10Thd.dots;

        let height = 400;
        let width = 800;

        const margin = {
          top: 20,
          right: 20,
          bottom: 30,
          left: 40,
        };

        let x0 = setX(width);
        let x1 = setXg();
        let y = setY(height);

        let svg = setGenderChartSvg(id, width, height, margin);

        var categoriesNames = data.map(function (d) {
          return d.country;
        });
        var rateNames = data[0].values.map(function (d) {
          return d.rate;
        });

        setGenderChartDomains(data, categoriesNames, rateNames, x0, x1, y);

        svg
          .select(".y")
          .transition()
          .duration(500)
          .delay(1300)
          .style("opacity", "1");
        let tooltext = setToolText(svg, "tooltip-" + id);

        let tooltip = svg
          .append("rect")
          .attr("class", "tipbox-" + id)
          .attr("width", 40)
          .attr("height", 40)
          .attr("rx", 10)
          .attr("ry", 10)
          .style("opacity", 0);

        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".tipbox-" + id));

        let boyWrap = svg
          .append("image")
          .style("opacity", 0)
          .attr("class", "gimage-" + id)
          .attr("width", 12)
          .attr("height", 16);

        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".gimage-" + id));

        function setFill(percentage) {
          let fill = green;
          if (
            percentage >= threshold.green &&
            percentage <= threshold.overlap
          ) {
            fill = green;
          } else if (
            (percentage < threshold.green && percentage >= threshold.orange) ||
            percentage > threshold.overlap
          ) {
            fill = orange;
          } else if (percentage < threshold.orange) {
            fill = red;
          }
          return fill;
        }

        function setTipFill(data) {
          let fill = "#fff";
          if (data.rate == "M") {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              fill = orange;
            } else if (data.value < threshold.orange) {
              fill = red;
            }
          }
          return fill;
        }

        function setTipTextFill(data) {
          let fill = "#fff";
          if (data.rate == "F") {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              fill = orange;
            } else if (data.value < threshold.orange) {
              fill = red;
            }
          }
          return fill;
        }

        function setXdelta(xGroup, rate) {
          if (rate == "M") {
            return xGroup - 9;
          } else {
            return parseInt(xGroup, 10) + 23;
          }
        }

        function showGender(data) {
          let path = "/modules/custom/spc_education_dashboard/img/";
          if (data.rate == "M") {
            path = path + "boys.png";
          } else {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              path = path + "girls_green.png";
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              path = path + "girls_orange.png";
            } else if (data.value < threshold.orange) {
              path = path + "girls_red.png";
            }
          }
          return path;
        }

        appendTolltip(id);
        setThreshold(svg, chart10Thd, x0, y, width, height, "%", "gender");

        var slice = svg
          .selectAll(".slice")
          .data(data)
          .enter()
          .append("g")
          .attr("class", "group")
          .attr("x", function (d) {
            return x0(d.country);
          })
          .attr("transform", function (d) {
            return "translate(" + x0(d.country) + ",0)";
          });

        slice
          .selectAll("rect")
          .data(function (d) {
            return d.values;
          })
          .enter()
          .append("rect")
          .attr("width", 20)
          .attr("x", function (d) {
            return x1(d.rate);
          })
          .style("fill", function (d) {
            if (d.rate == "M" || d.rate == "T") {
              return setFill(d.value);
            }
            return "#fff";
          })
          .style("stroke-width", 3)
          .style("stroke", function (d) {
            return setFill(d.value);
          })
          .attr("rx", 10)
          .attr("ry", 10)
          .attr("y", function (d) {
            return y(0);
          })
          .attr("height", function (d) {
            return height - y(0);
          })
          .on("mouseover", function (d) {
            let xGroup = 0;
            d3.select(this).attr("Xgroup", function () {
              xGroup = $(this).closest(".group").attr("x");
              return xGroup;
            });

            tooltip
              .style("opacity", 1)
              .attr("x", setXdelta(xGroup, d.rate) + 40)
              .attr("y", y(d.value) - 10)
              .style("stroke-width", 3)
              .style("stroke", setFill(d.value))
              .attr("fill", setTipFill(d));

            tooltext
              .style("opacity", 1)
              .text(Number(d.value.toFixed(1)))
              .attr("x", setXdelta(xGroup, d.rate) + 60)
              .attr("y", y(d.value) + 25)
              .attr("fill", setTipTextFill(d));

            boyWrap
              .style("opacity", 1)
              .attr("x", setXdelta(xGroup, d.rate) + 55)
              .attr("y", y(d.value) - 5)
              .attr("xlink:href", showGender(d));
          })
          .on("mouseout", function (d) {
            tooltip.style("opacity", 0);
            tooltext.style("opacity", 0);
            boyWrap.style("opacity", 0);
          });

        slice
          .selectAll("rect")
          .transition()
          .delay(function (d) {
            return Math.random() * 1000;
          })
          .duration(1000)
          .attr("y", function (d) {
            return y(d.value);
          })
          .attr("height", function (d) {
            return height - y(d.value);
          });
      }

      //Pupil-teacher ratio (PTR)
      if ($(".chart-11").length) {
        const id = "11";
        let chart11ece = settings.spc_education_dashboard.chart11[0].data;
        let chart11primary = settings.spc_education_dashboard.chart11[1].data;
        let chart11secondary = settings.spc_education_dashboard.chart11[2].data;

        $("#export-chart-" + id).attr("data-chart-mode", "ece");

        chart11ece.sort(barSort).reverse();
        chart11primary.sort(barSort).reverse();
        chart11secondary.sort(barSort).reverse();

        const threshold11Ece = settings.spc_education_dashboard.threshold11.ece;
        const threshold11Primary =
          settings.spc_education_dashboard.threshold11.primary;
        const threshold11Secondary =
          settings.spc_education_dashboard.threshold11.secondary;

        chart11ece = addColorsToData(chart11ece, threshold11Ece);
        chart11primary = addColorsToData(chart11primary, threshold11Primary);
        chart11secondary = addColorsToData(
          chart11secondary,
          threshold11Secondary
        );

        let height = 450;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);
        setSvgDomains(chart11ece, x, y);
        setThreshold(svg, threshold11Ece, x, y, width, height, "");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);
        appendTolltip(id);

        const attrX = function (d) {
          return x(d.country) + 20;
        };
        const attrY = function (d) {
          return y(d.percentage);
        };
        const attrH = function (d) {
          return y(0) - y(d.percentage);
        };
        const tipY = function (d) {
          return y(d.percentage) - 30;
        };

        setCartBars(
          svg,
          chart11ece,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY
        );

        $(".sw-11 a").on("click", function (e) {
          e.preventDefault();
          let newData = [];
          let newThreshold = [];

          $(this)
            .closest(".switchers")
            .find(".switcher a")
            .removeClass("checked");
          $(this).toggleClass("checked");

          if ($(this).attr("id") == "secondary") {
            newData = chart11secondary;
            newThreshold = threshold11Secondary;
            $("#export-chart-" + id).attr("data-chart-mode", "secondary");
          } else if ($(this).attr("id") == "primary") {
            newData = chart11primary;
            newThreshold = threshold11Primary;
            $("#export-chart-" + id).attr("data-chart-mode", "primary");
          } else if ($(this).attr("id") == "ece") {
            newData = chart11ece;
            newThreshold = threshold11Ece;
            $("#export-chart-" + id).attr("data-chart-mode", "ece");
          }

          d3.select(".chart-" + id + " svg").remove();
          svg = setChartSvg(".chart-" + id, width, height);
          setSvgDomains(newData, x, y);
          setThreshold(svg, newThreshold, x, y, width, height, "");

          tooltext = setToolText(svg, "tooltip-" + id);
          tooltip = setToolBox(svg);
          appendTolltip(id);

          setCartBars(
            svg,
            newData,
            x,
            y,
            width,
            height,
            tooltip,
            tooltext,
            attrX,
            attrY,
            attrH,
            tipY
          );
        });
      }

      //Safe drinking water
      if ($(".chart-12").length) {
        const id = "12";
        let chart12data = settings.spc_education_dashboard.chart12[0].data;
        const threshold12 = settings.spc_education_dashboard.threshold12;

        chart12data.sort(barSort);
        chart12data = addColorsToData(chart12data, threshold12);

        let height = 300;
        let width = 600;

        let x = setX(width);
        let y = setY(height);

        let svg = setChartSvg(".chart-" + id, width, height);
        setSvgDomains(chart12data, x, y);
        setThreshold(svg, threshold12, x, y, width, height, "%");

        let tooltext = setToolText(svg, "tooltip-" + id);
        let tooltip = setToolBox(svg);
        appendTolltip(id);

        const attrX = function (d) {
          return x(d.country) + 20;
        };
        const attrY = function (d) {
          return y(d.percentage);
        };
        const attrH = function (d) {
          return y(0) - y(d.percentage);
        };
        const tipY = function (d) {
          return y(d.percentage) - 30;
        };

        setCartBars(
          svg,
          chart12data,
          x,
          y,
          width,
          height,
          tooltip,
          tooltext,
          attrX,
          attrY,
          attrH,
          tipY,
          id
        );
      }

      //Trained teachers
      if ($(".chart-13").length) {
        const id = "13";
        let chart13primary = settings.spc_education_dashboard.chart13[0].data;
        let chart13secondary = settings.spc_education_dashboard.chart13[1].data;
        const threshold13 = settings.spc_education_dashboard.threshold13;
        const threshold = threshold13.dots;

        $("#export-chart-" + id).attr("data-chart-mode", "primary");

        chart13primary.sort(barSortGroup);
        chart13secondary.sort(barSortGroup);

        chart13primary = addColorsToData(chart13primary, threshold13);
        chart13secondary = addColorsToData(chart13secondary, threshold13);

        let height = 300;
        let width = 1050;

        const margin = {
          top: 20,
          right: 20,
          bottom: 30,
          left: 40,
        };

        let x0 = setX(width);
        let x1 = setXg();
        let y = setY(height);

        let svg = setGenderChartSvg(id, width, height, margin);

        var categoriesNames = chart13primary.map(function (d) {
          return d.country;
        });
        var rateNames = chart13primary[0].values.map(function (d) {
          return d.rate;
        });

        setGenderChartDomains(
          chart13primary,
          categoriesNames,
          rateNames,
          x0,
          x1,
          y
        );

        svg
          .select(".y")
          .transition()
          .duration(500)
          .delay(1300)
          .style("opacity", "1");
        let tooltext = setToolText(svg, "tooltip-" + id);

        let tooltip = svg
          .append("rect")
          .attr("class", "tipbox-" + id)
          .attr("width", 40)
          .attr("height", 40)
          .attr("rx", 10)
          .attr("ry", 10)
          .style("opacity", 0);

        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".tipbox-" + id));

        let boyWrap = svg
          .append("image")
          .style("opacity", 0)
          .attr("class", "gimage-" + id)
          .attr("width", 12)
          .attr("height", 16);

        document
          .querySelector(".chart-" + id + " svg")
          .appendChild(document.querySelector(".gimage-" + id));

        function setFill(percentage) {
          let fill = green;
          if (
            percentage >= threshold.green &&
            percentage <= threshold.overlap
          ) {
            fill = green;
          } else if (
            (percentage < threshold.green && percentage >= threshold.orange) ||
            percentage > threshold.overlap
          ) {
            fill = orange;
          } else if (percentage < threshold.orange) {
            fill = red;
          }
          return fill;
        }

        function setTipFill(data) {
          let fill = "#fff";
          if (data.rate == "T") {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              fill = orange;
            } else if (data.value < threshold.orange) {
              fill = red;
            }
          }
          return fill;
        }

        function setTipTextFill(data) {
          let fill = "#fff";
          if (data.rate == "Q") {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              fill = orange;
            } else if (data.value < threshold.orange) {
              fill = red;
            }
          }
          return fill;
        }

        function setXdelta(xGroup, rate) {
          if (rate == "T") {
            return xGroup - 9;
          } else {
            return parseInt(xGroup, 10) + 23;
          }
        }

        function showGender(data) {
          let path = "/modules/custom/spc_education_dashboard/img/";
          // let path = "/modules/custom/spc_education_dashboard/img/";
          if (data.rate == "T") {
            path = path + "qualified.png";
          } else {
            if (
              data.value >= threshold.green &&
              data.value <= threshold.overlap
            ) {
              path = path + "trained_green.png";
            } else if (
              (data.value < threshold.green &&
                data.value >= threshold.orange) ||
              data.value > threshold.overlap
            ) {
              path = path + "trained_orange.png";
            } else if (data.value < threshold.orange) {
              path = path + "trained_red.png";
            }
          }
          return path;
        }

        appendTolltip(id);
        setThreshold(svg, threshold13, x0, y, width, height, "%", "gender");
        setSvgGenderBarData(
          svg,
          chart13primary,
          x0,
          x1,
          y,
          width,
          height,
          tooltip,
          tooltext,
          boyWrap
        );

        $(".swch-13 .ui-switcher").on("click", function (e) {
          e.preventDefault();
          let newData = [];

          $(this)
            .closest(".switch-wrapper")
            .find(".labels span")
            .toggleClass("checked");

          if ($(this).attr("aria-checked") == "true") {
            newData = chart13secondary;
            $("#export-chart-" + id).attr("data-chart-mode", "secondary");
          } else {
            newData = chart13primary;
            $("#export-chart-" + id).attr("data-chart-mode", "primary");
          }

          d3.select(".chart-" + id + " svg").remove();
          svg = setGenderChartSvg(id, width, height, margin);

          var categoriesNames = newData.map(function (d) {
            return d.country;
          });
          var rateNames = newData[0].values.map(function (d) {
            return d.rate;
          });

          setGenderChartDomains(newData, categoriesNames, rateNames, x0, x1, y);

          svg
            .select(".y")
            .transition()
            .duration(500)
            .delay(1300)
            .style("opacity", "1");
          let tooltext = setToolText(svg, "tooltip-" + id);

          let tooltip = svg
            .append("rect")
            .attr("class", "tipbox-" + id)
            .attr("width", 40)
            .attr("height", 40)
            .attr("rx", 10)
            .attr("ry", 10)
            .style("opacity", 0);

          document
            .querySelector(".chart-" + id + " svg")
            .appendChild(document.querySelector(".tipbox-" + id));

          let boyWrap = svg
            .append("image")
            .style("opacity", 0)
            .attr("class", "gimage-" + id)
            .attr("width", 12)
            .attr("height", 16);

          document
            .querySelector(".chart-" + id + " svg")
            .appendChild(document.querySelector(".gimage-" + id));

          function setFill(percentage) {
            let fill = green;
            if (
              percentage >= threshold.green &&
              percentage <= threshold.overlap
            ) {
              fill = green;
            } else if (
              (percentage < threshold.green &&
                percentage >= threshold.orange) ||
              percentage > threshold.overlap
            ) {
              fill = orange;
            } else if (percentage < threshold.orange) {
              fill = red;
            }
            return fill;
          }

          function setTipFill(data) {
            let fill = "#fff";
            if (data.rate == "T") {
              if (
                data.value >= threshold.green &&
                data.value <= threshold.overlap
              ) {
                fill = green;
              } else if (
                (data.value < threshold.green &&
                  data.value >= threshold.orange) ||
                data.value > threshold.overlap
              ) {
                fill = orange;
              } else if (data.value < threshold.orange) {
                fill = red;
              }
            }
            return fill;
          }

          function setTipTextFill(data) {
            let fill = "#fff";
            if (data.rate == "Q") {
              if (
                data.value >= threshold.green &&
                data.value <= threshold.overlap
              ) {
                fill = green;
              } else if (
                (data.value < threshold.green &&
                  data.value >= threshold.orange) ||
                data.value > threshold.overlap
              ) {
                fill = orange;
              } else if (data.value < threshold.orange) {
                fill = red;
              }
            }
            return fill;
          }

          function setXdelta(xGroup, rate) {
            if (rate == "T") {
              return xGroup - 9;
            } else {
              return parseInt(xGroup, 10) + 23;
            }
          }

          function showGender(data) {
            let path = "/modules/custom/spc_education_dashboard/img/";
            if (data.rate == "T") {
              path = path + "boys.png";
            } else {
              if (
                data.value >= threshold.green &&
                data.value <= threshold.overlap
              ) {
                path = path + "girls_green.png";
              } else if (
                (data.value < threshold.green &&
                  data.value >= threshold.orange) ||
                data.value > threshold.overlap
              ) {
                path = path + "girls_orange.png";
              } else if (data.value < threshold.orange) {
                path = path + "girls_red.png";
              }
            }
            return path;
          }

          appendTolltip(id);
          setThreshold(svg, threshold13, x0, y, width, height, "%", "gender");
          setSvgGenderBarData(
            svg,
            newData,
            x0,
            x1,
            y,
            width,
            height,
            tooltip,
            tooltext,
            boyWrap
          );
        });
      }

      //end context
    },
  };
})(jQuery);
