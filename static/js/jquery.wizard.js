/*
 * jQuery / jqLite Wizard Plugin
 * version: 0.0.3
 * Author: Girolamo Tomaselli http://bygiro.com
 *
 * Copyright (c) 2013 G. Tomaselli
 * Licensed under the MIT license.
 */

// compatibility for jQuery / jqLite
var bg = bg || false;
if(!bg){
	if(typeof jQuery != 'undefined'){
		bg = jQuery;
	} else if(typeof angular != 'undefined'){
		bg = angular.element;
		
		(function(){
			bg.extend = angular.extend;
			bg.isFunction = angular.isFunction;
		
			function selectResult(elem, selector){
				if (elem.length == 1)
					return elem[0].querySelectorAll(selector);
				else {
					var matches = [];
					for(var i=0;i<elem.length;i++){
						var elm = elem[i];
						var nodes = angular.element(elm.querySelectorAll(selector));
						matches.push.apply(matches, nodes.slice());					
					}
					return matches;

				}

			}	
		
			bg.prototype.find = function (selector){			
				var context = this[0];
				// Early return if context is not an element or document
				if (!context || (context.nodeType !== 1 && context.nodeType !== 9) || !angular.isString(selector)) {
					return [];
				}
				var matches = [];
				if (selector.charAt(0) === '>')
					selector = ':scope ' + selector;
				if (selector.indexOf(':visible') > -1) {
					var elems = angular.element(selectResult(this, selector.split(':visible')[0]))

					forEach(elems, function (val, i) {
						if (angular.element(val).is(':visible'))
							matches.push(val);
					})

				} else {
					matches = selectResult(this, selector)
				}

				if (matches.length) {
					if (matches.length == 1)
						return angular.element(matches[0])
					else {
						return angular.element(matches);
					}
				}
				return angular.element();
			};
			
			bg.prototype.outerWidth = function () {
				var el = this[0];
				if(typeof el == 'undefined') return null;
				return el.offsetWidth;
			};
			
			bg.prototype.width = function () {
				var el = this[0];
				if(typeof el == 'undefined') return null;
				var computedStyle = getComputedStyle(el);
				var width = el.offsetWidth;
				if (computedStyle)
					width -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
				return width;
			};
		
		})();
	}
}
 
