/**
* 2010-2021 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through LICENSE.txt file inside our module
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright 2010-2021 Webkul IN
* @license LICENSE.txt
*/

$(document).ready(function() {
    //when seller choose from datepicker
    $('#date-range-picker').daterangepicker({
        "opens": "left",
        "showDropdowns": true,
        "startDate": userFriendlyDateFrom,
        "endDate": userFriendlyDateTo,
        "maxDate": currentDate,
        locale: {
            format: 'DD-MM-YYYY'
        },
    }, function(start, end, label) {
        $("#dashboardDateFrom").val(start.format('YYYY-MM-DD'));
        $("#dashboardDateTo").val(end.format('YYYY-MM-DD'));
        $('#preselectDateRange').val(0);
        $('.setPreselectDateRange').removeClass('btn-primary').addClass('btn-default');
        loadSellerDashboard(0);
    });

    //When seller choose button day, month, year
    $(".setPreselectDateRange").on('click', function() {
        $('#preselectDateRange').val($(this).attr('data-date-range'));
        //$('button[name="submitDashboardDate"]').click();
        $('.setPreselectDateRange').removeClass('btn-primary').addClass('btn-default');
        $(this).addClass('btn-primary');

        loadSellerDashboard($('#preselectDateRange').val());
    });

    $(".mp_order_row").on("click", function() {
        var id_order = $(this).attr('is_id_order');
        if (typeof friendly_url != 'undefined' && (friendly_url == '1')) {
            window.location.href = mporderdetails_link + '?id_order=' + id_order;
        } else {
            window.location.href = mporderdetails_link + '&id_order=' + id_order;
        }
    });

    //Display Graph Dashboard on page load
    loadSellerDashboard($('#preselectDateRange').val());
});

function loadSellerDashboard(preselectDateRange) {
    var dateFrom = $('#dashboardDateFrom').val();
    var dateTo = $('#dashboardDateTo').val();

    $('#wk-dashboad-graph-chart').addClass('wk-loading-graph');

    $.ajax({
        url: dashboard_link,
        data: {
            ajax: true,
            action: 'loadSellerDashboard',
            dateFrom: dateFrom,
            dateTo: dateTo,
            preselectDateRange: preselectDateRange,
        },
        headers: { "cache-control": "no-cache" },
        cache: false,
        global: false,
        dataType: 'json',
        success: function(widgets) {
            $('#wk-dashboad-graph-chart').removeClass('wk-loading-graph');
            $('.wk-sales').css({ 'background-color': '#1777b6', 'color': '#fff' }).addClass('active');
            $('.wk-orders').css({ 'background-color': '#fff', 'color': '#414141' }).removeClass('active');
            for (var widget_name in widgets) {
                for (var data_type in widgets[widget_name]) {
                    window[data_type](widget_name, widgets[widget_name][data_type]);
                }
            }
        },
        contentType: 'application/json'
    });
}

function data_value(widget_name, data) {
    for (var data_id in data) {
        $('#' + data_id + ' ').html(data[data_id]);
        $('#' + data_id + ', #' + widget_name).closest('section').removeClass('loading');
    }
}

function data_trends(widget_name, data) {
    for (var data_id in data) {
        this.el = $('#' + data_id);
        this.el.html(data[data_id].value);
        if (data[data_id].way === 'up') {
            this.el.parent().removeClass('dash_trend_down').removeClass('dash_trend_right').addClass('dash_trend_up');
        } else if (data[data_id].way === 'down') {
            this.el.parent().removeClass('dash_trend_up').removeClass('dash_trend_right').addClass('dash_trend_down');
        } else {
            this.el.parent().removeClass('dash_trend_down').removeClass('dash_trend_up').addClass('dash_trend_right');
        }
        this.el.closest('section').removeClass('loading');
    }
}

