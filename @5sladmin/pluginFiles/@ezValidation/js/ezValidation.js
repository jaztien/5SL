(function ($) {
    $.fn.validate = function (options) {
		//settings
        var settings = $.extend({
			//the Error messages
            required: "不得為空值！",
            email: "必須為郵件格式",
            password: "必須為至少8位以上的英文及數字組合！",
            match: "不一致！",
            number: "必須為數字！",
            min: "必須大於",
            max: "必須小於",
            time: "必須是時間格式 HH:MM！",
            date: "必須是日期格式 YYYY-MM-DD！",
            cell: "必須是10位數手機格式！",
            id: "必須是10位數身分證號格式！"
			//the help element class
            //helpClass: ".help-inline"
        }, options);
        var clean = true, $this = this;
        var returnMess = '';
        ($this.is("input") ? $this : $this.find("input")).each(function () {
            var ths = $(this), val = ths.val();
            //var help = ths.next(settings.helpClass);
            //var thisID = ths.prop('id');
            //var tTitle = $this.find('label[for="'+thisID+'"]').html();
            var tTitle = '';
            if (ths.data("required") && val == "") {
                clean = false;
                returnMess += '「'+tTitle+'」'+settings.required+'\r\n';
                ths.css("border-color", "red");
                //help.show().html(tTitle+': '+settings.required);
            }
            if (clean && ths.data("email") && !ValidateEmail(val)) {
                clean = false;
                //help.show().html(settings.email);
                returnMess += '「'+tTitle+'」'+settings.email+'\r\n';
                ths.css("border-color", "red");
            }
            if (clean && ths.data("password") && !ValidatePassword(val)) {
                clean = false;
                //help.show().html(settings.password);
                returnMess += '「'+tTitle+'」'+settings.password+'\r\n';
                ths.css("border-color", "red");
            }
            if (clean && ths.data("match") && val!=$("#" + ths.data("matchwith")).val()) {
                clean = false;
                //help.show().html(settings.match);
                returnMess += '「'+tTitle+'」'+settings.match+'\r\n';
                ths.css("border-color", "red");
            }
            if (clean && ths.data("number") && isNaN(val)) {
                clean = false;
                //help.show().html(settings.number);
                returnMess += '「'+tTitle+'」'+settings.number+'\r\n';
                ths.css("border-color", "red");
            }
            if (clean && ths.data("cell") && !ValidateCell(val)) {
                clean = false;
                //help.show().html(settings.time);
                returnMess += '「'+tTitle+'」'+settings.cell+'\r\n';
                ths.css("border-color", "red");
            }
            if (clean && ths.data("id") && !ValidateID(val)) {
                clean = false;
                //help.show().html(settings.time);
                returnMess += '「'+tTitle+'」'+settings.id+'\r\n';
                ths.css("border-color", "red");
            }
            if (clean && ths.data("time") && !ValidateTime(val)) {
                clean = false;
                //help.show().html(settings.time);
                returnMess += '「'+tTitle+'」'+settings.time+'\r\n';
                ths.css("border-color", "red");
            }
            if (clean) {
                ths.css("border-color", "");
            } else {
                ths.one("blur", function () {
                    $(this).validate();
                });
            }
        });
        if(returnMess!=""){
            returnMess = "以下欄位填寫錯誤，請修正後重新送出：\r\n------------------------------------\r\n\r\n"+returnMess+"\r\n------------------------------------\r\n\r\n";
            return returnMess;
        } else {
            return '';
        }
    };
	function ValidatePassword(s) {
        //var re = /^(?=.*[a-z])(?=.*\d)[a-z\d]{8,}$/,//8位數以上的英文小寫以及數字的混合字串
        //var re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/,//8位數以上的英文小寫以及數字的混合字串
        var re = /^(?=^.{8,}$)((?=.*\d)^(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/,
            r = /[\W]/g;
            return (!(s == "" || s.search(re) == -1 || s.search(r) == -1));
            //return re.test(s);
    }
    function ValidateEmail(s) {
        return /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(s);
    }
    function ValidateCell(s) {
        return /^09\d{8}$/.test(s);
    }
    function ValidateID(s) {
        return /^[A-Z]{1}[1-2]{1}[0-9]{8}$/.test(s);
    }
    function ValidateTime(s) {
        return /^((2[0-3]|[01][1-9]|10):([0-5][0-9]))$/.test(s);
    }
    function ValidateDate(s) {
        return /^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/.test(s);
    }

    
})(jQuery);
