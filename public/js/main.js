var dataTable, qa_dataTable;

jQuery(function($){
    // デフォルトの設定を変更
    $.extend( $.fn.dataTable.defaults, {
        language: {
            url: "/assets/datatables_js.json"
        }
    });

    $.fn.DataTable.ext.pager.full_numbers_no_ellipses = function(page, pages){
        var numbers = [];
        var buttons = $.fn.DataTable.ext.pager.numbers_length;
        var half = Math.floor( buttons / 2 );

        var _range = function ( len, start ){
            var end;

            if ( typeof start === "undefined" ){
                start = 0;
                end = len;

            } else {
                end = start;
                start = len;
            }

            var out = [];
            for ( var i = start ; i < end; i++ ){ out.push(i); }

            return out;
        };


        if ( pages <= buttons ) {
            numbers = _range( 0, pages );

        } else if ( page <= half ) {
            numbers = _range( 0, buttons);

        } else if ( page >= pages - 1 - half ) {
            numbers = _range( pages - buttons, pages );

        } else {
            numbers = _range( page - half, page + half + 1);
        }

        numbers.DT_el = 'span';

        return [ 'first', 'previous', numbers, 'next', 'last' ];
    };

    dataTable = $("#dataTable2").DataTable({
        "autoWidth": false,
        "aaSorting": [],
        "searching": false,
        "lengthChange": false,
        "pageLength": 50,
        "sPaginationType": "full_numbers_no_ellipses",
        "aoColumnDefs": [
            { targets:3, render:sortable_count },
            { 'bSortable': false, 'aTargets': [1, 4, 5] } ,
            { className: "dt-center dv-center", "aTargets": [1,2,3,4,5] },
        ],
        // "columnDefs" : [
        //
        // ],

    });

    $('#dataTable2').on( 'page.dt', function () {
        $('html, body').animate({
            scrollTop: 100000
        }, 200);
    });

    qa_dataTable = $("#dataTable3").DataTable({
        "sorting": false,
        "autoWidth": false,
        "searching": false,
        "lengthChange": false,
        "pageLength": 20,
        "sPaginationType": "full_numbers_no_ellipses",
    });

    $('#dataTable3').on( 'page.dt', function () {
        // $('page.dt:first-child').focus().blur();

        $('html, body').animate({
            // scrollTop: $(document).height()
            scrollTop: 100000
        }, 200);
    });



    var sortable_count = function(data, type, full, meta) {
        if(type === 'display'){
            return data + "人";
        } else {
            return data;
        }
    };

    $("#search_box .form-control").keypress(function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $('.btn-search').click();
        }
    });

    $(document).on("click", ".search_status", function () {
        var resourceURL = $('#search_url').val();
        var countURL = $('#count_url').val();

        var keyword = '';
        keyword = $('#keyword').val(); // Get the value of input text
        resourceURL = resourceURL.replace(":keyword", keyword); // Build the route
        countURL = countURL.replace(":keyword", keyword); // Build the route

        var type = 0;
        if ($('#chk_1').is(':checked')) {
            type += 1;
        }
        if ($('#chk_2').is(':checked')) {
            type += 2;
        }
        resourceURL = resourceURL.replace(":type", type); // Build the route
        countURL = countURL.replace(":type", type); // Build the route

        var open_status = 0;
        open_status = $(this).data('id');
        resourceURL = resourceURL.replace(":status", open_status); // Build the route
        countURL = countURL.replace(":status", open_status); // Build the route

        /*
        * Change the URL of dataTable and call ajax to load new data
        */
        dataTable.ajax.url(resourceURL).load();
        dataTable.draw();

        $.ajax({
            type: 'GET',
            url: countURL,
            dataType: 'json',
            success: function (data) {
                if (data.success == true) {
                    var html = data.html;
                    $('#count-div').html(html);

                    $('#searchStatus').val(open_status);
                }
            }, error: function (data) {
                console.log(data);
            }
        })
    });

    $(document).on("click", "#search_button", function () {
        var resourceURL = $('#search_url').val();
        var countURL = $('#count_url').val();

        var keyword = '';
        keyword = $('#keyword').val(); //Get the value of input text
        resourceURL = resourceURL.replace(":keyword", keyword); // Build the route
        countURL = countURL.replace(":keyword", keyword); // Build the route

        var type = 0;
        if ($('#chk_1').is(':checked')) {
            type += 1;
        }
        if ($('#chk_2').is(':checked')) {
            type += 2;
        }
        resourceURL = resourceURL.replace(":type", type); // Build the route
        countURL = countURL.replace(":type", type); // Build the route

        var open_status = 0;
        open_status = $('#searchStatus').val();
        resourceURL = resourceURL.replace(":status", open_status); // Build the route
        countURL = countURL.replace(":status", open_status); // Build the route

        /*
        * Change the URL of dataTable and call ajax to load new data
        */
        dataTable.ajax.url(resourceURL).load();
        dataTable.draw();

        $.ajax({
            type: 'GET',
            url: countURL,
            dataType: 'json',
            success: function (data) {
                if (data.success == true) {
                    var html = data.html;
                    $('#count-div').html(html);
                }
            }, error: function (data) {
                console.log(data);
            }
        })
    });

    $(document).on("click", ".recruitPublicModal", function () {
        $("#publicId").val($(this).data('id'));
        $("#publicType").val($(this).data('type'));
        $("#recruit-modal-title").text($(this).data('title'));
        $("#recruit-modal-full_category").text($(this).data('full_category'));
        if ($(this).data('method') == 1) {
            $("#recruit-modal-payment").text('割合（％）');
            $("#recruit-modal-income").text('年収の' + $(this).data('income') + '%');
        } else if ($(this).data('method') == 2) {
            $("#recruit-modal-payment").text('固定報酬');
            $("#recruit-modal-income").text('一律固定報酬 ' + $(this).data('fixed_reward') + '万円');
        }
        // $("#recruit-modal-payment").val($(this).data('payment'));
        $('#agreeRecruitPublicLabel').prop('checked', false);

        $("#recruit-modal-ideal_income").text($(this).data('ideal_income'));
        $("#recruit-modal-refund").text($(this).data('refund'));
    });


    $(document).on("click", ".outsourcePublicModal", function () {
        $("#publicId").val($(this).data('id'));
        $("#publicType").val($(this).data('type'));
        $('#agreeOutsourcePublicLabel').prop('checked', false);
        $("#outsource-modal-title").text($(this).data('title'));
        $("#outsource-modal-unit_price").text($(this).data('unit_price'));
    });


    $(document).on("click", ".stopRecruit", function () {
        var recruitId = $(this).data('id');
        $("#stopRecruitId").val( recruitId );

        var recruitType = $(this).data('type');
        $("#stopType").val( recruitType );
    });

    $(document).on("click", ".qa_search_status", function() {
        $('.qa_search_status').removeClass('qa_search_status_selected');
        $(this).addClass('qa_search_status_selected');
        searchQa();
        searchQaCount();
    });

    $(document).on("change", "#qaSearchSort", function() {
        searchQa();
        searchQaCount();
    });

    $(document).on("change", "#qaSearchType", function() {
        searchQa();
        searchQaCount();
    });


    $(document).on("click", ".answerModal", function () {
        var obj = $(this).parent().parent();

        $("#answerId").val($(this).data('id'));
        $("#answerPersonId").val($(this).data('answer_person_id'));

        $("#answerContent").val($('.answer_content', obj).val());

        if ($('.answer_content', obj).val().length > 0) {
            $('#answerModal').modal('toggle');
        } else {
            alert('回答内容を入力してください')
        }

    });

    $(document).on("click", ".rejectQuestionModal", function () {
        var obj = $(this).parent().parent();
        $("#rejectQuestionId").val($(this).data('id'));
    });

    $('i.ti-calendar').click(function (){
        $(this).parent().parent().find('input[data-provide="datepicker"]').trigger('select');
    });

    $(document).ready(function() {
        if ($( window ).width() < 992 && $('.app').hasClass('is-collapsed')) {
            $('.app').removeClass('is-collapsed');
        }
    });
});

