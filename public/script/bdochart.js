const div = d3.select("body").append("div")
        .attr("class", "tooltip")         
        .style("opacity", 0);

    function hbarchart (mydivname, mymargin, data, getLabel, getValue, title = "Barchart") {
        const margin = mymargin;
        width = 600 - margin.left - margin.right,
        height = Math.max(data.length*20, 250) - margin.top - margin.bottom;

        const y = d3.scaleBand()
               .range([ 0, height])
               .padding(0.1);

        const x = d3.scaleLinear()
            .range([ 0, width]);
    
        const svg = d3.select("#"+mydivname).append("svg")
            .attr("id", "svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
         data.forEach(d => d.value = +getValue(d));
            // Mise en relation du scale avec les données
            y.domain(data.map(d => getLabel(d)));
            x.domain([0, d3.max(data, d => d.value)]);

            // Ajout de l'axe X au SVG
            // Déplacement de l'axe horizontal et du futur texte (via la fonction translate) au bas du SVG
            // Selection des noeuds text, positionnement puis rotation
            svg.append("g")
                .attr("transform", "translate(0," + height + ")")
                .call(d3.axisBottom(x).tickSize(0))
                .selectAll("text")	
                    .style("text-anchor", "end")
                    .attr("dx", "-.8em")
                    .attr("dy", ".15em")
                    .attr("transform", "rotate(-65)");

            // Ajout de l'axe Y au SVG avec 6 éléments de légende en utilisant la fonction ticks (sinon D3JS en place autant qu'il peut).
            svg.append("g")
                .call(d3.axisLeft(y));

           // ajout des bar
            svg.selectAll(".bar")
                .data(data)
            .enter().append("rect")
                .attr("class", "bar")
                .attr("y", d => y(getLabel(d)))
                .attr("height", y.bandwidth())
                .attr("x", x(0))
                .attr("width", d => x(d.value))
                .attr("fill", "#8F1204" )
                .attr("fill-opacity","0.5")
                //.attr("stroke", "#8F1204")
                //.attr("stroke-linecap", "round")
                //.attr("stroke-width", y.bandwidth() / 3)
                .on('mouseenter', function (actual, i) {
                    d3.select(this).attr('opacity', 0.5)
                })
                .on('mouseleave', function (actual, i) {
                    d3.select(this).attr('opacity', 1)
                })
                .on("mouseover", function(event, d) {
                    div.transition()        
                        .duration(200)      
                        .style("opacity", .9);
                    const [x, y] = d3.pointer(event);
                    div.html(getLabel(d) + "<br>" + d.value)
                        .style("left", (event.pageX) + "px")     
                        .style("top", (event.pageY) + "px");
                })
                .on("mouseout", function(d) {
                    div.transition()
                        .duration(500)
                        .style("opacity", 0);
                });
            svg.selectAll(".txthint").append("g").data(data).enter()
                        .append("text")
                        .attr("class", "txthint")
                        .attr("x",  d => (x(0) + x(d.value) + 5))
                        .attr("y", d => (y(getLabel(d)) + y.bandwidth() - 2))
                        .attr("font-size", 0.9*y.bandwidth())
                        .text(d => d.value);
            // ajout du titre
            svg.append("text")
                .attr("x", (width / 2))             
                .attr("y", 0 - (margin.top / 2))
                .attr("text-anchor", "middle")  
                .style("font-size", "16px")
                .text(title);
             
    }
    
    function vbarchart (mydivname, mymargin, data, getLabel, getValue, title = "Barchart") {
        const margin = mymargin;
        width = Math.max(data.length*20, 400) - margin.left - margin.right,
        height = 400 - margin.top - margin.bottom;

        const x = d3.scaleBand()
               .range([ 0, width])
               .padding(0.1);

        const y = d3.scaleLinear()
            .range([ height, 0]);
    
        const svg = d3.select("#"+mydivname).append("svg")
            .attr("id", "svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
         data.forEach(d => d.value = +getValue(d));
            // Mise en relation du scale avec les données
            x.domain(data.map(d => getLabel(d)));
            y.domain([0, d3.max(data, d => d.value)]);

            // Ajout de l'axe X au SVG
            // Déplacement de l'axe horizontal et du futur texte (via la fonction translate) au bas du SVG
            // Selection des noeuds text, positionnement puis rotation
            svg.append("g")
                .attr("transform", "translate(0," + height + ")")
                .call(d3.axisBottom(x).tickSize(0))
                .selectAll("text")	
                    .style("text-anchor", "end")
                    .attr("dx", "-.8em")
                    .attr("dy", ".15em")
                    .attr("transform", "rotate(-65)");

            // Ajout de l'axe Y au SVG avec 6 éléments de légende en utilisant la fonction ticks (sinon D3JS en place autant qu'il peut).
            svg.append("g")
                .call(d3.axisLeft(y).ticks(6));

           // ajout des bar
            svg.selectAll(".bar")
                .data(data)
            .enter().append("rect")
                .attr("class", "bar")
                .attr("x", d => x(getLabel(d)))
                .attr("width", x.bandwidth())
                .attr("y", d => y(d.value))
                .attr("height", d => height - y(d.value))
                .attr("fill", "#8F1204" )
                .attr("fill-opacity","0.5")
                //.attr("stroke", "#8F1204")
                //.attr("stroke-linecap", "round")
                //.attr("stroke-width", y.bandwidth() / 3)
                .on('mouseenter', function (actual, i) {
                    d3.select(this).attr('opacity', 0.5)
                })
                .on('mouseleave', function (actual, i) {
                    d3.select(this).attr('opacity', 1)
                })
                .on("mouseover", function(event, d) {
                    div.transition()        
                        .duration(200)      
                        .style("opacity", .9);
                    const [x, y] = d3.pointer(event);
                    div.html(getLabel(d) + "<br>" + d.value)
                        .style("left", (event.pageX) + "px")     
                        .style("top", (event.pageY) + "px");
                })
                .on("mouseout", function(d) {
                    div.transition()
                        .duration(500)
                        .style("opacity", 0);
                });
            svg.selectAll(".txthint").append("g").data(data).enter()
                        .append("text")
                        .attr("class", "txthint")
                        .attr("y",  d =>  y(d.value) - 5)
                        .attr("x", d => x(getLabel(d)) + x.bandwidth()/ 3.0)
                        .attr("font-size", Math.min(0.9*x.bandwidth(),12))
                        .text(d => d.value);
            // ajout du titre
            svg.append("text")
                .attr("x", (width / 2))             
                .attr("y", 0 - (margin.top / 2))
                .attr("text-anchor", "middle")  
                .style("font-size", "16px")
                .text(title);
             
    }