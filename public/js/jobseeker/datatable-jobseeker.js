var dataTableRecruit;
var currentKeyword;
var currentJobTitle;
var currentSearchDate;
var currentStatus = 5;
var currentSort = 1; // 1: Desc, 2:Asc
var g_paginationClicked = false;
var g_jobID = 0;
var g_interID = 0;

jQuery(function($){

	function ensureValue(value) {
		return value == null ? ' ' : value;
	}

	function getRenderString(data, date, refusalDate, notAdoptedDate) {
		if (data == null) return 'ー';
		switch(data) {
			case 1:
				return '<div class="padding-box"><p><em class="green">〇</em>&nbsp内定</p><span>' + ensureValue(date) + '</span></div>';
			case 2:
				return '<div class="padding-box"><p><em class="green">〇</em>&nbsp通過</p><span>' + ensureValue(date) + '</span></div>';
			case 3:
				return '<div class="padding-box"><p><em class="gray">✖</em>&nbsp辞退</p><span>' + ensureValue(refusalDate) + '</span></div>';
			case 4:
				return '<div class="padding-box"><p><em class="gray">✖</em>&nbsp不採用</p><span>' + ensureValue(notAdoptedDate) + '</span></div>';
			case 5:
				return '<div class="first-line"><p class="red">！要対応</p></div><div class="second-line bg-blue">面談日程<br>未確定</div>';
			case 7:
				return '<div class="first-line"><p class="red">！要対応</p></div><div class="second-line bg-blue">選考結果<br>未送付</div>';
			default:
				return '<div class="padding-box"><p>面談設定済み</p></div>';
		}
	}

	// デフォルトの設定を変更
    $.extend( $.fn.dataTable.defaults, {
        language: {
            url: "/assets/datatables_js.json"
        }
    });

    dataTableRecruit = $("#dataTable-recruit").DataTable({
		ajax: {
            url: g_baseURL + "/agent/jobseeker/datatable",
            type: 'post',
			data: function(data, settings) {
				data.extra = {
					//recruit_offer_info_id : currentJobTitle,
					keyword : currentKeyword,
					status: currentStatus,
					order: $(".sort_recruit").val(),
					//search_date: currentSearchDate,
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
        columnDefs: [
            {
                "targets": 0,
				orderable: true,
                "render": function ( data, type, row, meta ) {
					return '<span style="display: none">' + row.job_seeker_id + '</span>' +
                        '<p style="white-space: break-spaces">' + row.last_name + row.first_name + '</p>';
                }
            },
			{
                "targets": 1,
                "render": function ( data, type, row, meta ) {
					return '<p style="white-space: break-spaces">' + row.name + '</p>';
                }
            },
			{
                "targets": 2,
                "render": function ( data, type, row, meta ) {
                    return '<p style="white-space: break-spaces">' + row.birthday + '</p>';
                }
            },
			{
                "targets": 3,
                "render": function ( data, type, row, meta ) {
                    return '<p style="white-space: break-spaces">' + row.status + '</p>';
                }
            },
			{
				"targets": 4,
                "render": function ( data, type, row, meta ) {
                    return '<p style="white-space: break-spaces">' + row.selection_status + '</p>';
                }
			},
			{
				"targets": 5,
                "render": function ( data, type, row, meta ) {
                    return '<p style="white-space: break-spaces">' + row.job_offer_count + '</p>';
                }
			},
			{
				"targets": 6,
                "render": function ( data, type, row, meta ) {
                    return '<p style="white-space: break-spaces">' + row.apply_count + '</p>';
                }
			},
			{
				"targets": 7,
                "render": function ( data, type, row, meta ) {
                    return '<p style="white-space: break-spaces">' + row.register_type + '</p>';
                }
			},
            {
                "targets": 8,
                "render": function ( data, type, row, meta ) {
                    return '<p style="white-space: break-spaces">' + row.workable_date + '</p>';
                }
            },
			{
				"targets": 9,
                "render": function ( data, type, row, meta ) {
                    return '<p style="white-space: break-spaces">' + row.job_seeker_memo + '</p>';
                }
			},
        ],
        columns: [
            {data: 'last_name'},
			{data: 'name'},
			{data: 'age'},
			{data: 'status'},
			{data: 'selection_status'},
			{data: 'job_offer_count'},
			{data: 'apply_count'},
			{data: 'register_type'},
            {data: 'workable_date'},
			{data: 'job_seeker_memo'},
        ]
    });

	dataTableRecruit.on('page.dt', function(){
		var info = dataTableRecruit.page.info();
		console.log( 'Showing page: '+info.page+' of '+info.pages );
		g_paginationClicked = true;
		window.scrollTo(0,document.body.scrollHeight);
	});

	dataTableRecruit.on( 'draw', function () {
		if (g_paginationClicked == true) {
			g_paginationClicked = false;
			window.scrollTo(0,document.body.scrollHeight);
		}

		$('#dataTable-recruit tbody td').each(function(i) {
			var index = dataTableRecruit.cell( this ).index();
			var obj = $(this);

			if (obj.find('div').length && index.column > 2) {
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

    $(document).on("click", ".recruit_status", function () {
        currentStatus = $(this).data('id');
		dataTableRecruit.draw();
    });

	$(document).on("click", "#recruit_search_button", function () {
		currentStatus = 5;
		currentJobTitle = $("#job-title-select").val();
		currentKeyword = $("#keyword").val();
		currentSearchDate = $("#search_date").val();
		dataTableRecruit.draw();
	});

	$(document).on("change", ".sort_recruit", function () {
		dataTableRecruit.draw();
	});

	window.onkeydown = function(event){
		var keyCode = event.keyCode;
		if (keyCode == 13) {
			$("#recruit_search_button").trigger('click');
			return false;
		}
	}

	$('#dataTable-recruit tbody').on( 'click', 'tr', function () {
		let index = dataTableRecruit.row( this ).index();
		let obj = $(this);

		if (obj.find('span').length) {
			let dataSpan = obj.parent().find('td:nth-child(1) span');
            let jobseekerID = dataSpan[index].innerHTML;
            g_jobID.setJobID(jobseekerID);
            g_interID.setInterID(jobseekerID);

            $.ajax({
                type: 'GET',
                url: '/agent/jobseeker/' + jobseekerID,
                dataType: 'json',
                success: function (data) {
                    if (data.success == true) {
                        $("#detail_name").text(data.jobSeeker.last_name + data.jobSeeker.first_name);
                        $("#detail_name_kana").text(data.jobSeeker.last_name_kana + data.jobSeeker.first_name_kana);
                        $("#detail_email").text(data.jobSeeker.email);
                        $("#detail_phone_number").text(data.jobSeeker.phone_number);
                        $("#detail_address").text(data.jobSeeker.address);
                        $("#detail_closest_station").text(data.jobSeeker.closest_station);
                        $("#detail_job_seeker_memo").text(data.jobSeeker.job_seeker_memo);
                        $("#detail_updated_at").text(data.jobSeeker.updated_at);

                        $("#detail_age").text(data.jobSeeker.age);
                        $("#detail_sex").text(enumJobSeekersSex[data.jobSeeker.sex]);
                        $("#detail_final_edcuation").text(enumJobSeekersFinalEducation[data.jobSeeker.final_education]);
                        $("#detail_working_company_number").text(enumJobSeekersWorkingCompanyNumber[data.jobSeeker.working_company_number]);
                        $("#detail_nationality").text(enumJobSeekersNationality[data.jobSeeker.nationality]);
                        $("#detail_japanese_level").text(enumJobSeekersLanguageLevel[data.jobSeeker.japanese_level]);
                        $("#detail_english_level").text(enumJobSeekersLanguageLevel[data.jobSeeker.english_level]);
                        $("#detail_chinese_level").text(enumJobSeekersLanguageLevel[data.jobSeeker.chinese_level]);
                        $("#detail_desired_job_category").text(data.jobSeeker.desired_job_category);
                        $("#detail_desired_industry").text(data.jobSeeker.desired_industry);
                        $("#detail_experience_job_category").text(data.jobSeeker.experience_job_category);
                        $("#detail_experience_industry").text(data.jobSeeker.experience_industry);
                        $("#detail_employment_status").text(data.jobSeeker.employment_status);
                        $("#detail_annual_income").text(data.jobSeeker.annual_income + '万円');
                        $("#detail_desired_income").text(data.jobSeeker.desired_income + '円');
                        $("#detail_current_annual_income").text(data.jobSeeker.current_annual_income);
                        $("#detail_suggested_working_place").text(data.jobSeeker.suggested_working_place);
                        $("#detail_home_working").text(enumJobSeekersHomeWorking[data.jobSeeker.home_working]);
                        $("#detail_workable_date").text(data.jobSeeker.workable_date);
                        $("#detail_feature_desired").text(enumJobSeekersFeatureDesired[data.jobSeeker.feature_desired]);

                        $("#edit_first_name").val(data.jobSeeker.first_name);
                        $("#edit_last_name").val(data.jobSeeker.last_name);
                        $("#edit_first_name_kana").val(data.jobSeeker.first_name_kana);
                        $("#edit_last_name_kana").val(data.jobSeeker.last_name_kana);
                        $("#edit_email").val(data.jobSeeker.email);
                        $("#edit_phone_number").val(data.jobSeeker.phone_number);
                        $("#edit_prefecture_id").val(3);
                        $("#edit_address").val(data.jobSeeker.address);
                        $("#edit_closest_station").val(data.jobSeeker.closest_station);
                        $("#edit_birthday").val(data.jobSeeker.birthday);
                        $("#edit_age").val(data.jobSeeker.age);

                        switch (data.jobSeeker.sex) {
                            case 1:
                                $("#edit_sex_man").prop("checked", true);
                                break;
                            case 2:
                                $("#edit_sex_woman").prop("checked", true);
                                break;
                            case 3:
                                break;
                        }

                        $("#edit_final_edcuation").val(data.jobSeeker.final_education);
                        $("#edit_working_company_number").val(data.jobSeeker.working_company_number);
                        $("#edit_nationality").val(data.jobSeeker.nationality);
                        $("#edit_japanese_level").val(data.jobSeeker.japanese_level);
                        $("#edit_english_level").val(data.jobSeeker.english_level);
                        $("#edit_chinese_level").val(data.jobSeeker.chinese_level);
                        $("#edit_desired_job_category").val(data.jobSeeker.desired_job_category);
                        $("#edit_desired_industry").val(data.jobSeeker.desired_industry);
                        $("#edit_experience_job_category").val(data.jobSeeker.experience_job_category);
                        $("#edit_experience_industry").val(data.jobSeeker.experience_industry);
                        $("#edit_employment_status").val(data.jobSeeker.employment_status);
                        $("#edit_desired_income_type").val(data.jobSeeker.desired_income_type);
                        $("#edit_annual_income").val(data.jobSeeker.annual_income);
                        $("#edit_suggested_working_place").val(data.jobSeeker.suggested_working_place);
                        $("#edit_home_working").val(data.jobSeeker.home_working);
                        $("#edit_feature_desired").val(data.jobSeeker.feature_desired);
                        $("#edit_workable_date").val(data.jobSeeker.workable_date);
                        $("#edit_status").val(data.jobSeeker.status);

                        $("#search-result").removeClass("hide");
                    }
                }, error: function (data) {
                    console.log(data);
                }
            })
		}

	});
});