function agreeRecruitPublicModal(obj) {
    if (obj.checked == true) {
        $('#publicRecruitBtn').attr('disabled', false);
    } else {
        $('#publicRecruitBtn').attr('disabled', true);
    }
}

function agreeOutsourcePublicModal(obj) {
    if (obj.checked == true) {
        $('#publicOutsourceBtn').attr('disabled', false);
    } else {
        $('#publicOutsourceBtn').attr('disabled', true);
    }
}

function publicFunc() {
    var recruitId = $("#publicId").val();
    var type = $('#publicType').val();

    if (recruitId && type) {
        $.ajax({
            type: 'GET',
            url: '/company/job_public/' + type + '/' + recruitId,
            dataType: 'json',
            success: function (data) {
                if (data.success == true) {
                    var id = data.id;
                    var type = data.type;

                    var obj = $('[data-id=' + id + '][data-type=' + type + ']');
                    var parent = obj.parent();
                    var tr_element = parent.parent();
                    if (data.open_status == 1) {
                        var html = '公開中';
                        html += '<br>';
                        html += '<a href="#" data-bs-toggle="modal" data-bs-target="#stopModal" class="stopRecruit" data-id="' + id + '" data-type="' + type + '">募集停止</a>';
                        parent.html(html);
                        $('.job_list_edit', tr_element).addClass('job_list_disabled');
                    } else if (data.open_status == 2) {
                        var html = '申請中';
                        parent.html(html);
                        $('.job_list_edit', tr_element).addClass('job_list_disabled');
                    }

                }
            }, error: function (data) {
                console.log(data);
            }
        })
    }
    if (type == 'J') {
        $('#recruitPublicModal').modal('toggle');
    } else {
        $('#outsourcePublicModal').modal('toggle');
    }

}

