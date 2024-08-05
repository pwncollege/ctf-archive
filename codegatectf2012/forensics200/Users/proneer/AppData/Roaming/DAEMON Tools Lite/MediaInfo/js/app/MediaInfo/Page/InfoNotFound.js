/**
 * @class MediaInfo.Page.InfoNotFound
 */
MediaInfo.Page.InfoNotFound = new Class(/** @lends MediaInfo.Page.InfoNotFound.prototype */{
	Extends: MediaInfo.Page.PageAbstract,

	/**
	 * Show page
	 *
	 * @returns {Void}
	 */
	show: function() {

		var _this = this;
		// Prepare template
		var templateDirective = {
			'.other_statistics_button@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.homeUrl, 'other_statistics_button')
				);
			}
		};

		// Prepare template data
		var templateData = {
			homeUrl: "http://" + MediaInfo.Ajax.getServerHost() + "/"
		};

		// Render template
		this.renderPage('page-info-not-found', templateDirective, templateData);
		this.showPageBlock('page-info-not-found');

		// Render diagram
		var imageInfo = app.imageInfoLoader.getInfo();
		if( imageInfo.data && typeof imageInfo.data.diagramData != "undefined"  ) {
			var size = imageInfo.data.diagramData.length;
			if( size >= 7 ) {
				this.showDiagram( imageInfo.data.diagramData );
			}
		}

		// Render TOP
		app.imageInfoLoader.getTopsInfo({
			success: function(data){
				_this.renderWidgetTops(
					{games:data.games},
					$('#page-info-not-found .tops_block'),
					'not_found_top',
					60
				);
			}
		});

	},
	showDiagram: function( data ) {
		var diagramData = [];
		var size = data.length;
		var maxActiveUser = 0;
		for( i = 0; i < size; i++ ) {
			diagramData.push( [ data[i]['date'], data[i]['user_active'] ] );
			var number = parseInt( data[i]['user_active'] );
			if( maxActiveUser < number ) {
				maxActiveUser = number;
			}
		}
 		var activeCutCount = Math.ceil( ( maxActiveUser + '').length / 3 );
 		var activeCutNumber = Math.pow( 10, activeCutCount );
 		var activeMaxYAxis = ( ( Math.round( maxActiveUser / activeCutNumber ) + 1) * activeCutNumber );

		var centerPosition = Math.ceil( size / 2 );
		var dateTicks = [data[0]['date'], data[centerPosition]['date'], data[size-1]['date'] ];
		var discDiagramDefaultOptions = {
			series:[
				{
					showMarker: false
				}
			],
			axes: {
				xaxis: {
					renderer: $.jqplot.DateAxisRenderer,
					tickOptions: {
						formatString: '%b&nbsp;%d'
					},
					numberTicks: 7,
					pad: 0

					,
					ticks: dateTicks
				},
				yaxis: {
					tickOptions: {
						formatString: '%d'
					},
					pad: 1.5,
					min: 0
				}
			},
			legend: {
				show: false
			},
		    highlighter: {
				show: true,
				sizeAdjust: 7.5
			}
		};

		$('.diagram-wrap').css( 'display', 'block' );
		var discDiagramActive = $.jqplot(
			'disc_diagram_active',
			[diagramData],
			$.extend(true, {}, discDiagramDefaultOptions, {
				title: '',
				labels:['users'],
				seriesColors: ['#f1601c'],
				axes: {
					yaxis: { 	max: activeMaxYAxis	}
				}
			})
		);
	}

});