/**
 *
 */
var DocumentWidget = function () {
	var _this = this;

	_this.$widgets = $('[data-document-widget]');
	_this.$sortables = $('.documents-widget-sortable');
	_this.$toggleExplorerButtons = $('[data-document-widget-toggle-explorer]');
	_this.$unlinkDocumentButtons = $('[data-document-widget-unlink-document]');

	_this.init();
};

DocumentWidget.prototype.$explorer = null;
DocumentWidget.prototype.$widgets = null;
DocumentWidget.prototype.$toggleExplorerButtons = null;
DocumentWidget.prototype.$unlinkDocumentButtons = null;
DocumentWidget.prototype.$sortables = null;

DocumentWidget.prototype.init = function() {
	var _this = this;

	var changeProxy = $.proxy(_this.onSortableDocumentWidgetChange, _this);
	_this.$sortables.on('uk.sortable.change', changeProxy);
	_this.$sortables.on('uk.sortable.change', changeProxy);

	var onExplorerToggleP = $.proxy(_this.onExplorerToggle, _this);
	_this.$toggleExplorerButtons.off('click', onExplorerToggleP);
	_this.$toggleExplorerButtons.on('click', onExplorerToggleP);

	var onUnlinkDocumentP = $.proxy(_this.onUnlinkDocument, _this);
	_this.$unlinkDocumentButtons.off('click', onUnlinkDocumentP);
	_this.$unlinkDocumentButtons.on('click', onUnlinkDocumentP);
};

/**
 * Update document widget input values after being sorted.
 *
 * @param  {[type]} event   [description]
 * @param  {[type]} element [description]
 * @return {void}
 */
DocumentWidget.prototype.onSortableDocumentWidgetChange = function(event, list, element) {
	var _this = this;

	//console.log("Document: "+element.data('document-id'));
	console.log(element);
	$sortable = $(element).parent();
	var inputName = 'source['+$sortable.data('input-name')+']';
	$sortable.find('li').each(function (index) {
		$(this).find('input').attr('name', inputName+'['+index+']');
	});

	return false;
};

/**
 * Create document explorer.
 *
 * @param  {[type]} event [description]
 * @return false
 */
DocumentWidget.prototype.onExplorerToggle = function(event) {
	var _this = this;

	if (_this.$explorer === null) {

		_this.$toggleExplorerButtons.addClass('uk-active');

		var ajaxData = {
			'_action':'toggleExplorer',
			'_token': Rozier.ajaxToken
		};

		$.ajax({
			url: Rozier.routes.documentsAjaxExplorer,
			type: 'get',
			dataType: 'json',
			data: ajaxData
		})
		.success(function(data) {
			console.log(data);
			console.log("success");

			if (typeof data.documents != "undefined") {

				var $currentsortable = $($(event.currentTarget).parents('.documents-widget')[0]).find('.documents-widget-sortable');
				_this.createExplorer(data, $currentsortable);
			}
		})
		.fail(function(data) {
			console.log(data.responseText);
			console.log("error");
		});
	}
	else {
		_this.$toggleExplorerButtons.removeClass('uk-active');
		_this.$explorer.removeClass('visible');
		_this.$explorer.one('transitionend webkitTransitionEnd mozTransitionEnd msTransitionEnd', function(event) {
			/* Act on the event */
			_this.$explorer.remove();
			_this.$explorer = null;
		});
	}

	return false;
};

DocumentWidget.prototype.onUnlinkDocument = function( event ) {
	var _this = this;

	var $element = $(event.currentTarget);

	var $doc = $element.parents('li');
	var $widget = $element.parents('.documents-widget-sortable').first();

	$doc.remove();
	$widget.trigger('uk.sortable.change', [$widget, $doc]);

	return false;
};

/**
 * Populate explorer with documents thumbnails
 * @param  {[type]} data [description]
 * @return {[type]}      [description]
 */
DocumentWidget.prototype.createExplorer = function(data, $originWidget) {
	var _this = this;
	console.log($originWidget);
	var changeProxy = $.proxy(_this.onSortableDocumentWidgetChange, _this);

	$("body").append('<div class="document-widget-explorer"><ul class="uk-sortable"></ul></div>');
	_this.$explorer = $('.document-widget-explorer');
	var $sortable = _this.$explorer.find('.uk-sortable');

	for (var i = 0; i < data.documents.length; i++) {
		var doc = data.documents[i];
		$sortable.append(doc.html);
	}

	$sortable.find('li').each (function (index, element) {
		var $link = $(element).find('.link-button');
		if($link.length){
			$link.on('click', function (event) {

				var $object = $(event.currentTarget).parent();
				$object.appendTo($originWidget);

				var inputName = 'source['+$originWidget.data('input-name')+']';
				$originWidget.find('li').each(function (index, element) {
					$(element).find('input').attr('name', inputName+'['+index+']');
				});

				return false;
			});
		}
	});

	window.setTimeout(function () {
		_this.$explorer.addClass('visible');
	}, 0);
};