function stopFunc() {
    var recruitId = $("#stopRecruitId").val();
    var type = $('#stopType').val();

    if (recruitId && type) {
        $.ajax({
            type: 'GET',
            url: '/company/job_stop/' + type + '/' + recruitId,
            dataType: 'json',
            success: function (data) {
                if (data.success == true) {
                    var id = data.id;
                    var type = data.type;

                    var obj = $('[data-id=' + id + '][data-type=' + type + ']');
                    var parent = obj.parent();
                    var tr_element = parent.parent();

                    var html = '募集停止中';
                    html += '<br>';
                    if (data.type == 'J') {
                        html += '<a href="#" data-bs-toggle="modal" data-bs-target="#recruitPublicModal" data-id="' + data.id + '" data-type="' + data.type + '" data-title="' + data.title + '" data-full_category="' + data.full_category + '" data-method="' + data.method + '" data-income="' + data.income + '" data-ideal_income="' + data.ideal_income + '" data-refund="' + data.refund + '" data-fixed_reward ="' + data.fixed_reward  + '" class="recruitPublicModal">公開</a>';
                    } else if (data.type == 'G') {
                        html += '<a href="#" data-bs-toggle="modal" data-bs-target="#outsourcePublicModal" data-id="' + data.id + ' data-type="' + data.type + '" data-title="' + data.title + '" data-full_category="' + data.full_category + '" data-unit_price="' + data.unit_price + '" class="outsourcePublicModal">公開</a>';
                    }
                    parent.html(html);
                    $('.job_list_edit', tr_element).removeClass('job_list_disabled');
                }
            }, error: function (data) {
                console.log(data);
            }
        })
    }

    $('#stopModal').modal('toggle');
}

