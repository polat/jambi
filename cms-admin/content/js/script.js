$(document).ready(function () {
    // Date Input
    var inputDate = $('input.date').not('.start, .end');

    inputDate.each(function () {
        $(this).datepicker({
            dateFormat: 'dd.mm.yy',
            minDate: $(this).attr('data-minDate')
        });
    });

    inputDate.keypress(function (e) {
        if (e.keyCode !== 8 && ($(this).val().length === 2 || $(this).val().length === 5)) {
            $(this).val($(this).val() + ".");
        }
    });

    // Time Input
    $('input.time[value=""]').each(function(){
        var d = new Date(),
            h = d.getHours(),
            m = d.getMinutes();

        if(h < 10) h = '0' + h;
        if(m < 10) m = '0' + m;

        $(this).attr({
            'value': h + ':' + m
        });
    });

    // Price Input
    $('input.price').maskMoney({thousands:'', decimal:'.'});

    // Date Range Input Initialize
    function dateRangePickersInitialize() {
        $("input.date.start").datepicker($.extend({
            onSelect: function () {
                var minDate = $(this).datepicker('getDate');
                minDate.setTime(minDate.getTime() + (1000 * 60 * 60 * 24));
                minDate.setDate(minDate.getDate());

                $(this).siblings('.end').datepicker('option', 'minDate', minDate);
                $(this).siblings('.end').datepicker('option', 'disabled', false);

                var endMinDate = minDate.getDate() + "." + (minDate.getMonth() + 1) + "." + minDate.getFullYear();
                $(this).siblings('.end').attr('placeholder', endMinDate);
            },
            onClose: function () {
                $(this).siblings('.end').focus();
            }
        }, {dateFormat: 'dd.mm.yy', numberOfMonths: 2, minDate: $(this).attr('data-minDate')}));

        $("input.date.end").datepicker($.extend({
            disabled: false,
            onClose: function () {
                var start = $(this).siblings('.start').datepicker('getDate');
                var end = $(this).datepicker('getDate');

                if (start === null) {
                    start = new Date();
                    start.setHours(0, 0, 0, 0);
                }

                var currentDate = new Date(start);
                var currentDay, currentMonth;
                var between = [];

                while (currentDate <= end) {
                    currentDay = currentDate.getDate();
                    currentMonth = currentDate.getMonth() + 1;

                    if (currentDay < 10) {
                        currentDay = '0' + currentDay;
                    }

                    if (currentMonth < 10) {
                        currentMonth = '0' + currentMonth;
                    }

                    between.push(currentDay + '.' + currentMonth + '.' + currentDate.getFullYear());
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                // Set between dates and number of these days
                $(this).siblings('.between_dates').val(between);
                $(this).siblings('.data_number').val(between.length);
            }
        }, {dateFormat: 'dd.mm.yy', numberOfMonths: 2, minDate: $(this).attr('data-minDate')}));
    }

    dateRangePickersInitialize();

    // Duplicate Dynamic Fields
    $(document).on('click', ".dynamic_fieldset_append:not('.delete')", function (e) {
        var clicked_button = $(this);
        var dateInputs = clicked_button.parents('li').find("input.date");
        dateInputs.datepicker("destroy");
        var cloneElem = clicked_button.parent().clone();
        dateInputs.not('.start, .end').datepicker({dateFormat: 'dd.mm.yy', minDate: $(this).attr('data-minDate')});

        // File input
        if (cloneElem.find("input.file").length) {
            var fileElem = clicked_button.parents("li").find("input.file");
            var fileLength = fileElem.length + 1;

            fileElem.each(function (index) {
                cloneElem
                    .find("input.file")
                    .eq(index)
                    .attr('id', $(this).attr('data-id') + '-' + (fileLength + index))
                    .siblings('a.filemanager-iframe').attr('href', $(this).siblings('a.filemanager-iframe').attr('data-href') + '-' + (fileLength + index));
            });
        }

        // Price
        if (cloneElem.find('input.price').length) {
            cloneElem
                .find('input.price').each(function () {
                $(this).maskMoney({thousands:'', decimal:'.'});
            });
        }

        // Date
        if (cloneElem.find('input.date').not('.start, .end').length) {
            cloneElem
                .find('input.date').not('.start, .end').each(function () {
                $(this)
                    .attr('id', Math.random())
                    .datepicker({dateFormat: 'dd.mm.yy', minDate: $(this).attr('data-minDate')});
            });
        }

        // dateRange
        if (cloneElem.find('input.date.start, input.date.end').length) {
            cloneElem
                .find('input.date.start, input.date.end').each(function () {
                $(this)
                    .attr('id', Math.random())
            });
        }

        // Radio
        if (cloneElem.find("input[type='radio']").length) {
            var radioContainer = clicked_button.parents("li").find(".radioListContainer");
            var radioLength = radioContainer.length + 1;
            var radioName = radioContainer.find('input[type="radio"]:first').attr('data-name');

            cloneElem
                .find("input[type='radio']").removeAttr('checked')
                .attr('name', radioName + '[][' + radioLength + ']').first().trigger('click').attr('checked', true)
        }

        // Checkbox
        if (cloneElem.find("input[type='checkbox']").length) {
            var checkboxContainer = clicked_button.parents("li").find(".checkboxListContainer");
            var checkboxLength = checkboxContainer.length + 1;
            var checkboxName = checkboxContainer.find('input[type="checkbox"]:first').attr('data-name');

            cloneElem
                .find("input[type='checkbox']").removeAttr('checked')
                .attr('name', checkboxName + '[][' + checkboxLength + ']').first().trigger('click').attr('checked', true)
        }

        cloneElem
            .find("*").not("input[type='radio'], input[type='checkbox'], select, option").val('')
            .parents(".dynamic_fieldset").appendTo(clicked_button.parent().parent())
            .find(".dynamic_fieldset_append").addClass("delete");

        // Re-Initialize Datepickers
        dateRangePickersInitialize();
    });

    // Delete Dynamic Fields
    $(document).on('click', '.dynamic_fieldset_append.delete', function () {
        var removeData = $(this);
        $(".metabox_alert").addClass("active");

        $(".metabox_alert_yes").click(function () {
            removeData.parent().remove();
            $(".metabox_alert").removeClass("active");
        });

        $(".metabox_alert_no").click(function () {
            $(".metabox_alert").removeClass("active");
        });
    });

    // Sortable to Dynamic Fields
    $('.dynamic_fieldset').parent().sortable();

    // Toggle Metaboxes
    $(".metabox_trigger").click(function () {
        $(this).toggleClass("active").siblings(".metabox_content").stop().slideToggle();
    });

    /* Record Form - Submit [Publish & Draft] */
    var recordForm = $("#recordForm");

    $("#form-submit").click(function () {
        recordForm.find('input[name="rec_status"]').val(0);

        var multipleDate = $('.multipleDate');

        if (multipleDate.length > 0) {
            multipleDate.each(function () {
                var elemID = $(this).attr('id');
                var dates = $('#' + elemID).multiDatesPicker('getDates');

                $("input[name='" + elemID + "']").val(dates).promise().done(function () {
                    recordForm.submit();
                });
            })
        } else {
            recordForm.submit();
        }
    });

    $("#form-submit-draft").click(function () {
        recordForm.find('input[name="rec_status"]').val(1);

        var multipleDate = $('.multipleDate');

        if (multipleDate.length > 0) {
            multipleDate.each(function () {
                var elemID = $(this).attr('id');
                var dates = $('#' + elemID).multiDatesPicker('getDates');

                $("input[name='" + elemID + "']").val(dates).promise().done(function () {
                    recordForm.submit();
                });
            })
        } else {
            recordForm.submit();
        }
    });

    // DataTable Settings
    $('#dataTable').dataTable({
        "sDom": '<"tools"><"clear">',
        "aoColumnDefs": [
            {'bSortable': false, 'aTargets': [0, 1, 2, 3]}
        ],
        "iDisplayLength": -1,
        "aaSorting": []
    });


    // Delete Handles
    var selectedList = [];

    $("#selectAll").on("click", function () {
        if ($(this).hasClass('fa-square-o')) {
            $("#selectAll, .selectBox").removeClass("fa-square-o").addClass("fa-check");
            $('.tableButtons a').addClass('active');
        } else {
            $("#selectAll, .selectBox").removeClass("fa-check").addClass("fa-square-o");
            $('.tableButtons a').removeClass('active');
        }

        $(".selectBox").each(function () {
            var dataID = $(this).parents('tr').attr('data-id');
            var id = $.inArray(dataID, selectedList);

            if (id === -1) {
                selectedList.push(dataID);
            } else {
                selectedList.splice(id, 1);
            }

            $("input#selectedRecordList").val(selectedList);
        });
    });

    $('#dataTable').on('click', '.selectBox', function () {
        if ($(this).hasClass('fa-square-o')) {
            $(this).removeClass('fa fa-square-o').addClass('fa fa-check');
        } else {
            $(this).removeClass('fa fa-check').addClass('fa fa-square-o');
        }

        if ($('#dataTable .selectBox.fa-check').length > 0) {
            $('.tableButtons a.fa-square-o').removeClass('fa-square-o').addClass('fa-check');
            $('.tableButtons a.fa-trash-o, .tableButtons a.fa-square-o').addClass('active');
        } else {
            $('.tableButtons a.fa-check').removeClass('fa-check').addClass('fa-square-o');
            $('.tableButtons a.fa-trash-o, .tableButtons a.fa-square-o').removeClass('active');
        }

        var dataID = $(this).parents('tr').attr('data-id');
        var id = $.inArray(dataID, selectedList);

        if (id === -1) {
            selectedList.push(dataID);
        } else {
            selectedList.splice(id, 1);
        }

        $("input#selectedRecordList").val(selectedList);
    });

    // Single Delete Process
    $('a.singleDeleteRecord').click(function () {
        if (confirm('Kayıt silinecek. Emin misiniz?')) {
            var id = [];
            var elem = $(this).parents('tr');
            id.push(elem.attr('data-id'));
            $("form#selectedRecordListForm #selectedRecordList").val(id);

            var type = $("#recordListForm #dataTable").attr('data-delete-type');

            request = $.ajax({
                url: window.location.protocol + "//" + window.location.host + "/" + BASEURL + "/cms-admin/controller/modules/default/handle/" + type + ".php",
                type: "post",
                data: $('form#selectedRecordListForm').serialize(),
                success: function (response) {
                    var result = jQuery.parseJSON(response);

                    if (result.result === true) {
                        location.reload();
                    }
                }
            });
        }
    });

    // Multiple Delete Process
    $("#multipleDeleteRecord").click(function () {
        if ($("input#selectedRecordList").val()) {
            if (confirm('Seçtiğiniz kayıtlar silinecek. Emin misiniz?')) {
                var deleteType = $("#recordListForm #dataTable").attr('data-delete-type');

                request = $.ajax({
                    url: window.location.protocol + "//" + window.location.host + BASEURL + "/cms-admin/controller/modules/default/handle/" + deleteType + ".php",
                    type: "post",
                    data: $('form#selectedRecordListForm').serialize(),
                    success: function (response) {
                        var result = jQuery.parseJSON(response);

                        if (result.result === true) {
                            location.reload();
                        }
                    }
                });
            }
        }
    });

    // Restore Record Process
    $('a.restoreRecord').click(function () {
        if (confirm('Kayıt geri alınacak. Emin misiniz?')) {
            var id = $(this).parents('tr').attr('data-id');
            var module = $(this).parents('table').attr('data-module');

            request = $.ajax({
                url: window.location.protocol + "//" + window.location.host + "/" + BASEURL + "/cms-admin/controller/modules/default/handle/restore.php",
                type: "post",
                data: {"id": id, "module": module},
                success: function (response) {
                    var result = jQuery.parseJSON(response);

                    if (result.result === true) {
                        location.reload();
                    }
                }
            });
        }
    });

    // Copy Process
    $('a.copyRecord').click(function () {
        if (confirm('Kayıt kopyalanacak. Emin misiniz?')) {
            var id = [];
            var elem = $(this).parents('tr');
            id.push(elem.attr('data-id'));
            $("form#selectedRecordListForm #selectedRecordList").val(id);

            request = $.ajax({
                url: window.location.protocol + "//" + window.location.host + "/" + BASEURL + "/cms-admin/controller/modules/default/handle/copy.php",
                type: "post",
                data: $('form#selectedRecordListForm').serialize(),
                success: function (response) {
                    var result = jQuery.parseJSON(response);

                    if (result.result === true) {
                        location.reload();
                    }
                }
            });
        }
    });

    // Sorting Records
    $('#dataTable tbody').sortable({
        beforeStop: function (event, ui) {
            if (ui.helper.parent().hasClass('sortableTable')) {
                request = $.ajax({
                    url: window.location.protocol + "//" + window.location.host + "/" + BASEURL + "/cms-admin/controller/modules/default/handle/sort.php",
                    type: "post",
                    data: $('form#recordListForm').serialize()
                });
            }
        }
    }).disableSelection();

    // Backward & Forward Sorting
    $("#transferRecordsBackward, #transferRecordsForward").click(function () {
        if ($("input#selectedRecordList").val()) {
            var transferType = $(this).attr('data-transfer-type');
            $('form#selectedRecordListForm input[name="recordTransferType"]').val(transferType);

            request = $.ajax({
                url: window.location.protocol + "//" + window.location.host + "/" + BASEURL + "/cms-admin/controller/modules/default/handle/sort.php",
                type: "post",
                data: $('form#selectedRecordListForm').serialize(),
                success: function (response) {
                    var result = jQuery.parseJSON(response);

                    if (result.result === true) {
                        location.reload();
                    }
                }
            });
        }
    });

    // Pagination Change
    $('select#paginationList').on('change', function () {
        window.location.replace($(this).val());
    })

    // File Input delete
    $('a.removeFile').click(function () {
        if ($(this).siblings('input').val()) {
            if (confirm('Dosya silinecek. Emin misiniz?')) {
                $(this).siblings("input").val(null);
            }
        }
    });

    /* Filamanager Iframe */
    $('.filemanager-iframe').fancybox({
        width: 900,
        height: 570,
        type: 'iframe',
        fitToView: false,
        autoSize: false
    });

    /* Permalink Options */
    $(".permalink").each(function () {
        var permalink = $(this);
        var permalinkName = $(this).attr('name');
        var permalinkRel = $(this).attr('rel');
        var recordTypeValue = $('input[name="recordType"]:checked').val();

        var handler = function () {
            $("input[name='" + permalinkName + "']").val(slugify($(this).val()));
        };

        function permalinkSwichTrue(elem, elem2) {
            elem.attr("readonly", true);
            $("input[name='" + permalinkRel + "']").bind('keypress keydown keyup change', elem2);
        }

        function permalinkSwichFalse(elem, elem2) {
            elem.attr("readonly", false);
            $("input[name='" + permalinkRel + "']").unbind('keypress keydown keyup change', elem2);
        }

        if (recordTypeValue != 'link') {
            permalinkSwichTrue(permalink, handler);
        } else {
            permalinkSwichFalse(permalink, handler);
        }

        permalink.dblclick(function () {
            permalinkSwichFalse(permalink, handler);
        });

        // Record Type
        $('input[name="recordType"]').on('change', function () {
            if ($(this).val() != 'link') {
                permalinkSwichTrue(permalink, handler);
            } else {
                permalinkSwichFalse(permalink, handler);
            }
        });
    });

    function slugify(s, opt) {
        s = String(s);
        opt = Object(opt);

        var defaults = {
            'delimiter': '-',
            'limit': undefined,
            'lowercase': true,
            'replacements': {},
            'transliterate': (typeof(XRegExp) === 'undefined') ? true : false
        };

        // Merge options
        for (var k in defaults) {
            if (!opt.hasOwnProperty(k)) {
                opt[k] = defaults[k];
            }
        }

        var char_map = {
            // Latin
            'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A', 'Æ': 'AE', 'Ç': 'C',
            'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E', 'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I',
            'Ð': 'D', 'Ñ': 'N', 'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O', 'Ő': 'O',
            'Ø': 'O', 'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U', 'Ű': 'U', 'Ý': 'Y', 'Þ': 'TH',
            'ß': 'ss',
            'à': 'a', 'á': 'a', 'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a', 'æ': 'ae', 'ç': 'c',
            'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e', 'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i',
            'ð': 'd', 'ñ': 'n', 'ò': 'o', 'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o', 'ő': 'o',
            'ø': 'o', 'ù': 'u', 'ú': 'u', 'û': 'u', 'ü': 'u', 'ű': 'u', 'ý': 'y', 'þ': 'th',
            'ÿ': 'y',

            // Latin symbols
            '©': '(c)',

            // Greek
            'Α': 'A', 'Β': 'B', 'Γ': 'G', 'Δ': 'D', 'Ε': 'E', 'Ζ': 'Z', 'Η': 'H', 'Θ': '8',
            'Ι': 'I', 'Κ': 'K', 'Λ': 'L', 'Μ': 'M', 'Ν': 'N', 'Ξ': '3', 'Ο': 'O', 'Π': 'P',
            'Ρ': 'R', 'Σ': 'S', 'Τ': 'T', 'Υ': 'Y', 'Φ': 'F', 'Χ': 'X', 'Ψ': 'PS', 'Ω': 'W',
            'Ά': 'A', 'Έ': 'E', 'Ί': 'I', 'Ό': 'O', 'Ύ': 'Y', 'Ή': 'H', 'Ώ': 'W', 'Ϊ': 'I',
            'Ϋ': 'Y',
            'α': 'a', 'β': 'b', 'γ': 'g', 'δ': 'd', 'ε': 'e', 'ζ': 'z', 'η': 'h', 'θ': '8',
            'ι': 'i', 'κ': 'k', 'λ': 'l', 'μ': 'm', 'ν': 'n', 'ξ': '3', 'ο': 'o', 'π': 'p',
            'ρ': 'r', 'σ': 's', 'τ': 't', 'υ': 'y', 'φ': 'f', 'χ': 'x', 'ψ': 'ps', 'ω': 'w',
            'ά': 'a', 'έ': 'e', 'ί': 'i', 'ό': 'o', 'ύ': 'y', 'ή': 'h', 'ώ': 'w', 'ς': 's',
            'ϊ': 'i', 'ΰ': 'y', 'ϋ': 'y', 'ΐ': 'i',

            // Turkish
            'Ş': 'S', 'İ': 'I', 'Ç': 'C', 'Ü': 'U', 'Ö': 'O', 'Ğ': 'G',
            'ş': 's', 'ı': 'i', 'ç': 'c', 'ü': 'u', 'ö': 'o', 'ğ': 'g',

            // Russian
            'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo', 'Ж': 'Zh',
            'З': 'Z', 'И': 'I', 'Й': 'J', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N', 'О': 'O',
            'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C',
            'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sh', 'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu',
            'Я': 'Ya',
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh',
            'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o',
            'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c',
            'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu',
            'я': 'ya',

            // Ukrainian
            'Є': 'Ye', 'І': 'I', 'Ї': 'Yi', 'Ґ': 'G',
            'є': 'ye', 'і': 'i', 'ї': 'yi', 'ґ': 'g',

            // Czech
            'Č': 'C', 'Ď': 'D', 'Ě': 'E', 'Ň': 'N', 'Ř': 'R', 'Š': 'S', 'Ť': 'T', 'Ů': 'U',
            'Ž': 'Z',
            'č': 'c', 'ď': 'd', 'ě': 'e', 'ň': 'n', 'ř': 'r', 'š': 's', 'ť': 't', 'ů': 'u',
            'ž': 'z',

            // Polish
            'Ą': 'A', 'Ć': 'C', 'Ę': 'e', 'Ł': 'L', 'Ń': 'N', 'Ó': 'o', 'Ś': 'S', 'Ź': 'Z',
            'Ż': 'Z',
            'ą': 'a', 'ć': 'c', 'ę': 'e', 'ł': 'l', 'ń': 'n', 'ó': 'o', 'ś': 's', 'ź': 'z',
            'ż': 'z',

            // Latvian
            'Ā': 'A', 'Č': 'C', 'Ē': 'E', 'Ģ': 'G', 'Ī': 'i', 'Ķ': 'k', 'Ļ': 'L', 'Ņ': 'N',
            'Š': 'S', 'Ū': 'u', 'Ž': 'Z',
            'ā': 'a', 'č': 'c', 'ē': 'e', 'ģ': 'g', 'ī': 'i', 'ķ': 'k', 'ļ': 'l', 'ņ': 'n',
            'š': 's', 'ū': 'u', 'ž': 'z'
        };

        // Make custom replacements
        for (var k in opt.replacements) {
            s = s.replace(RegExp(k, 'g'), opt.replacements[k]);
        }

        // Transliterate characters to ASCII
        if (opt.transliterate) {
            for (var k in char_map) {
                s = s.replace(RegExp(k, 'g'), char_map[k]);
            }
        }

        // Replace non-alphanumeric characters with our delimiter
        var alnum = (typeof(XRegExp) === 'undefined') ? RegExp('[^a-z0-9]+', 'ig') : XRegExp('[^\\p{L}\\p{N}]+', 'ig');
        s = s.replace(alnum, opt.delimiter);

        // Remove duplicate delimiters
        s = s.replace(RegExp('[' + opt.delimiter + ']{2,}', 'g'), opt.delimiter);

        // Truncate slug to max. characters
        s = s.substring(0, opt.limit);

        // Remove delimiter from ends
        s = s.replace(RegExp('(^' + opt.delimiter + '|' + opt.delimiter + '$)', 'g'), '');

        return opt.lowercase ? s.toLowerCase() : s;
    }

    /* Character Limit Counter */
    function characterLimitCounter(elem) {
        var characterLimit = elem.attr('data-limit-counter');
        var value = elem.val().replace(/\s\s+/g, ' ');
        var count = characterLimit - value.length;
        var elemSpan = elem.parents('.fieldset').find('span.limit_counter');

        elemSpan.html(count);

        if (count < 0) {
            elemSpan.removeClass('valid').addClass('wrong');
        } else {
            elemSpan.removeClass('wrong').addClass('valid');
        }
    }

    var characterLimitElem = $('*[data-limit-counter]');

    characterLimitElem.each(function () {
        characterLimitCounter($(this));
    });

    characterLimitElem.keyup(function () {
        characterLimitCounter($(this));
    });

    /* System Labels Type Select */
    function setLabelType() {
        $('.tab-pane').each(function () {
            var lang = $(this).attr('id');
            var type = $('.tab-content.system_labels input[name="type"]:checked').val();

            if (type === 'content') {
                $(this).find('input[name="file' + lang + '"]').parents().eq(2).hide();
                $(this).find('textarea[name="content' + lang + '"]').parents().eq(1).show();
            } else if (type === 'file') {
                $(this).find('textarea[name="content' + lang + '"]').parents().eq(1).hide();
                $(this).find('input[name="file' + lang + '"]').parents().eq(2).show();
            }
        });
    }

    setLabelType();

    $('div.system_labels input[name="type"]').change(function () {
        setLabelType();
    });
});

/* Tinymce Editor */
tinymce.init({
    height: 340,
    editor_selector: "editor",
    mode: "specific_textareas",
    fontsize_formats: "10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 21pt 22pt 23pt 24pt 25pt 26pt 27pt 28pt 29pt 30pt 31pt 32pt 33pt 34pt 35pt 36pt 37pt 38pt 39pt 40pt",
    plugins: [
        "advlist autolink lists link image charmap print",
        "searchreplace wordcount visualblocks visualchars code",
        "insertdatetime save table contextmenu directionality",
        "paste textcolor colorpicker textpattern responsivefilemanager fullscreen"
    ],
    menubar: "",
    formats: {
        bold: {
            inline: 'b'
        }
    },
    toolbar1: "undo redo | responsivefilemanager link | table | styleselect | fontsizeselect forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | code | fullscreen",
    image_advtab: true,
    valid_elements: '*[*]',
    convert_urls: false,
    entity_encoding: "raw",
    external_filemanager_path: JAMBI_ADMIN_CONTENT + "plugins/filemanager/",
    filemanager_title: "Dosya Yöneticisi",
    external_plugins: {"filemanager": JAMBI_ADMIN_CONTENT + "plugins/filemanager/plugin.min.js"},
    branding: false,
    valid_children: '+a[div],+a[p]'
});