function data_table(widget_name, data) {
    for (var data_id in data) {
        var tr = '<tr>';
        for (var header in data[data_id].header) {
            var head = data[data_id].header[header];
            var th = '<th ' + (head.class ? ' class="' + head.class + '" ' : '') + ' ' + (head.id ? ' id="' + head.id + '" ' : '') + '>';
            th += (head.wrapper_start ? ' ' + head.wrapper_start + ' ' : '');
            th += head.title;
            th += (head.wrapper_stop ? ' ' + head.wrapper_stop + ' ' : '');
            th += '</th>';
            tr += th;
        }
        tr += '</tr>';
        $('#' + data_id + ' thead').html(tr);
        $('#' + data_id + ' tbody').html('');
        if (typeof data[data_id].body === 'string') {
            $('#' + data_id + ' tbody').html('<tr><td class="text-center" colspan="' + data[data_id].header.length + '"><br/>' + data[data_id].body + '</td></tr>');
        } else if (data[data_id].body.length) {
            for (var body_content_id in data[data_id].body) {
                tr = '<tr>';
                for (var body_content in data[data_id].body[body_content_id]) {
                    var body = data[data_id].body[body_content_id][body_content];
                    var td = '<td ' + (body.class ? ' class="' + body.class + '" ' : '') + ' ' + (body.id ? ' id="' + body.id + '" ' : '') + '>';
                    td += (body.wrapper_start ? ' ' + body.wrapper_start + ' ' : '');
                    td += body.value;
                    td += (body.wrapper_stop ? ' ' + body.wrapper_stop + ' ' : '');
                    td += '</td>';
                    tr += td;
                }
                tr += '</tr>';
                $('#' + data_id + ' tbody').append(tr);
            }
        } else {
            $('#' + data_id + ' tbody').html('<tr><td class="text-center" colspan="' + data[data_id].header.length + '">' + no_results_translation + '</td></tr>');
        }
    }
}

function data_chart(widget_name, charts) {
    for (var chart_id in charts) {
        window[charts[chart_id].chart_type](widget_name, charts[chart_id]);
    }
}

var dashtrends_data;
var dashtrends_chart;

function line_chart_trends(widget_name, chart_details) {
    if (chart_details.data[0].values.length <= 1)
        $('#wk-dashboad-graph-chart').hide();
    else
        $('#wk-dashboad-graph-chart').show();
    nv.addGraph(function() {
        var chart = nv.models.lineChart()
            .useInteractiveGuideline(true)
            .x(function(d) { return (d !== undefined ? d[0] : 0); })
            .y(function(d) { return (d !== undefined ? parseInt(d[1]) : 0); })
            .margin({ left: 80 });

        chart.xAxis.tickFormat(function(d) {
            date = new Date(d * 1000);
            return date.getDate() + "/" + (date.getMonth() + 1) + "/" + date.getFullYear();
        });

        first_data = new Array();
        $.each(chart_details.data, function(index, value) {
            if (value.id == 'sales' || value.id == 'sales_compare') {
                if (value.id == 'sales')
                    $('#dashtrends_toolbar dl:first').css({ 'background-color': chart_details.data[index].color, 'color': '#fff' }).addClass('active');
                first_data.push(chart_details.data[index]);
            }
        });

        dashtrends_data = chart_details.data;
        dashtrends_chart = chart;

        d3.select('#wk-dashboad-graph-chart svg')
            .datum(first_data)
            .call(chart);
        nv.utils.windowResize(chart.update);

        return chart;
    });
}

function selectDashtrendsChart(element, type) {
    $('#dashtrends_toolbar dl').removeClass('active');
    current_charts = new Array();
    $.each(dashtrends_data, function(index, value) {
        if (value.id == type || value.id == type + '_compare') {
            if (value.id == type) {
                $(element).siblings().css({ 'background-color': '#fff', 'color': '#414141' }).removeClass('active');
                $(element).css({ 'background-color': dashtrends_data[index].color, 'color': '#fff' }).addClass('active');
            }

            current_charts.push(dashtrends_data[index]);
            value.disabled = false;
        }
    });

    dashtrends_chart.yAxis.tickFormat(d3.format('.f'));

    if (type == 'conversion_rate')
        dashtrends_chart.yAxis.tickFormat(function(d) {
            return d3.round(d * 100, 2) + ' %';
        });

    d3.select('#wk-dashboad-graph-chart svg')
        .datum(current_charts)
        .call(dashtrends_chart);
}