function answerFunc() {
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        /* the route pointing to the post function */
        url: '/company/qa_answer',
        type: 'POST',
        /* send the csrf-token and the input to the controller */
        data: {_token: CSRF_TOKEN, id:$("#answerId").val(), answer_person_id:$("#answerPersonId").val(), answer_content:$('#answerContent').val()},
        dataType: 'JSON',
        /* remind that 'data' is the response of the AjaxController */
        success: function (data) {
            var id = data.id;

            var td_container = $('#td-' + id);

            if (data.success) {
                $('input[type=checkbox]', td_container).attr('checked', false);
                $('.qa_title_status_panel', td_container).removeClass('qa_title_status_panel_waiting').addClass('qa_title_status_panel_finish');
                $('.qa_title_status_panel', td_container).text('回答済み');
                $('.qa_answer_item_datetime', td_container).text('回答日：' + data.answer_datetime);

                var html = $('.qa_answer_item_container',td_container).html();
                html += '<p class="qa_contents_item_text">' + data.answer_content + '</p>';
                $('.qa_answer_item_container',td_container).html(html);

                $('.qa_contents_item_warning', td_container).remove();
                $('.qa_contents_item_input', td_container).remove();

                // searchQa();
                searchQaCount();
            }
        }
    });

    $('#answerModal').modal('toggle');
}

function rejectFunc() {
    var id = $('#rejectQuestionId').val();
    if (id) {
        $.ajax({
            type: 'GET',
            url: '/company/qa_reject/' + id,
            dataType: 'json',
            success: function (data) {


                if (data.success == true) {
                    var id = data.id;
                    var td_container = $('#td-' + id);

                    $('.qa_contents_item_warning', td_container).remove();
                    $('.qa_contents_item_input', td_container).remove();

                }
            }, error: function (data) {
                console.log(data);
            }
        })
    }

    $('#rejectQuestionModal').modal('toggle');
}

function countChar(val) {
    var parent = val.parentElement;
    var len = val.value.length;

    // if (len > 0) {
    //     $('.answerModal', parent).data('bs-toggle', 'modal');
    //     $('.answerModal', parent).data('bs-target', '#answerModal');
    // } else {
    //     $('.answerModal', parent).data('bs-toggle', 'modal');
    //     $('.answerModal', parent).data('bs-target', '#answerModal');
    // }

    if (len >= 2000) {
        val.value = val.value.substring(0, 2000);
        $('.qa_contents_item_text_count_span', parent).text(2000);
    } else {
        $('.qa_contents_item_text_count_span', parent).text(len);
    }
}

function onImgErr(elm, type) {
    if (type == undefined || type < 1 || type > 4) {
        elm.src = "/assets/static/images/no_image.png";
        return;
    }
    elm.src = "/assets/static/images/avatar/no_image"+type+".png";
}

function searchQa() {
    var resourceURL = $('#search_url').val();

    var sort = $('#qaSearchSort').val();
    var type = $('#qaSearchType').val();
    var status = $('.qa_search_status_selected').data('id');

    resourceURL = resourceURL.replace(":sort", sort); // Build the route
    resourceURL = resourceURL.replace(":type", type); // Build the route
    resourceURL = resourceURL.replace(":status", status); // Build the route

    /*
    * Change the URL of dataTable and call ajax to load new data
    */
    qa_dataTable.ajax.url(resourceURL).load();
    qa_dataTable.draw();

}

function searchQaCount() {
    var countURL = $('#count_url').val();

    var sort = $('#qaSearchSort').val();
    var type = $('#qaSearchType').val();
    var status = $('.qa_search_status_selected').data('id');

    countURL = countURL.replace(":sort", sort); // Build the route
    countURL = countURL.replace(":type", type); // Build the route
    countURL = countURL.replace(":status", status); // Build the route

    //
    $.ajax({
        type: 'GET',
        url: countURL,
        dataType: 'json',
        success: function (data) {
            if (data.success == true) {
                var html = data.html;
                $('#count-div').html(html);
            }
        }, error: function (data) {
            console.log(data);
        }
    })
}

function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
        /*check if the item starts with the same letters as the text field value:*/
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) {
              /*insert the value for the autocomplete text field:*/
              inp.value = this.getElementsByTagName("input")[0].value;
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
        }
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }
  /*execute a function when someone clicks in the document:*/
  document.addEventListener("click", function (e) {
    closeAllLists(e.target);
  });
}
