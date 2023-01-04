var dataTableOutsource;
var currentJobSeeker;
var currentOutsourceUser;
var currentKeyword;
var currentStatus = 8;
var currentSort = 1; // 1: Desc, 2:Asc
var g_paginationClicked = false;

jQuery(function($){

    function ensureValue(value) {
        return value == null ? ' ' : value;
    }

    function getRenderString(data, record, date, refusalDate, sendOffDate) {
        if (data == null) return 'ー';
        switch(data) {
            case 1:
                return '<div class="padding-box"><p><em class="green">〇</em>&nbspオファー</p><span>' + ensureValue(date) + '</span></div>';
            case 2:
                return '<div class="padding-box"><p><em class="green">〇</em>&nbsp成約</p><span>' + ensureValue(date) + '</span></div>';
            case 3:
                return '<div class="padding-box"><p><em class="green">〇</em>&nbsp通過</p><span>' + ensureValue(date) + '</span></div>';
            case 4:
                return '<div class="padding-box"><p><em class="gray">✖</em>&nbsp辞退</p><span>' + ensureValue(refusalDate) + '</span></div>';
            case 5:
                return '<div class="padding-box"><p><em class="gray">✖</em>&nbsp見送り</p><span>' + ensureValue(sendOffDate) + '</span></div>';
            case 6:
                if (record.is_pending) {
                    return '<div class="first-line"><p>日程待ち</p></div><div class="second-line bg-blue">面談日程<br>未確定</div>';
                    // return '<div class="padding-box"><p>面談実施待ち</p></div>';
                }
                return '<div class="first-line"><p class="red">！要対応</p></div><div class="second-line bg-blue">面談日程<br>未確定</div>';
            case 7:
                return '<div class="padding-box"><p>面談設定済み</p><span>' + ensureValue(date) + '</span></div>';
            case 8:
                return '<div class="first-line"><p class="red">！要対応</p></div><div class="second-line bg-blue">選考結果<br>未送付</div>';
            default:
                return 'ー';
        }
    }

    // デフォルトの設定を変更
    $.extend( $.fn.dataTable.defaults, {
        language: {
            url: "/assets/datatables_js.json"
        }
    });

    dataTableOutsource = $("#dataTable-outsource").DataTable({
        ajax: {
            url: g_baseURL + "/ses/apply/datatable",
            type: 'post',
            data: function(data, settings) {
                data.extra = {
                    job_seeker : currentJobSeeker,
                    outsource_user_id: currentOutsourceUser,
                    keyword : currentKeyword,
                    status: currentStatus,
                    order: $(".sort_outsource").val(),
                }
            },
            "dataSrc": function ( json ) {
                $("#status_count").html(json.statusCountHtml);
                return json.data;
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        },
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: true,
        bSort : false,
        searching: false,
        "bLengthChange": false,
        pageLength: 50,
        sPaginationType: "full_numbers_no_ellipses",
        /*scrollY: "300px",*/
        scrollCollapse: true,
        fixedHeader: true,
        fixedColumns: {
            leftColumns: 1,
        },
        createdRow: function (row, data, index, cells) {
            // if the status column cell is set, apply special formatting
            //1:選考中
            if (data.selection_status == 1) {
                let childCount = $(row).children().length;
                let bAction = false;
                for (i=0; i<childCount; i++) {
                    if ($(cells[i]).text().includes('！要対応')) {
                        bAction = true;
                        break;
                    }
                }
                if (bAction) {
                    $(row).addClass('label-danger');
                }
                else {
                    $(row).addClass('label-info');
                }
            }
            //2:見送り/辞退
            else if (data.selection_status == 2) {
                // todo nothing
            }
            //3:内定(参画開始待ち)
            else if (data.selection_status == 3) {
                $(row).addClass('label-green');
            }
            //4:参画中
            else if (data.selection_status == 4) {
                $(row).addClass('label-orange');
            }
            //5:参画終了
            else if (data.selection_status == 5) {
                // todo nothing
            }
        },
        columnDefs: [
            {
                "targets": 0,
                orderable: false,
                "render": function ( data, type, row, meta ) {
                    return '<div class="padding-box"><a href="#/jobseeker/outsource/' + row.job_seeker_id + '"><p>' + (row.initial!=null ? row.initial : 'ー') + '</p><span>' + row.birthday + '歳／' + row.sex + '</span></a>' + 
                    (row.selection_status == 1 ? '<p>提案単価</p><a href="' + g_baseURL + '/ses/apply/' + row.id + '"><span class="blue">' + (row.proposal_unit_price!=null ? row.proposal_unit_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '-') + '円/月' + '</span>' : '') + '</div>';
                }
            },
            {
                "targets": 1,
                "render": function ( data, type, row, meta ) {
                    return '<div class="padding-box fixed-w200"><a href="#/ses/G/' + row.recruiting_company_id + '">' + row.recruiting_company_name + '</a>' + 
                        '<p>' + row.outsource_user_name + '</p></div>';
                }
            },
            {
                "targets": 2,
                "render": function ( data, type, row, meta ) {
                    return '<div class="padding-box fixed-w200" data-id="' + row.id +'">'
                        + '<a href="' + g_baseURL + '/ses/job_show/G/' + row.outsource_offer_info_id + '">' + row.job_title + '</a><br>'
                        + '<a class="link-detail" href="' + g_baseURL + '/ses/job_list?cat=' + row.occupation_category_2 + '">' + row.occupation_category_2 + '</a></div>';
                }
            },
            {
                "targets": 3,
                "render": function ( data, type, row, meta ) {
                    return data != 1 ? '' : '<div class="padding-box"><p><em class="green">〇</em>&nbsp;エントリー</p><span>' + ensureValue(row.proposal_date) + '</span></div>';
                }
            },
            {
                "targets": 4,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 2 ? 'ー' : '';
                    }
                    if (data == 1) return '<div class="padding-box"><p><em class="green">〇</em>&nbsp;確認済</p><span>' + ensureValue(row.document_confirmation_date) + '</span></div>';
                    return '<div class="first-line"><p class="red">！要対応</p></div><div class="second-line bg-blue">書類<br>未確認</div>';
                }
            },
            {
                "targets": 5,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 3 ? 'ー' : '';
                    }
                    switch(data) {
                        case 1:
                            return '<div class="padding-box"><p><em class="green">〇</em>&nbspオファー</p><span>' + ensureValue(row.applicant_screening_date) + '</span></div>';
                        case 2:
                            return '<div class="padding-box"><p><em class="green">〇</em>&nbsp成約</p><span>' + ensureValue(row.applicant_screening_date) + '</span></div>';
                        case 3:
                            return '<div class="padding-box"><p><em class="green">〇</em>&nbsp通過</p><span>' + ensureValue(row.applicant_screening_date) + '</span></div>';
                        case 4:
                            return '<div class="padding-box"><p><em class="gray">✖</em>&nbsp辞退</p><span>' + ensureValue(row.applicant_screening_refusal_reason_date) + '</span></div>';
                        case 5:
                            return '<div class="padding-box"><p><em class="gray">✖</em>&nbsp見送り</p><span>' + ensureValue(row.applicant_screening_send_off_date) + '</span></div>';
                        default:
                            return '<div class="first-line"><p class="red">！要対応</p></div><div class="second-line bg-blue">選考結果<br>未送付</div>';
                    }
                }
            },
            {
                "targets": 6,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 4 ? 'ー' : '';
                    }
                    return getRenderString(data, row, row['1st_interview_date'], row['1st_refusal_reason_date'], row['1st_send_off_date']);
                }
            },
            {
                "targets": 7,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 5 ? 'ー' : '';
                    }
                    return getRenderString(data, row, row['2nd_interview_date'], row['2nd_refusal_reason_date'], row['2nd_send_off_date']);
                }
            },
            {
                "targets": 8,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 6 ? 'ー' : '';
                    }
                    return getRenderString(data, row, row['3rd_interview_date'], row['3rd_refusal_reason_date'], row['3rd_send_off_date']);
                }
            },
            {
                "targets": 9,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 7 ? 'ー' : '';
                    }
                    return getRenderString(data, row, row['last_interview_date'], row['last_refusal_reason_date'], row['last_send_off_date']);
                }
            },
            {
                "targets": 10,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 8 ? 'ー' : '';
                    }
                    switch(data) {
                        case 1:
                            return '<div class="padding-box"><p>参画予定日</p><span class="bold">' + ensureValue(row.joining_scheduled_date) + '</span></div>';
                        case 2:
                            return '<div class="first-line"><p class="red">！要対応</p></div><div class="second-line bg-blue">契約条件<br>提示・交渉</div>';
                        default:
                            return '<div class="padding-box"><p>契約条件<br>同意待ち</p></div>';
                    }
                }
            },
            {
                "targets": 11,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 9 ? 'ー' : '';
                    }
                    switch(data) {
                        case 1:
                            return '<div class="padding-box"><p class="bold"><em class="orange">〇</em>&nbsp;参画開始</p><span>' + ensureValue(row.joining_confirmation_start_date) + '</span></div>';
                        case 2:
                            return '<div class="padding-box"><p class="bold"><em class="gray">✖</em>&nbsp;辞退</p><span>' + ensureValue(row.joining_confirmation_refusal_reason_date) + '</span></div>';
                        default:
                            return '<div class="padding-box"><p class="bold"><em class="gray">✖</em>&nbsp;見送り</p><span>' + ensureValue(row.joining_confirmation_send_off_date) + '</span></div>';
                    }
                }
            },
            {
                "targets": 12,
                "render": function ( data, type, row, meta ) {
                    if (data == null) {
                        return row.last_selection_flow_number > 10 ? 'ー' : '';
                    }
                    switch(data) {
                        case 1:
                            return '<div class="padding-box"><p class="bold"><em class="orange">〇</em>&nbsp;参画中</p><span>' + ensureValue(row.joining_confirmation_start_date) + '</span></div>';
                        case 2:
                            return '<div class="padding-box"><p class="bold"><em class="gray">✖</em>&nbsp;終了</p><span>' + ensureValue(row.joining_end_date) + '</span></div>';
                        default:
                            return 'ー';
                    }
                }
            },
        ],
        columns: [
            {data: 'job_seeker_id'},
            {data: 'recruiting_company_id'},
            {data: 'job_title'},
            {data: 'proposal'},
            {data: 'document_confirmation'},
            {data: 'applicant_screening'},
            {data: '1st_interview'},
            {data: '2nd_interview'},
            {data: '3rd_interview'},
            {data: 'last_interview'},
            {data: 'contract'},
            {data: 'joining_confirmation'},
            {data: 'current_state'},
        ]
    });

    dataTableOutsource.on('page.dt', function(){
        var info = dataTableOutsource.page.info();
        console.log( 'Showing page: '+info.page+' of '+info.pages );
        g_paginationClicked = true;
        window.scrollTo(0,document.body.scrollHeight);
    });

    dataTableOutsource.on('draw', function () {
        if (g_paginationClicked == true) {
            g_paginationClicked = false;
            window.scrollTo(0,document.body.scrollHeight);
        }

        dataTableOutsource.rows().every(function(rowIdx, tableLoop, rowLoop){
            var rowData = this.data();
            if(rowData.selection_status == 1) {
                $(this.node()).addClass('expand');
            }       
        });

        $('#dataTable-outsource tbody td').each(function(i) {
            var index = dataTableOutsource.cell(this).index();
            var obj = $(this);

            if (/*obj.find('div').length &&*/ index != undefined && index.column > 2) {
                $(this).css('cursor', 'pointer');
            }
        });
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

    $(document).on("click", ".outsource_status ", function () {
        currentStatus = $(this).data('id');
        dataTableOutsource.draw();
    });

    $(document).on("click", "#search_box .btn-search", function () {
        currentStatus = 8;
        currentJobSeeker = $("#job-seeker").val();
        currentOutsourceUser = $("#outsource-user-select").val();
        currentKeyword = $("#keyword").val();
        dataTableOutsource.draw();
    });

    $(document).on("change", ".sort_outsource", function () {
        dataTableOutsource.draw();
    });

    window.onkeydown = function(event){
        var keyCode = event.keyCode;
        if (keyCode == 13) {
            $("#search_box .btn-search").trigger('click');
            return false;
        }
    }

    $('#dataTable-outsource tbody').on( 'click', 'td', function () {
        let index = dataTableOutsource.cell(this).index();
        let obj = $(this);

        if (/*obj.find('div').length &&*/ index != undefined && index.column > 2) {
            let dataDiv = obj.parent().find('td:nth-child(3) div');
            let id = dataDiv.data('id');
            location.href = g_baseURL + '/ses/apply/' + id;
        }
    });
});
