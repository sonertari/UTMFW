<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Prints pie chart on a modal.
 */
function PrintModalPieChart()
{
	?>
	<!-- The Modal -->
	<div id="modalPieChart" class="modal">
		<!-- The Close Button -->
		<span class="close">&times;</span>
	</div>

	<!-- The Pie Slice Tooltip -->
	<div id="slicetip" class="hidden">
		<p><strong><span id="title"></span></strong></p>
		<p><span id="key"></span></p>
		<p><span id="value"></span></p>
	</div>

	<script type="text/javascript" src="../lib/d3.min.js"></script>
	<script language="javascript" type="text/javascript">
		// Get the modal
		var modal = document.getElementById('modalPieChart');

		var slicetip = d3.select('#slicetip');

		function generateChart(dataset, title) {
			modal.style.display = 'block';

			var keys = Object.keys(dataset);
			var values = Object.values(dataset);

			var dataSum = d3.sum(values);

			// Use index to get key from dataset to display it on slicetip
			// {key => {index => value}}
			var chartDataset = {};
			var curSum = 0;
			for (var j = 0; j < keys.length; ++j) {
				if (j > 10) {
					chartDataset['others'] = {index: j, value: dataSum - curSum};
					break;
				}
				chartDataset[keys[j]] = {index: j, value: values[j]};
				curSum += values[j];
			}

			// Use an accessor to get value as pie data
			var pie = d3.pie().value((d) => d.value);
			var w = 400;
			var h = 400;
			var outerRadius = w / 2;
			var innerRadius = 0;

			var arc = d3.arc()
				.innerRadius(innerRadius)
				.outerRadius(outerRadius);

			// Create SVG element
			var svg = d3.select('#modalPieChart')
				.append('svg')
				.attr('id', 'pieChart')
				.attr('width', w)
				.attr('height', h)
				.attr('class', 'modal-content');

			// Set up groups
			var arcs = svg.selectAll('g.arc')
				.data(pie(Object.values(chartDataset)))
				.enter()
				.append('g')
				.attr('class', 'arc')
				.attr('transform', 'translate(' + outerRadius + ', ' + outerRadius + ')');

			var color = d3.scaleOrdinal(d3.schemeCategory10);

			// Draw arc paths
			arcs.append('path')
				.attr('fill', function (d, i) {
					return color(i);
				})
				.attr('d', arc)
				.on('mouseover', (event, d) => {
					d3.select(event.currentTarget).style('opacity', 0.7);

					// Get the mouse pointer's x/y page coords for the tooltip
					var x = event.pageX + 25;
					var y = event.pageY + 25;

					// Update the tooltip position and contents
					slicetip
						.style('left', x + 'px')
						.style('top', y + 'px')
						.style('z-index', 1);
					slicetip
						.select('#title')
						.text(title);
					slicetip
						.select('#key')
						.text(Object.keys(chartDataset)[d.data.index]);
					slicetip
						.select('#value')
						.text(Math.round(100 * d.value / dataSum) + '%: ' + d.value + '/' + dataSum);

					// Show the tooltip
					slicetip.classed('hidden', false);
				})
				.on('mouseout', (event) => {
					d3.select(event.currentTarget)
						.transition()
						.duration(500)
						.style('opacity', 1);

					// Hide the tooltip
					slicetip.classed('hidden', true);
				});

			arcs.append('text')
				.attr('transform', function (d) {
					return 'translate(' + arc.centroid(d) + ')';
				})
				.attr('text-anchor', 'middle')
				.attr('font-weight', 'bold')
				.text(function (d, i) {
					return (d.value / dataSum > .05) ? Object.keys(chartDataset)[i] : '';
				});
		};

		// Get the <span> element that closes the modal
		var xbtn = document.getElementsByClassName('close')[0];

		// When the user clicks on the modal or <span> (x), close the modal
		function close() {
			var chart = document.getElementById('pieChart');
			// This function is called twice, for both xbtn and modal
			if (chart) {
				chart.parentNode.removeChild(chart);
				slicetip.classed('hidden', true);
				modal.style.display = 'none';
			}
		}
		xbtn.onclick = close;
		modal.onclick = close;
	</script>
	<?php
}

/**
 * Displays chart trigger images.
 * 
 * These images are initially hidden, we enable them using this JavaScript code.
 * So that if JavaScript is disabled, we don't show the triggers at all.
 * Pie charts need JavaScript being enabled.
 * 
 * @attention This function should be called at the end of the page, after the PHP code inserts the hidden images.
 */
function DisplayChartTriggers()
{
	?>
	<script language="javascript" type="text/javascript">
		var trigs = document.getElementsByClassName('chart-trigger');
		for (var j = 0; j < trigs.length; ++j) {
			trigs[j].style.display = 'inline';
		}
	</script>
	<?php
}

/// Stats page warning message.
$StatsWarningMsg= _NOTICE('Analysis of statistical data may take a long time to process. Please be patient. Also note that if you refresh this page frequently, CPU load may increase considerably.');

/// Main help box used on all statistics pages.
$StatsHelpMsg= _HELPWINDOW('This page displays statistical data collected over the log files of this module.

You can change the date of statistics using drop-down boxes. An empty value means match-all. For example, if you choose 3 for month and empty value for day fields, the charts and lists display statistics for all the days in March. Choosing empty value for month means empty value for day field as well.

For single dates, Horizontal chart direction is assumed. For date ranges, default graph style is Daily, and direction is Vertical. Graph style can be changed to Hourly for date ranges, where cumulative hourly statistics are shown. In Daily style, horizontal direction is not possible.');

$Submenu= SetSubmenu('general');
require_once("../lib/stats.$Submenu.php");
?>
