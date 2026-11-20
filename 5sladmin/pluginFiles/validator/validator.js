/*
 Validator jQuery Plugin
 Validator is a JQuery validation plugin for forms.
 version 1.9, Sep 5th, 2015
 by Ingi á Steinamørk

 The MIT License (MIT)

 Copyright (c) 2013 Qodio

 Permission is hereby granted, free of charge, to any person obtaining a copy of
 this software and associated documentation files (the "Software"), to deal in
 the Software without restriction, including without limitation the rights to
 use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 the Software, and to permit persons to whom the Software is furnished to do so,
 subject to the following conditions:

 The above copyright notice and this permission notice shall be included in all
 copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

data-required
data-required-if
data-min
data-max
data-match
data-type
	//email
	//url
	//number
	//digits
	//cellphone
	//idnumber
	//time
	//date
	//keycode
 */

var Validator = {
	elementErrorClass: 'valierror',
	language: 'tw',
	languages: {
		'en': {
			textbox: {
				required: 'This field is required',
				min: 'This field must contain at least {characters} characters',
				max: 'This field must not contain more than {characters} characters',
				email: 'Email is not valid',
				url: 'Website is not valid',
				number: 'Only numbers',
				digits: 'Only numbers'
			},
			password: {
				required: 'This field is required',
				min: 'This field must contain at least {characters} characters',
				max: 'This field must not contain more than {characters} characters',
				match: 'The passwords do not match'
			},
			radio: {
			},
			checkbox: {
				required: 'This checkbox is required'
			},
			select: {
				required: 'Choose a field from the list'
			},
			textarea: {
				required: 'This field is required',
				min: 'This field must contain at least {characters} characters',
				max: 'This field must not contain more than {characters} characters',
				url: 'Website is not valid'
			}
		},
		'tw': {
			textbox: {
				required: '必須填寫',
				min: '必須大於(含) {characters} 字元',
				max: '必須小於(含) {characters} 字元',
				email: '必須為電子郵箱格式',
				url: '必須為網址格式',
				number: '必須為純數字',
				digits: '必須為以數字組成的字串',
				cellphone: '必須為10位數手機號碼格式',
				idnumber: '必須為10位數身份證號格式',
				time: '必須為HH:MM時間格式',
				date: '必須為YYYY-MM-DD日期格式',
				keycode: '必須為大小寫字母以及數字'

			},
			password: {
				required: '必須填寫',
				//min: '必須大於(含) {characters} 字元',
				//max: '必須小於(含) {characters} 字元',
				password: '必須為數字及小寫字母組合的8-16碼密碼格式',
				match: '與密碼不符'
			},
			radio: {
			},
			checkbox: {
				required: '必須勾選'
			},
			select: {
				required: '必須選擇'
			},
			textarea: {
				required: '必須填寫',
				min: '必須大於(含) {characters} 字元',
				max: 'T必須小於(含) {characters} 字元',
				url: '必須為網址格式'
			}
		}
	},
	patterns: {
		url: /^((https?|ftp|mailto):(\/\/)?)?((([a-z\d]+)(\.|@|:|-))+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3}))(\:\d+)?(\/[a-z\d_\-+~%.()]*)*(\?[a-z\d_\-+~%.,=;&{}/()]*)?(\#[a-z\d_\-+~%.,=;&{}/()]*)?$/i,
		email: /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i,
		number: /^\s*(\+|-)?((\d+([\.,]\d+)?)|([\.,]\d+))%?\s*$/i,
		digits: /^\s*\d+\s*$/,
		password: /^(?=.*[a-z])(?=.*\d)[a-z\d]{8,16}$/,
		cellphone: /^09\d{8}$/,
		idnumber: /^[A-Z]{1}[1-2]{1}[0-9]{8}$/,
		time: /^((2[0-3]|[01][1-9]|10):([0-5][0-9]))$/,
		date: /^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/,
		keycode: /[A-Z]|[a-z]|[0-9]/i
	},
	/*
	showError: function (element, text) {
		$(element).css('border-color','red');


	},
	*/
	showError: function (element, text) {
		if (!$(element).hasClass("valierror")) {
			//var error = document.createElement('div');
			//$(error).addClass('validator-error').html(text);
			var error = document.createElement('i');
			$(error).addClass('vali-error');
			//$(error).next().addClass('vali-error-text');

			if ($(element).attr('data-error-position') == undefined) {
				var errorPosition = 'before';
				if ($(this).is('input') && $(this).attr('type') == 'checkbox') {
					errorPosition = 'before label';
				}
			} else {
				errorPosition = $(element).attr('data-error-position');
			}
			var attrValue = errorPosition.split(' ');
			var targetElementForError;
			if (attrValue[1] == undefined) {
				targetElementForError = element;
			} else {
				targetElementForError = $(element).closest(attrValue[1])[0];
			}
			if (attrValue[0] == 'before') {
				$(targetElementForError).before(error);
				//$(targetElementForError).addClass("valierror");
				$(element).addClass("valierror");
			} else if (attrValue[0] == 'after') {
				$(targetElementForError).after(error);
				//$(targetElementForError).addClass("valierror");
				$(element).addClass("valierror");
			} else if (attrValue[0] == 'prepend') {
				$(targetElementForError).prepend(error);
				$(element).addClass("valierror");
			} else if (attrValue[0] == 'append') {
				$(targetElementForError).append(error);
				$(element).addClass("valierror");
			}

			if ($(element).attr('data-match') != undefined) {
				$('#' + $(element).attr('data-match')).addClass("valierror");
			}
		}
	},

	validate: function (form, onlyVisible) {
		var hasErrors = false;
		var firstErrorElement = null;
		onlyVisible = typeof onlyVisible !== 'undefined' ? onlyVisible : false;
		var ErrorPopUpMess = '';
		Validator.removeErrors(form);

		$(form).find('input, select, textarea').each(function () {
			//var thisID = $(this).prop('id');
            //var tTitle = $(form).find('label[for="'+thisID+'"]').html();
			var tTitle = $(this).attr('placeholder');
			if (!onlyVisible || $(this).is(':visible')) {
				// Input[type=text]
				if ($(this).is('input') && ($(this).attr('type') == 'text' || $(this).attr('type') == undefined)) {
					// required
					if ($(this).attr('data-required') != undefined && $(this).val() == '' && $(this).attr('data-required-if') == undefined) {
						Validator.showError(this, Validator.languages[Validator.language].textbox.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.required + '<br>';
						hasErrors = true;
					}
					// required-if & required-if-value
					if ($(this).attr('data-required-if') != undefined && $(this).val() == '' && (($(this).attr('data-required-if-value') == undefined && $('#' + $(this).attr('data-required-if')).is(':checked')) || ($(this).attr('data-required-if-value') != undefined && $('#' + $(this).attr('data-required-if')).val() == $(this).attr('data-required-if-value')))) {
						Validator.showError(this, Validator.languages[Validator.language].textbox.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.required + '<br>';
						hasErrors = true;
					}
					// min
					if ($(this).attr('data-min') != undefined && $(this).val().length < parseFloat($(this).attr('data-min')) && $(this).val().length != 0) {
						Validator.showError(this, Validator.languages[Validator.language].textbox.min.replace('{characters}', $(this).attr('data-min')));
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.min.replace('{characters}', $(this).attr('data-min')) + '<br>';
						hasErrors = true;
					}
					// max
					if ($(this).attr('data-max') != undefined && $(this).val().length > parseFloat($(this).attr('data-max'))) {
						Validator.showError(this, Validator.languages[Validator.language].textbox.max.replace('{characters}', $(this).attr('data-max')));
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.max.replace('{characters}', $(this).attr('data-max')) + '<br>';
						hasErrors = true;
					}
					// patterns
					if ($(this).attr('data-type') != undefined) {
						switch ($(this).attr('data-type')) {
							case 'email':
								if (!Validator.patterns.email.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.email);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.email + '<br>';
									hasErrors = true;
								}
								break;
							case 'url':
								if ($(this).val().substr(0, 1) !== '/' && !Validator.patterns.url.test($(this).val().replace('_', '')) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.url);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.url + '<br>';
									hasErrors = true;
								}
								break;
							case 'number':
								if (!Validator.patterns.number.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.number);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.number + '<br>';
									hasErrors = true;
								}
								break;
							case 'digits':
								if (!Validator.patterns.digits.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.digits);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.digits + '<br>';
									hasErrors = true;
								}
								break;
							case 'cellphone':
								if (!Validator.patterns.cellphone.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.cellphone);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.cellphone + '<br>';
									hasErrors = true;
								}
								break;
							case 'idnumber':
								if (!Validator.patterns.idnumber.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.idnumber);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.idnumber + '<br>';
									hasErrors = true;
								}
								break;
							case 'time':
								if (!Validator.patterns.time.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.time);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.time + '<br>';
									hasErrors = true;
								}
								break;
							case 'date':
								if (!Validator.patterns.date.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.date);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.date + '<br>';
									hasErrors = true;
								}
								break;
							case 'keycode':
								if (!Validator.patterns.keycode.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textbox.keycode);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textbox.keycode + '<br>';
									hasErrors = true;
								}
								break;
						}

					}
				}
				// Input[type=password]
				if ($(this).is('input') && $(this).attr('type') == 'password') {
					// required
					if ($(this).attr('data-required') != undefined && $(this).val() == '' && $(this).attr('data-required-if') == undefined) {
						Validator.showError(this, Validator.languages[Validator.language].password.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].password.required + '<br>';
						hasErrors = true;
					}
					// required-if & required-if-value
					if ($(this).attr('data-required-if') != undefined && $(this).val() == '' && (($(this).attr('data-required-if-value') == undefined && $('#' + $(this).attr('data-required-if')).is(':checked')) || ($(this).attr('data-required-if-value') != undefined && $('#' + $(this).attr('data-required-if')).val() == $(this).attr('data-required-if-value')))) {
						Validator.showError(this, Validator.languages[Validator.language].password.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].password.required + '<br>';
						hasErrors = true;
					}
					// min
					/*
					if ($(this).attr('data-min') != undefined && $(this).val().length < parseFloat($(this).attr('data-min')) && $(this).val().length != 0) {
						Validator.showError(this, Validator.languages[Validator.language].password.min.replace('{characters}', $(this).attr('data-min')));
						hasErrors = true;
					}
					// max
					if ($(this).attr('data-max') != undefined && $(this).val().length > parseFloat($(this).attr('data-max'))) {
						Validator.showError(this, Validator.languages[Validator.language].password.max.replace('{characters}', $(this).attr('data-min')));
						hasErrors = true;
					}
					*/
					// match
					if ($(this).attr('data-match') != undefined && $(this).val() != $('#' + $(this).attr('data-match')).val()) {
						Validator.showError(this, Validator.languages[Validator.language].password.match);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].password.match + '<br>';
						hasErrors = true;
					}
					// patterns
					if ($(this).attr('data-type') != undefined && $(this).attr('data-type')=='password') {
						if (!Validator.patterns.password.test($(this).val()) && $(this).val() != '') {
							Validator.showError(this, Validator.languages[Validator.language].password.password);
							ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].password.password + '<br>';
							hasErrors = true;
						}
					}

				}
				// Input[type=radio]
				if ($(this).is('input') && $(this).attr('type') == 'radio') {
				}
				// Input[type=checkbox]
				if ($(this).is('input') && $(this).attr('type') == 'checkbox') {
					// required
					if ($(this).attr('data-required') != undefined && !$(this).is(':checked') && $(this).attr('data-required-if') == undefined) {
						Validator.showError(this, Validator.languages[Validator.language].checkbox.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].checkbox.required + '<br>';
						hasErrors = true;
					}
					// required-if & required-if-value
					if ($(this).attr('data-required-if') != undefined && $(this).val() == '' && (($(this).attr('data-required-if-value') == undefined && $('#' + $(this).attr('data-required-if')).is(':checked')) || ($(this).attr('data-required-if-value') != undefined && $('#' + $(this).attr('data-required-if')).val() == $(this).attr('data-required-if-value')))) {
						Validator.showError(this, Validator.languages[Validator.language].checkbox.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].checkbox.required + '<br>';
						hasErrors = true;
					}
				}
				// Select
				if ($(this).is('select')) {
					// required
					if ($(this).attr('data-required') != undefined && $(this).val() == '' && $(this).attr('data-required-if') == undefined) {
						Validator.showError(this, Validator.languages[Validator.language].select.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].select.required + '<br>';
						hasErrors = true;
					}
					// required-if & required-if-value
					if ($(this).attr('data-required-if') != undefined && $(this).val() == '' && (($(this).attr('data-required-if-value') == undefined && $('#' + $(this).attr('data-required-if')).is(':checked')) || ($(this).attr('data-required-if-value') != undefined && $('#' + $(this).attr('data-required-if')).val() == $(this).attr('data-required-if-value')))) {
						Validator.showError(this, Validator.languages[Validator.language].select.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].select.required + '<br>';
						hasErrors = true;
					}
				}
				// Textarea
				if ($(this).is('textarea')) {
					// required
					if ($(this).attr('data-required') != undefined && $(this).val().replace(/<[^>]*(>|$)|&nbsp;|&zwnj;|&raquo;|&laquo;|&gt;|' '/g, '') == '' && $(this).attr('data-required-if') == undefined) {
						Validator.showError(this, Validator.languages[Validator.language].textarea.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textarea.required + '<br>';
						hasErrors = true;
					}
					// required-if & required-if-value
					if ($(this).attr('data-required-if') != undefined && $(this).val().replace(/<[^>]*(>|$)|&nbsp;|&zwnj;|&raquo;|&laquo;|&gt;|' '/g, '') == '' && (($(this).attr('data-required-if-value') == undefined && $('#' + $(this).attr('data-required-if')).is(':checked')) || ($(this).attr('data-required-if-value') != undefined && $('#' + $(this).attr('data-required-if')).val() == $(this).attr('data-required-if-value')))) {
						Validator.showError(this, Validator.languages[Validator.language].textarea.required);
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textarea.required + '<br>';
						hasErrors = true;
					}
					// min 
					if ($(this).attr('data-min') != undefined && $(this).val().replace(/<[^>]*(>|$)|&nbsp;|&zwnj;|&raquo;|&laquo;|&gt;|' '/g, '').length < parseFloat($(this).attr('data-min')) && $(this).val().replace(/<[^>]*(>|$)|&nbsp;|&zwnj;|&raquo;|&laquo;|&gt;|' '/g, '').length != 0) {
						Validator.showError(this, Validator.languages[Validator.language].textarea.min.replace('{characters}', $(this).attr('data-min')));
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textarea.min.replace('{characters}', $(this).attr('data-min')) + '<br>';
						hasErrors = true;
					}
					// max
					if ($(this).attr('data-max') != undefined && $(this).val().rreplace(/<[^>]*(>|$)|&nbsp;|&zwnj;|&raquo;|&laquo;|&gt;|' '/g, '').length > parseFloat($(this).attr('data-max'))) {
						Validator.showError(this, Validator.languages[Validator.language].textarea.max.replace('{characters}', $(this).attr('data-min')));
						ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textarea.max.replace('{characters}', $(this).attr('data-min')) + '<br>';
						hasErrors = true;
					}
					// patterns
					if ($(this).attr('data-type') != undefined) {
						switch ($(this).attr('data-type')) {
							case 'url':
								if (!Validator.patterns.url.test($(this).val()) && $(this).val() != '') {
									Validator.showError(this, Validator.languages[Validator.language].textarea.url);
									ErrorPopUpMess += '「'+tTitle+'」' + Validator.languages[Validator.language].textarea.url + '<br>';
									hasErrors = true;
								}
								break;
						}
					}
				}
				// Focus first element with error
				/*
				if (hasErrors && firstErrorElement == null) {
					firstErrorElement = this;
					$(this).focus();
				}
				*/
				$("#searchURI").focus();
			}
		});
		if(ErrorPopUpMess!=''){
			ErrorPopUpMess = "以下欄位填寫錯誤，請修正後重新送出：<br>----------------------------------------------------------<br><br>"+ErrorPopUpMess+"<br>----------------------------------------------------------<br><br>";
			//alert(ErrorPopUpMess);
			Swal.fire({
				icon: "error",
				title: "人生第五季系統提醒",
				html: '<div class="text-left pl-5 small" style="line-height: 1.5;">' + ErrorPopUpMess + '</div>',
				confirmButtonColor: "#3c8dbc",
				confirmButtonText: "我知道了!",
			});
		}
		return !hasErrors;
	},
	removeErrors: function (form) {
		// Remove all error text divs
		$(form).find('.vali-error').each(function () {
			$(this).remove();
		});
		$(form).find('.valierror').each(function () {
			$(this).removeClass('valierror');
		});

		// Reset error classes
		$(form).find('input[type=text], input[type=password], input[type=radio], input[type=checkbox], select, textarea').each(function () {
			if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
				$(this).closest('label').removeClass("valierror");
			} else {
				$(this).removeClass("valierror");
			}
		});

	}
};

$(function () {
	$('form.validator').each(function () {
		$(this).submit(function () {
			return Validator.validate(this, false);
		});
	});
	$('form.validator-onlyvisible').each(function () {
		$(this).submit(function () {
			return Validator.validate(this, true);
		});
	});
});