;(function ($, document, window){

	"use strict";
	
    var pluginName = "wizardByGiro",
    // the name of using in .data()
	dataPlugin = "plugin_" + pluginName,
	defaults = {
		currentStep: 0,
		checkStep: true,
		onCompleted: false,
		bottomButtons: true,
		topButtons: true,
		autoSubmit: false,
		nextClass: '.btn-next',
		prevClass: '.btn-prev',
		text:{
			finished: text_complete,
			next: text_next,
			previous: text_prev
		}
	},
	
	attachEventsHandler = function(){
		var that = this;
		
		that.$element.find('.btn-prev:not(.disabled):not(.hidden)').on('click', function(e){
			e.stopPropagation();
			that.previous.call(that,e);
		});	
		
		that.$element.find('.btn-next').on('click', function(e){
					var variable = true;
    $('.active input:required').each(function() {
        if(!$(this).val()){
			$(this).css('border','1px solid red');
           // alert('Some fields are empty');
			variable = false;
        } else {
				$(this).css('border','1px solid #D5D9DD');
		}
    });
if(variable) {
			e.stopPropagation();
			that.next.call(that,e);
}
		});
		
		that.$element.find('.steps > li').on('click', function(e){
			e.stopPropagation();
			var step = $(this).attr('data-step'),
			isCompleted = $(this).hasClass('completed');
			if(!isCompleted) return true;
			
			that.setStep.call(that,step,e);
		});		
	},
	
	checkStatus = function(){
		var that = this,
			currentWidth,
			stepsWidth = 0,
			stepPosition = false,
			steps = that.$element.find('.steps'),
			stepsItems = that.$element.find('.steps > li');
			
		if(!this.currentStep) this.currentStep = 1;
		
		stepsItems.removeClass('active');
		that.$element
			.find('.steps > li[data-step="'+ that.currentStep +'"]')
			.addClass('active');
			
		that.$element.find('.steps-content .step-pane').removeClass('active');
		var current = that.$element.find('.steps-content .step-pane[data-step="'+ that.currentStep +'"]');
			current.addClass('active');

		for(var i=0;i<stepsItems.length;i++){
			var stepLi = $(stepsItems[i]);
			if(that.currentStep > (i+1)){
				stepLi.addClass('completed');
			} else {
				stepLi.removeClass('completed');
			}
			
			currentWidth = stepLi.outerWidth();
			if(!stepPosition && stepLi.hasClass('active')){				
				stepPosition = stepsWidth + (currentWidth / 2);
			}
			
			stepsWidth += currentWidth;			
		}
		
		// set buttons based on current step
		that.$element.find('.btn-next').removeClass('final-step btn-success');
		that.$element.find('.btn-prev').removeClass('disabled hidden');
		if(that.currentStep == stepsItems.length){
			// we are in the last step
			that.$element.find('.btn-next').addClass('final-step btn-success');
		} else if(that.currentStep == 1){
			that.$element.find('.btn-prev').addClass('disabled hidden');
		}		
		
		// move steps view if needed
		var totalWidth = that.$element.width() - that.$element.find('.actions').outerWidth();
		
		if(stepsWidth < totalWidth) return;
		
		var offsetDiff = stepPosition - (totalWidth / 2);
		if(offsetDiff > 0){
			// move it forward
			steps.css('left',-offsetDiff);
		} else {
			// move it backward
			steps.css('left',0);
		}
	},
	
	moveStep = function(step,direction,event){		
		var canMove = true,
		steps = this.$element.find('.steps > li'),
		triggerEnd = false;
		
		// check we can move
		if(typeof this.options.checkStep == 'function'){
			canMove = this.options.checkStep(this,direction,event);
		}
		
		if(!canMove) return;
		
		if(step){
			this.currentStep = parseInt(step);
		} else {
			if(direction){
				this.currentStep++;
			} else {
				this.currentStep--;
			}
		}

		if(this.currentStep < 0) this.currentStep = 0;
		if(this.currentStep > steps.length){
			this.currentStep = steps.length;
			triggerEnd = true;
		}
		
		checkStatus.call(this);
		
		if(triggerEnd){
			//alert("1");
			var atLeastOneIsChecked = $('input[name="form[agree]"]:checked').length > 0;
				//alert(atLeastOneIsChecked);
				if (!atLeastOneIsChecked)
				{
					alert("Bitte AGB  zustimmen oder Bestellung abbrechen");
					
					return false;
				}  else {
					
			if(typeof this.options.onCompleted == 'function'){
				this.options.onCompleted(this);
					alert("12");
			} else if(this.options.autoSubmit) {
						alert("122");
				// search if wizard is inside a form and submit it.
				
					var form = this.$element.closest('form');
					if(form.length)	form.submit();
			}
				}
		}
	},
		
	methods = {
		init: function (element, options) {
			var that = this;
			this.$element = $(element);
			this.options = $.extend({},	defaults, options);
			
			var opts = this.options;

			this.$element.addClass('wizard');
			
			// add the buttons
			var stepsBar = this.$element.find('.steps'),
			topActions = this.$element.find('.top-actions'),
			bottomActions = this.$element.find('.bottom-actions'),
			progressBar = this.$element.find('.progress-bar'),
			html = '';
			if(opts.topButtons && stepsBar.length && !topActions.length){
				// html += '<div class="top-actions"><div class="btn-group">';
				// html += '<span class="btn btn-default btn-mini btn-xs btn-prev"><span class="previous-text">'+ opts.text.previous +'</span></span>';
				// html += '<span class="btn btn-default btn-mini btn-xs btn-next"><span class="next-text">'+ opts.text.next +'</span><span class="finished-text">'+ opts.text.finished +'</span></span>';
				// html += '</div></div>';
				html += '';
				
				stepsBar.after(html);
			}
			
			html = '';
			if(opts.bottomButtons && !bottomActions.length){
				html += '<footer class="bottom-actions bottom_box on_the_sides">';
				// html += '<span class="btn btn-default btn-mini btn-xs btn-prev"><span class="previous-text">'+ opts.text.previous +'</span></span>';
				// html += '<span class="btn btn-default btn-mini btn-xs btn-next"><span class="next-text">'+ opts.text.next +'</span><span class="finished-text">'+ opts.text.finished +'</span></span>';
				html += '<div class="left_side buttons_row btn-prev"><a class="button_red middle_btn" href="javascript:void(0);">'+ opts.text.previous +'</a></div>';
				html += '<div class="right_side buttons_row btn-next"><a class="button_blue middle_btn next-text" href="javascript:void(0);">'+ opts.text.next +'</a><input class="button_blue middle_btn finished-text" type="submit" value="'+ opts.text.finished +'"></div>';
				
				html += '</footer>';
				
				that.$element.find('.steps-content').append(html);
			}

			// add arrows to btn
			// this.$element.find('.btn-prev').prepend('<i class="wiz-icon-arrow-left"></i>');
			// this.$element.find('.btn-next').append('<i class="wiz-icon-arrow-right"></i>');
			
			// get steps and prepare them
			var stepsLi = this.$element.find('.steps > li');
			for(var i=0;i<stepsLi.length;i++){
				var step = $(stepsLi[i]),
				target = step.attr('data-step'),
				content = '<span class="step-text">'+ step.html() +'</span>';
				
				step.empty().html('<span class="step-index"><span class="label">'+ (i+1) +'</span></span>'+ content + '<span class="wiz-icon-chevron-right colorA"></span><span class="wiz-icon-chevron-right colorB"></span>');
				
				that.$element.find('.steps-content [data-step="'+ target +'"]').addClass('step-pane');
				
				// detect currentStep
				if(step.hasClass('active') && !that.currentStep){
					that.currentStep = i+1;
				}				
			}

			this.$element.find('.steps > li:last-child').addClass('final');
			
			attachEventsHandler.call(this);
			
			var callbacks = ['checkStep','onCompleted'],cb;
			for(var i=0;i<callbacks.length;i++){
				cb = callbacks[i];
				if(typeof this.options[cb] == 'string' && typeof window[this.options[cb]] == 'function'){
					this.options[cb] = window[this.options[cb]];
				}
			}
		
			checkStatus.call(this);
		},

		next: function(event){			
			moveStep.call(this,false,true,event);
		},
		
		previous: function(event){
			moveStep.call(this,false,false,event);
		},
		
		setStep: function(step, event){
			moveStep.call(this,step,null,event);
		}
	};
		
    var main = function (method) {
        var thisPlugin = this.data(dataPlugin);
        if (thisPlugin) {
            if (typeof method === 'string' && thisPlugin[method]) {
                return thisPlugin[method].apply(thisPlugin, Array.prototype.slice.call(arguments, 1));
            }
            return console.log('Method ' + arg + ' does not exist on jQuery / jqLite' + pluginName);
        } else {
            if (!method || typeof method === 'object') {
				thisPlugin = $.extend({}, methods);
				thisPlugin.init(this, method);
				this.data(dataPlugin, thisPlugin);

				return this;
            }
            return console.log( pluginName +' is not instantiated. Please call $("selector").'+ pluginName +'({options})');
        }
    };

	// plugin integration
	if($.fn){
		$.fn[ pluginName ] = main;
	} else {
		$.prototype[ pluginName ] = main;
	}

	$(document).ready(function(){
		var mySelector = document.querySelector('[data-wizard-init]');
		$(mySelector)[ pluginName ]({});				
	});
}(bg, document, window));
