<?php
include './config/connection.php';
include './common_service/common_functions.php';

$type = isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 0;
// Allow Admin (1), Doctor (2), Front Desk (3)
if (!in_array($type, [1, 2, 3], true)) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './config/site_css_links.php'; ?>
    <title>Follow-up Calendar - Medical Care System</title>
    <style>
        .calendar-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
        }
        .calendar-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .calendar-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .calendar-card-header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .calendar-nav button {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.4);
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .calendar-nav button:hover {
            background: rgba(255,255,255,0.3);
        }
        .calendar-nav .month-year-label {
            font-size: 1.25rem;
            font-weight: 700;
            min-width: 200px;
            text-align: center;
        }
        .calendar-body {
            padding: 24px;
        }
        /* Big calendar grid */
        .big-calendar {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: clamp(16px, 2.2vw, 22px);
        }
        .big-calendar th {
            background: #2c3e50;
            color: white;
            padding: 18px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 1.05em;
        }
        .big-calendar td {
            border: 1px solid #e0e0e0;
            padding: 0;
            vertical-align: top;
            min-height: 0;
            height: 140px;
        }
        .big-calendar .day-cell {
            height: 140px;
            padding: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .big-calendar .day-cell:hover {
            background: #f0f4ff;
        }
        .big-calendar .day-cell.other-month {
            background: #f8f9fa;
            color: #adb5bd;
        }
        .big-calendar .day-cell.other-month:hover {
            background: #e9ecef;
        }
        .big-calendar .day-cell.today {
            background: #e7f1ff;
            border: 2px solid #667eea;
        }
        .big-calendar .day-cell.has-appointments {
            background: #e8f5e9;
        }
        .big-calendar .day-cell.has-appointments:hover {
            background: #c8e6c9;
        }
        .day-number {
            font-weight: 700;
            font-size: 1em;
            margin-bottom: 4px;
        }
        .day-count {
            display: inline-block;
            background: #667eea;
            color: white;
            border-radius: 50%;
            min-width: 26px;
            height: 26px;
            line-height: 26px;
            text-align: center;
            font-size: 0.85em;
            font-weight: 700;
        }
        .day-count.zero {
            background: transparent;
            color: #999;
        }
        /* Day detail panel */
        .day-detail-panel {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            margin-top: 20px;
        }
        .day-detail-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 16px 24px;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .day-detail-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        .day-detail-empty {
            color: #6c757d;
            text-align: center;
            padding: 40px 20px;
        }
        .followup-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
            transition: all 0.2s;
        }
        .followup-item:hover {
            background: #e8f5e9;
            border-color: #28a745;
        }
        .followup-item .patient-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.05rem;
        }
        .followup-item .reason {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 4px;
        }
        .followup-item .time {
            font-weight: 700;
            color: #667eea;
            font-size: 1.1rem;
        }
        .followup-item .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .followup-item .status-badge.scheduled { background: #cce5ff; color: #004085; }
        .followup-item .status-badge.checked-in { background: #fff3cd; color: #856404; }
        .followup-item .status-badge.completed { background: #d4edda; color: #155724; }
        .followup-item .status-badge.doctor-done { background: #d4edda; color: #155724; }
        .followup-item .status-badge.cancelled { background: #f8d7da; color: #721c24; }
        .followup-item .status-badge.follow-up-check-up { background: #d1ecf1; color: #0c5460; }
        .followup-item .status-badge['follow-up-check-up'] { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    <?php include './config/header.php'; ?>
    <?php include './config/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0 text-dark" style="font-size: 1.5rem;"></h1>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="calendar-page">
                    <div class="calendar-card">
                        <div class="calendar-card-header">
                            <h1><i class="fas fa-calendar-alt"></i></h1>
                            <div class="calendar-nav">
                                <button type="button" id="cal_prev" title="Previous month"><i class="fas fa-chevron-left"></i></button>
                                <span class="month-year-label" id="month_year_label"></span>
                                <button type="button" id="cal_next" title="Next month"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="calendar-body">
                            <table class="big-calendar">
                                <thead>
                                    <tr>
                                        <th>Sun</th>
                                        <th>Mon</th>
                                        <th>Tue</th>
                                        <th>Wed</th>
                                        <th>Thu</th>
                                        <th>Fri</th>
                                        <th>Sat</th>
                                    </tr>
                                </thead>
                                <tbody id="cal_tbody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="day-detail-panel" id="day_detail_panel" style="display: none;">
                        <div class="day-detail-header" id="day_detail_header">Appointments</div>
                        <div class="day-detail-body" id="day_detail_body">
                            <div class="day-detail-empty" id="day_detail_empty">Select a day on the calendar.</div>
                            <div id="day_detail_list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include './config/site_js_links.php'; ?>
<script>
(function() {
    var currentYear = parseInt('<?php echo date("Y"); ?>', 10);
    var currentMonth = parseInt('<?php echo date("n"); ?>', 10);
    var counts = {};
    var selectedDate = null;

    function monthName(m) {
        var names = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return names[m - 1];
    }

    function loadCounts() {
        $.get('ajax/get_followup_calendar_counts.php', { year: currentYear, month: currentMonth }, function(res) {
            if (res.status === 'success') {
                counts = res.counts || {};
                renderCalendar();
            }
        });
    }

    function renderCalendar() {
        var firstDay = new Date(currentYear, currentMonth - 1, 1);
        var lastDay = new Date(currentYear, currentMonth, 0);
        var startPad = firstDay.getDay();
        var daysInMonth = lastDay.getDate();
        var prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
        var prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;
        var prevLast = new Date(prevYear, prevMonth, 0);
        var daysPrev = prevLast.getDate();

        $('#month_year_label').text(monthName(currentMonth) + ' ' + currentYear);

        var html = '';
        var dayCount = 1;
        var nextMonthDay = 1;
        var totalCells = Math.ceil((startPad + daysInMonth) / 7) * 7;

        for (var i = 0; i < totalCells; i++) {
            if (i % 7 === 0) html += '<tr>';
            var cellDay, cellMonth, cellYear, isOther, dateKey;
            if (i < startPad) {
                cellDay = daysPrev - startPad + i + 1;
                cellMonth = prevMonth;
                cellYear = prevYear;
                isOther = true;
            } else if (dayCount <= daysInMonth) {
                cellDay = dayCount;
                cellMonth = currentMonth;
                cellYear = currentYear;
                isOther = false;
                dayCount++;
            } else {
                cellDay = nextMonthDay++;
                cellMonth = currentMonth === 12 ? 1 : currentMonth + 1;
                cellYear = currentMonth === 12 ? currentYear + 1 : currentYear;
                isOther = true;
            }
            dateKey = cellYear + '-' + String(cellMonth).padStart(2,'0') + '-' + String(cellDay).padStart(2,'0');
            var today = new Date();
            var isToday = today.getFullYear() === cellYear && today.getMonth() + 1 === cellMonth && today.getDate() === cellDay;
            var cnt = counts[dateKey] || 0;
            var cls = 'day-cell';
            if (isOther) cls += ' other-month';
            if (isToday) cls += ' today';
            if (!isOther && cnt > 0) cls += ' has-appointments';
            html += '<td class="' + cls + '" data-date="' + dateKey + '">';
            html += '<div class="day-number">' + cellDay + '</div>';
            html += '<span class="day-count' + (cnt === 0 ? ' zero' : '') + '">' + (cnt || '') + '</span>';
            html += '</td>';
            if (i % 7 === 6) html += '</tr>';
        }
        $('#cal_tbody').html(html);

        $('#cal_tbody .day-cell').on('click', function() {
            var date = $(this).data('date');
            selectedDate = date;
            $('#day_detail_panel').show();
            $('#day_detail_header').text(date);
            loadDayAppointments(date);
        });
    }

    function loadDayAppointments(date) {
        $('#day_detail_list').empty();
        $('#day_detail_empty').show();
        $.get('ajax/get_followup_appointments_by_date.php', { date: date }, function(res) {
            $('#day_detail_empty').hide();
            if (res.status !== 'success' || !res.data || res.data.length === 0) {
                $('#day_detail_empty').text('No follow-up appointments on this day.').show();
                return;
            }
            $('#day_detail_empty').hide();
            $.each(res.data, function(i, a) {
                var status = (a.status || '').toLowerCase().replace(/\s+/g, '-');
                var badge = '<span class="status-badge ' + status + '">' + (a.status || 'Scheduled') + '</span>';
                var item = '<div class="followup-item">' +
                    '<div><div class="patient-name">' + (a.patient_name || 'Unknown') + '</div>' +
                    '<div class="reason">' + (a.reason_for_visit || '—') + '</div></div>' +
                    '<div style="display:flex;align-items:center;gap:12px;">' +
                    '<span class="time">' + (a.appointment_time || '') + '</span>' + badge + '</div></div>';
                $('#day_detail_list').append(item);
            });
        });
    }

    $('#cal_prev').on('click', function() {
        currentMonth--;
        if (currentMonth < 1) { currentMonth = 12; currentYear--; }
        loadCounts();
    });
    $('#cal_next').on('click', function() {
        currentMonth++;
        if (currentMonth > 12) { currentMonth = 1; currentYear++; }
        loadCounts();
    });

    loadCounts();
})();
</script>
</body>
</html>
