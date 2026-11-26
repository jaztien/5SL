<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
// 定義本頁面代碼（排程列表目錄）
define('PAGE_MARK', 'PG_SCH_CAL');
define('PAGE_ITEM', 'ITEM_SCH');
define('PAGE_OPANEL', 1); // 1-新增
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('COLL_OPANEL', 1);
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$csrfToken = function_exists('_fn_genToken') ? _fn_genToken() : ($_SESSION['token'] ?? '');
?>
<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
    <title>Fifth Season Life 人生第五季 | 排程與通知日曆</title>
    <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css" rel="stylesheet">
    <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../cssFiles/adminlte.css">
    <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
    <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
    <style>
        #appointmentModal .modal-content {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 1.25rem 3rem rgba(0, 0, 0, .32);
        }

        #appointmentModal .form-control,
        #appointmentModal .form-select {
            border-radius: .65rem;
        }

        #appointmentModal .form-floating>label {
            color: var(--bs-secondary-color);
        }

        #appointmentModal .modal-title {
            font-size: 1.25rem;
            font-weight: 500;
        }

        #appointmentModal #appointmentTitle {
            height: 3.5rem;
            padding: .5rem 0;
            border: 0;
            border-bottom: 1px solid var(--bs-border-color);
            border-radius: 0;
            background: transparent;
            font-size: 1.35rem;
            box-shadow: none;
        }

        #appointmentModal #appointmentTitle:focus {
            border-bottom: 2px solid var(--bs-primary);
        }

        #appointmentModal #appointmentTitle+label {
            display: none;
        }

        #appointmentModal .editor-section {
            padding: 0;
            border: 0;
            background: transparent;
        }

        #appointmentModal .editor-section .form-label {
            color: var(--bs-secondary-color);
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        #appointmentModal .editor-section .form-control,
        #appointmentModal .editor-section .form-select {
            background: var(--bs-tertiary-bg);
        }

        #appointmentModal .event-row {
            display: grid;
            grid-template-columns: 2rem minmax(0, 1fr);
            gap: .75rem;
            align-items: start;
        }

        #appointmentModal .event-icon {
            width: 2rem;
            padding-top: .65rem;
            color: var(--bs-secondary-color);
            font-size: 1.1rem;
            text-align: center;
        }

        #appointmentModal .js-priority-fields .btn-group {
            display: inline-flex;
            width: auto !important;
            gap: .25rem;
        }

        #appointmentModal .js-priority-fields .btn-group .btn {
            min-width: 5rem;
            border: 1px solid var(--bs-border-color) !important;
            border-radius: 999px !important;
            color: var(--bs-secondary-color);
            background: transparent;
        }

        #appointmentModal .js-priority-fields .btn-group .btn:hover,
        #appointmentModal .js-priority-fields .btn-group .btn-check:checked+.btn {
            color: var(--bs-primary);
            background: var(--bs-primary-bg-subtle);
            border-color: var(--bs-primary) !important;
        }

        #appointmentModal .powered-by {
            display: none;
        }

        #appointmentModal .modal-footer-actions {
            padding: 1rem 1.5rem;
            background: var(--bs-secondary-bg);
            border-radius: 0 0 1rem 1rem;
        }

        @media (min-width: 992px) {
            #appointmentModal .modal-footer-actions {
                padding-inline: 3rem;
            }
        }

#appointmentModal .form-switch .form-check-input {
    position: relative !important;
    background-image: none !important;
    background-color: transparent !important;
    border: 1px solid #6c757d !important;
    box-shadow: none !important;
}
#appointmentModal .form-switch .form-check-input::after {
    content: "";
    position: absolute;
    top: 2px;
    left: 2px;
    width: calc(1em - 4px);
    height: calc(1em - 4px);
    border-radius: 50%;
    background-color: #adb5bd;
    transition: transform .15s ease-in-out, background-color .15s ease-in-out;
}
#appointmentModal .form-switch .form-check-input:focus {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}
#appointmentModal .form-switch .form-check-input::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 2px;
    width: calc(1em - 5px);
    height: calc(1em - 5px);
    border-radius: 50%;
    background-color: #adb5bd;
    transform: translateY(-50%);
    transition: transform .15s ease-in-out, background-color .15s ease-in-out;
}
#appointmentModal .form-switch .form-check-input:checked {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}
#appointmentModal .form-switch .form-check-input:checked::after {
    background-color: #ffffff;
    transform: translate(1em, -50%);
}



    </style>
</head>

<body class="bg-body-tertiary theme-cyan p-1">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
    <div class="app-wrapper">
        <main class="app-main">

            <div class="d-flex h-100 align-items-center justify-content-center z-6 position-fixed top-0 start-0 w-100" id="temp_loading">
                <div class="spinner-border" role="status" style="width: 15rem; height: 15rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div id="calendar" data-sidebar-addons="#sidebarAddons"></div>

            <div class="visually-hidden">
                <div id="sidebarAddons">
                    <h6 class="text-uppercase text-body-secondary fw-semibold mb-2">日曆操作與檢視</h6>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckTheme" name="flexSwitchCheckTheme" checked>
                            <label class="form-check-label" for="flexSwitchCheckTheme">深色模式主題</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="switchToggleDraggable" name="switchToggleDraggable">
                            <label class="form-check-label" for="switchToggleDraggable">啟用拖曳改期</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="switchQuarterHourLabels" checked>
                            <label class="form-check-label" for="switchQuarterHourLabels">顯示 15 分鐘刻度</label>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="sch_list.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="bi bi-list-ul me-1"></i> 切換列表檢視
                        </a>
                        <a href="sch_detail.php?currID=0&fromCal=1" class="btn btn-primary btn-sm rounded-pill">
                            <i class="bi bi-plus-lg me-1"></i> 建立完整通知排程
                        </a>
                        <button type="button" class="btn btn-outline-info btn-sm rounded-pill" data-method="refresh">
                            <i class="bi bi-arrow-clockwise me-1"></i> 重新同步資料
                        </button>
                    </div>
                </div>
            </div>


            <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header px-4 px-lg-5 py-3">
                            <div>
                                <div class="small text-uppercase text-body-secondary fw-semibold">Calendar</div>
                                <h5 class="modal-title mb-0" id="appointmentModalLabel">Appointment</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4 p-lg-5">

                            <div class="form-floating mb-4">
                                <input name="title" type="text" class="form-control" id="appointmentTitle"
                                    placeholder="appointment title">
                                <label for="appointmentTitle">Title</label>
                            </div>

                            <div class="event-row mb-3">
                                <i class="bi bi-calendar-event event-icon" aria-hidden="true"></i>
                                <div class="editor-section">
                                    <div id="appointmentDateRange">
                                        <input name="from_date" type="hidden" id="appointmentFromDate">
                                        <input name="to_date" type="hidden" id="appointmentToDate">
                                    </div>
                                    <!--                        <div class="powered-by small text-body-secondary mt-1">-->
                                    <!--                        Date selection powered by-->
                                    <!--                        <a href="https://github.webcito.de/?bootswatch=bootstrap&plugin=bs-datepicker" target="_blank"-->
                                    <!--                           rel="noopener noreferrer">ThomasDev-de/bs-datepicker</a>.-->
                                    <!--                        </div>-->
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-4 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allDay" role="switch"
                                        id="appointmentAllDay">
                                    <label class="form-check-label" for="appointmentAllDay">all day</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="overlap" role="switch"
                                        id="appointmentOverlap">
                                    <label class="form-check-label" for="appointmentOverlap">allow overlap (stack)</label>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                <select class="form-select form-select-sm w-auto" name="calendarId" id="appointmentCalendarId">
                                    <option value="course">課程梯次 (course)</option>
                                    <option value="remind">課前提醒 (remind)</option>
                                    <option value="marketing">行銷推播 (marketing)</option>
                                    <option value="admin">內部工作 (admin)</option>
                                </select>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="trigger_enable" role="switch" id="appointmentTriggerEnable">
                                    <label class="form-check-label" for="appointmentTriggerEnable">🔔 啟用定時自動通知</label>
                                </div>
                            </div>

                            <div class="event-row mb-3">
                                <i class="bi bi-arrow-repeat event-icon" aria-hidden="true"></i>
                                <div class="editor-section">
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <label class="form-label mb-1" for="appointmentRecurrenceFrequency">Repeat</label>
                                            <select class="form-select" name="recurrence_frequency" id="appointmentRecurrenceFrequency">
                                                <option value="none">Does not repeat</option>
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="yearly">Yearly</option>
                                            </select>
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label mb-1" for="appointmentRecurrenceUntil">Until</label>
                                            <input class="form-control" type="date" name="recurrence_until" id="appointmentRecurrenceUntil">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" name="isTask" role="switch"
                                    id="appointmentIsTask">
                                <label class="form-check-label" for="appointmentIsTask">task</label>
                            </div>

                            <div class="js-priority-fields mb-3" style="display: none;">
                                <div class="btn-group w-100 mb-3" role="group" aria-label="Priority">
                                    <input type="radio" class="btn-check" name="priority" id="priorityLow" value="low" autocomplete="off">
                                    <label class="btn btn-outline-success" for="priorityLow">Low</label>

                                    <input type="radio" class="btn-check" name="priority" id="priorityMedium" value="normal" autocomplete="off" checked>
                                    <label class="btn btn-outline-warning" for="priorityMedium">Medium</label>

                                    <input type="radio" class="btn-check" name="priority" id="priorityHigh" value="high" autocomplete="off">
                                    <label class="btn btn-outline-danger" for="priorityHigh">High</label>
                                </div>

                                <div class="form-floating">
                                    <input name="due" type="datetime-local" class="form-control" id="appointmentDue"
                                        placeholder="due date">
                                    <label for="appointmentDue">Due date</label>
                                </div>
                            </div>

                            <div class="event-row mb-3 js-time-fields">
                                <i class="bi bi-clock event-icon" aria-hidden="true"></i>
                                <div class="editor-section">
                                    <div class="d-flex justify-content-start align-items-center flex-nowrap mb-3 gap-2">
                                        <div class="flex-fill">
                                            <label for="appointmentFromTimePicker" class="form-label mb-1">From time</label>
                                            <div id="appointmentFromTimePicker"></div>
                                        </div>
                                        <div class="flex-fill">
                                            <label for="appointmentToTimePicker" class="form-label mb-1">To time</label>
                                            <div id="appointmentToTimePicker"></div>
                                        </div>
                                    </div>
                                    <!--                    <div class="powered-by small text-body-secondary mb-3">-->
                                    <!--                        Time selection powered by-->
                                    <!--                        <a href="https://github.webcito.de/?bootswatch=bootstrap&plugin=bs-timepicker" target="_blank"-->
                                    <!--                           rel="noopener noreferrer">ThomasDev-de/bs-timepicker</a>.-->
                                    <!--                    </div>-->
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-5">
                                    <div class="form-floating editor-section p-0">
                                        <textarea class="form-control" style="height: 110px" name="location"
                                            placeholder="Location" id="appointmentLocation"></textarea>
                                        <label for="appointmentLocation">Location</label>
                                    </div>
                                    <div class="form-text">Use one location per line.</div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-floating editor-section p-0">
                                        <textarea class="form-control" style="height: 110px" name="description"
                                            placeholder="Description" id="appointmentDescription"></textarea>
                                        <label for="appointmentDescription">Description</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating mb-4">
                                <input name="link" type="text" class="form-control" id="appointmentLink"
                                    placeholder="appointment link">
                                <label for="appointmentLink">Link</label>
                            </div>

                        </div>

                        <div class="modal-footer modal-footer-actions d-flex flex-wrap justify-content-end align-items-center gap-2">
                            <!--                    <div class="small text-body-secondary me-auto">-->
                            <!--                        Color picker powered by-->
                            <!--                        <a href="https://github.webcito.de/?bootswatch=bootstrap&plugin=bs-color-picker" target="_blank"-->
                            <!--                           rel="noopener noreferrer">ThomasDev-de/bs-colorpicker</a>.-->
                            <!--                    </div>-->

                            <div class="small text-body-secondary me-auto">
                                <a href="#" id="btnGoDetail" class="btn btn-outline-info me-auto d-none" target="_self">
                                    <i class="bi bi-sliders"></i> 進階通知對象設定
                                </a>
                            </div>
                            <div class="me-2">
                                <label for="appointmentColor" class="d-none">Color</label>
                                <input type="color" name="color" class="form-control me-2" id="appointmentColor">
                            </div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary ms-2" data-save-appointment>Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <!-- 右側進階搜尋 Offcanvas -->
        <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
            <div class="offcanvas-header border-bottom border-secondary">
                <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>排程與通知日曆功能篩選</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">

                <div class="mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="switchTriggeredEvents" name="switchTriggeredEvents">
                        <label class="form-check-label" for="switchTriggeredEvents">顯示觸發事件通知 (Toast)</label>
                    </div>
                    <label class="form-label small text-secondary mb-1" for="selectClickSnapMode">點擊新增時間對齊</label>
                    <select class="form-select form-select-sm mb-2" id="selectClickSnapMode">
                        <option value="nearest">最近 15 分鐘刻度</option>
                        <option value="previous">向下對齊 15 分鐘</option>
                        <option value="next">向上對齊 15 分鐘</option>
                    </select>
                    <label class="form-label small text-secondary mb-1" for="selectAppointmentRule">預設時長規則</label>
                    <select class="form-select form-select-sm mb-2" id="selectAppointmentRule">
                        <option value="none">不限制時長</option>
                        <option value="coaching45">45 分鐘課程區塊</option>
                        <option value="fixed60">固定 60 分鐘</option>
                    </select>
                </div>
                <hr class="border-secondary">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-light btn-sm" data-cal-cmd="setToday" data-view="week">回到本週 (Week)</button>
                    <button type="button" class="btn btn-outline-light btn-sm" data-cal-cmd="setToday" data-view="4day">回到今日 4 日檢視</button>
                    <button type="button" class="btn btn-outline-light btn-sm" data-cal-cmd="render">重新繪製畫面 (Render)</button>
                    <button type="button" class="btn btn-outline-warning btn-sm" data-cal-cmd="clear">暫時清空畫面 (Clear)</button>
                </div>

            </div>
        </div>

    </div>

    <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
    <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
    <script src="../pluginFiles/bscalendar/bs-calendar.js"></script>
    <script src="../pluginFiles/bstimepicker/bs-timepicker.min.js"></script>
    <script src="../pluginFiles/bscolorpicker/bs-color-picker.min.js?v=dev"></script>
    <script src="../pluginFiles/bsdatepicker/bs-datepicker.min.js?v=dev"></script>
    <script src="../pluginFiles/bstoast/bs-toast.min.js?v=dev"></script>
    <script src="../scriptFiles/adminlte.js"></script>
    <script src="../scriptFiles/page.basic.js"></script>
    <script src="../scriptFiles/iframe-state-keeper.js"></script>
    <script src="../scriptFiles/form.init.inc.js<?php echo '?t=' . time(); ?>"></script>
    <!--suppress JSUnresolvedReference -->
    <script>
        ////////////////////////////////////////////////////////////////
        // ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
        ////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////
        // -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
        // -------------- 可接收include檔案要求後自定義動作 -------------- //
        ////////////////////////////////////////////////////////////////
        /** 頂部工具列「新增」按鈕：前往完整排程設定頁 */
        function doButAdd() {
            window.location.href = 'sch_detail.php?currID=0&fromCal=1';
        }
        /** 頂部工具列「重整」按鈕：重新載入日曆頁 */
        function doButReset() {
            window.location.replace('sch_cal_list.php');
        }

        function doButLeftSide() {
            alert('doButLeftSide');
        }

        function doButSerach() {
            const offcanvasEl = document.getElementById('offcanvasSearch');
            if (offcanvasEl) bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).toggle();
        }

        ////////////////////////////////////////////////////////////////
        // ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
        // ---------------------------------------------------------- //
        ////////////////////////////////////////////////////////////////

        $(document).ready(function() {

            const $calendar = $('#calendar');
            const $modal = $('#appointmentModal');
            const $colorpicker = $('#appointmentColor');
            const $dateRange = $('#appointmentDateRange');

            // 因安全過濾限制，完整變數與 fetch/ajax 設定請參考系統模組結構，或使用原生 fetch 取代 $.ajax 呼叫。
            const ACT_URL = '../funcFiles/sch_act_func.php';
            const CSRF_TOKEN = '<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>';
            const FSL_CALENDARS = [{
                    id: 'course',
                    title: '課程梯次',
                    color: 'primary',
                    active: true
                },
                {
                    id: 'remind',
                    title: '課前提醒',
                    color: 'warning',
                    active: true
                },
                {
                    id: 'marketing',
                    title: '行銷推播',
                    color: 'success',
                    active: true
                },
                {
                    id: 'admin',
                    title: '內部工作',
                    color: 'secondary',
                    active: true
                }
            ];
            /** bs-calendar 繁體中文介面翻譯詞庫常數 */
            const FSL_TRANSLATIONS = {
                today: '今天', day: '日', week: '週', month: '月',
                agenda: '議程清單', allDay: '全天', search: '搜尋...'
            };
            /** 統一封裝後端 AJAX 請求（自動夾帶 CSRF Token 與 formAcce 操作代碼） */
            async function callSchApi(actionName, payload = {}) {
                const body = new URLSearchParams();
                body.append('token', CSRF_TOKEN);
                body.append('formAcce', actionName);
                Object.entries(payload).forEach(([k, v]) => {
                    if (Array.isArray(v)) v.forEach(item => body.append(`${k}[]`, item));
                    else if (v !== null && v !== undefined) body.append(k, typeof v === 'object' ? JSON.stringify(v) : v);
                });
                const res = await fetch(ACT_URL, {
                    method: 'POST',
                    body
                });
                return res.json();
            }
            /** bs-calendar 遠端資料載入器：向後端請求指定區間、年視圖或關鍵字搜尋的排程清單 */
            function loadAppointments(query) {
                return callSchApi('func_bs_cal_load', query);
            }


            $colorpicker.bsColorPicker({
                btnClass: 'btn btn-lg btn-outline-secondary',
            });

            $dateRange.bsDatepicker({
                months: 1,
                range: true
            });

            const modalState = {
                mode: 'add',
                appointmentId: null,
                taskChecked: false
            };
            const demoText = {
                modalAddTitle: '新增排程事件',
                modalEditTitle: '編輯排程事件',
                modalCreate: '建立排程',
                modalUpdate: '儲存變更',
                untitled: '未命名排程'
            };
            /*
            const demoText = {
                locale: 'en-US',
                holidays: {
                    country: 'IE',
                    federalState: null
                },
                calendars: [{
                        id: 'personal',
                        title: 'Personal',
                        color: 'primary',
                        active: true
                    },
                    {
                        id: 'work',
                        title: 'Work',
                        color: 'danger',
                        active: true
                    },
                    {
                        id: 'sports',
                        title: 'Sports',
                        color: 'success',
                        active: true
                    }
                ],
                btnRefresh: 'Refresh',
                btnClear: 'Clear',
                modalTitle: 'Appointment',
                inputTitlePlaceholder: 'appointment title',
                labelTitle: 'Title',
                labelFromDate: 'From date',
                labelToDate: 'To date',
                labelAllDay: 'all day',
                labelOverlap: 'allow overlap (stack)',
                labelRecurrenceFrequency: 'Repeat',
                labelRecurrenceUntil: 'Until',
                recurrenceNone: 'Does not repeat',
                recurrenceDaily: 'Daily',
                recurrenceWeekly: 'Weekly',
                recurrenceMonthly: 'Monthly',
                recurrenceYearly: 'Yearly',
                labelIsTask: 'task',
                labelPriorityLow: 'Low',
                labelPriorityMedium: 'Medium',
                labelPriorityHigh: 'High',
                labelDue: 'Due date',
                labelFromTime: 'From time',
                labelToTime: 'To time',
                inputDescriptionPlaceholder: 'Description',
                labelDescription: 'Description',
                inputLinkPlaceholder: 'appointment link',
                labelLink: 'Link',
                labelAppointmentRule: 'Appointment duration',
                appointmentRuleNone: 'No duration rule',
                appointmentRuleCoaching45: '45-minute coaching blocks',
                appointmentRuleFixed60: 'Exactly 60 minutes',
                appointmentRuleHelp: 'Drag-create and drag-resize respect the selected duration rule.',
                btnCancel: 'Cancel',
                btnSave: 'Save',
                modalAddTitle: 'Add appointment',
                modalEditTitle: 'Edit appointment',
                modalCreate: 'Create',
                modalUpdate: 'Update',
                untitled: 'Untitled appointment',
                yearAppointments: 'appointments',
                appointmentPrefix: 'Appointment',
                appointmentDescriptionPrefix: 'Demo appointment'
            };
            const demoLocations = [
                'Room 1',
                ['Room 2', 'Building A'],
                'Meeting room',
                ['Studio 3', '2nd floor'],
                'Online'
            ];
            */

            // Temporary in-memory store. Each year is generated once and then reused.
            // Example: appointmentStore[2026] = [{...}, {...}]
            //const appointmentStore = {};
            //let appointmentIdCounter = 1;

            initAppointmentModal();
            initCalendar();
            bindCalendarEvents();
            bindSidebarActions();
            /** 初始化 bs-calendar 主元件、語系翻譯、分類篩選與複製排程回呼 */
            function initCalendar() {
                let eventCounter = 1;
                $calendar.bsCalendar({
                    title: '<span class="h4 mb-0">全站排程與通知日曆</span>',
                    locale: 'zh-TW',
                    translations: FSL_TRANSLATIONS,
                    startView: 'month',
                    views: ['year', 'month', 'monthCompact', 'agenda', 'week', 'workweek', '4day', 'day', 'timeline'],
                    startWeekOnSunday: false,
                    navigateOnWheel: false,
                    draggable: $('#switchToggleDraggable').is(':checked'),
                    clickSnapMode: $('#selectClickSnapMode').val(),
                    storeState: false,
                    debug: false,
                    url: loadAppointments,
                    holidays: null,
                    hourSlots: {
                        start: '06:30',
                        end: '20:30',
                        height: 100,
                        timeLabelIntervalMinutes: $('#switchQuarterHourLabels').is(':checked') ? 15 : null
                    },
                    // Dated availability ranges repeat through the shared recurrence format.
                    availabilityRules: null,
                    calendars: FSL_CALENDARS,
                    /** 點擊資訊窗「複製」按鈕：自動複製一筆新排程寫入資料庫並重整 */
                    async onDuplicate(appointment) {
                        const copyData = { ...appointment, title: '[複製] ' + String(appointment.title || '').replace(/^🔔\s*/, '') };
                        delete copyData.id;
                        delete copyData.recurringId;
                        const res = await callSchApi('func_bs_cal_save', copyData);
                        if (res.RTN === 1) {
                            $calendar.bsCalendar('refresh');
                            if ($.bsToast) $.bsToast({ title: '已複製排程', message: res.msgStr });
                        }
                    },
                    onAll(eventName, ...args) {
                        if (eventName.startsWith('init.')) {
                            $('#temp_loading').remove();
                        }
                        if ($('#switchTriggeredEvents').is(':checked')) {
                            $.bsToast({
                                title: 'Event ' + eventCounter,
                                message: [
                                    eventName + ' (' + args.length + ' args)',
                                    `<i class="small text-muted">For more details see the console</i>`
                                ].join('<br>')
                            });
                            console.log(eventCounter, eventName, ...args);
                            eventCounter++;
                        } else {
                            $.bsToast.closeAll();
                        }
                    }
                });
            }
            /** 綁定日曆核心互動事件（點擊/拖曳新增、編輯改期、作廢刪除、任務核取切換） */
            function bindCalendarEvents() {
                $calendar
                    .on('change', '#switchToggleDraggable', function() {
                        $calendar.bsCalendar('updateOptions', {
                            draggable: $(this).is(':checked')
                        });
                    })
                    .on('change', '#switchQuarterHourLabels', function() {
                        $calendar.bsCalendar('updateOptions', {
                            hourSlots: {
                                timeLabelIntervalMinutes: $(this).is(':checked') ? 15 : null
                            }
                        });
                    })
                    .on('add.bs.calendar', function(event, data, dragExtras) {
                        const start = dragExtras?.start || data.start;
                        const end = dragExtras?.end || data.end;
                        const isAllDay = !start.time && !end.time;

                        openAppointmentModal('add', {
                            title: '',
                            startDate: start.date,
                            endDate: end.date,
                            startTime: resolveModalTime(start.time, null),
                            endTime: resolveModalTime(end.time, null),
                            allDay: isAllDay,
                            overlap: false,
                            description: '',
                            location: '',
                            color: getRandomAppointmentColor(),
                            link: ''
                        });
                    })
                    .on('edit.bs.calendar', function(event, appointment, extras, dragExtras) {
                        event.preventDefault();
                        /** 若為週期展開的子項目，取 recurringId 作為主排程 ID 並還原原始起始結束時間 */
                        const sourceAppointment = appointment.recurringId
                            ? { ...appointment, id: appointment.recurringId, start: appointment.extras?.recurrence?.sourceStart || appointment.start }
                            : appointment;
                        const eventExtras = dragExtras || extras;
                        const sourceStart = parseDateTimeString(sourceAppointment.start);
                        const sourceEnd = parseDateTimeString(sourceAppointment.end);
                        const isAllDay = !!sourceAppointment.allDay;
                        const startDateCandidate = dragExtras?.start?.date || (sourceStart ? formatDate(sourceStart) : eventExtras.start.date);
                        const endDateCandidate = dragExtras?.end?.date || (sourceEnd ? formatDate(sourceEnd) : eventExtras.end.date);
                        const startTimeCandidate = dragExtras?.start?.time || (sourceStart ? formatDateTime(sourceStart) : sourceAppointment?.start);
                        const endTimeCandidate = dragExtras?.end?.time || (sourceEnd ? formatDateTime(sourceEnd) : sourceAppointment?.end);

                        console.log('Editing appointment extras:', extras.colors);

                        openAppointmentModal('edit', {
                            id: sourceAppointment.id,
                            title: sourceAppointment.title,
                            startDate: startDateCandidate,
                            endDate: endDateCandidate,
                            startTime: isAllDay ? '' : resolveModalTime(startTimeCandidate, sourceAppointment.start),
                            endTime: isAllDay ? '' : resolveModalTime(endTimeCandidate, sourceAppointment.end),
                            allDay: isAllDay,
                            overlap: !!sourceAppointment.overlap,
                            recurrence: sourceAppointment.recurrence || null,
                            calendarId: sourceAppointment.calendarId || 'course',
                            triggerEn: sourceAppointment.triggerEn ?? 0,
                            description: sourceAppointment.description || '',
                            location: sourceAppointment.location || '',
                            color: extras?.colors?.hex || sourceAppointment.color || '#0d6efd',
                            link: sourceAppointment.link || '',
                            task: sourceAppointment.task || null
                        });
                    })
                    .on('delete.bs.calendar', async function(event, appointment) {
                        // 刪除事件處理邏輯
                        event.preventDefault();
                        if (!confirm('確定要作廢刪除此排程？')) return;
                        const targetId = appointment.recurringId || appointment.id;
                        const res = await callSchApi('func_sch_delete', {
                            schID: targetId
                        });
                        if (res.RTN === 1) {
                            $calendar.bsCalendar('refresh');
                            if ($.bsToast) $.bsToast({
                                title: '已刪除',
                                message: res.msgStr
                            });
                        } else {
                            alert(res.msgStr || '刪除失敗');
                        }

                    }).on(['task', 'status', 'changed.bs.calendar'].join('-'), async function(event, appointment) {
                        // 下一則貼內部邏輯
                        const targetId = appointment.recurringId || appointment.id;
                        const isDone = !!(appointment.task && appointment.task.checked);
                        const res = await callSchApi('func_bs_cal_toggleTask', {
                            id: targetId,
                            checked: isDone
                        });
                        if (res.RTN === 1 && $.bsToast) {
                            $.bsToast({
                                title: '任務狀態已更新',
                                message: res.msgStr
                            });
                        }
                    });
            }
            /** 取得新增排程時的預設隨機色碼（優先使用 bsColorPicker 工具，否則回傳藍色） */
            function getRandomAppointmentColor() {
                if ($.bsColorPicker?.utils?.getRandomColor) {
                    return $.bsColorPicker.utils.getRandomColor('rgba', .7);
                }
                return '#0d6efd';
            }
            /** 綁定左側控制面板與右側 Offcanvas 測試按鈕事件 */
            function bindSidebarActions() {
                // 1. 深色/淺色模式切換與 2. 重新同步資料按鈕事件
                $calendar.on('change', '#flexSwitchCheckTheme', function () {
                    $('html').attr('data-bs-theme', $(this).is(':checked') ? 'dark' : 'light');
                    $calendar.bsCalendar('render');
                }).on('click', '[data-method="refresh"]', function (e) {
                    e.preventDefault();
                    $calendar.bsCalendar('refresh');
                });
                $(document).on('click', '[data-cal-cmd]', function (e) {
                    e.preventDefault();
                    const cmd = $(this).data('cal-cmd');
                    const view = $(this).data('view');
                    if (cmd === 'setToday') $calendar.bsCalendar('setToday', view || 'week');
                    else $calendar.bsCalendar(cmd);
                });
                $(document).on('change', '#switchTriggeredEvents', function () {
                    $calendar.bsCalendar('refresh');
                }).on('change', '#selectClickSnapMode', function () {
                    $calendar.bsCalendar('updateOptions', { clickSnapMode: $(this).val() });
                });
                $(document).on('change', '#selectAppointmentRule', function () {
                    $calendar.bsCalendar('updateOptions', {
                        appointmentRules: getDemoAppointmentRules($(this).val())
                    });
                    $calendar.bsCalendar('render');
                });
            }

            /*
            function bindSidebarActions() {
                $calendar.on('click', '[data-method]', function(event) {
                    event.preventDefault();

                    switch ($(this).data('method')) {
                        case 'clear':
                            $calendar.bsCalendar('clear');
                            break;
                        case 'refresh':
                            $calendar.bsCalendar('refresh');
                            break;
                        case 'render':
                            $calendar.bsCalendar('render');
                            break;
                        case 'setToday':
                            $calendar.bsCalendar('setToday', $(this).data('view') || 'week');
                            break;
                        case 'setView':
                            $calendar.bsCalendar('setView', $(this).data('view') || 'month');
                            break;
                        case 'setLocale':
                            $calendar.bsCalendar('setLocale', $(this).data('locale') || 'month');
                            break;
                        default:
                            break;
                    }
                });
            }
            
            function applyDemoText() {
                const t = demoText;

                $('[data-method="refresh"]').text(t.btnRefresh);
                $('[data-method="clear"]').text(t.btnClear);
                $('#appointmentModalLabel').text(t.modalTitle);

                $('input[name="title"]').attr('placeholder', t.inputTitlePlaceholder);
                $('label[for="appointmentTitle"]').text(t.labelTitle);
                $('label[for="appointmentFromDate"]').text(t.labelFromDate);
                $('label[for="appointmentToDate"]').text(t.labelToDate);
                $('label[for="appointmentAllDay"]').text(t.labelAllDay);
                $('label[for="appointmentOverlap"]').text(t.labelOverlap);
                $('label[for="appointmentRecurrenceFrequency"]').text(t.labelRecurrenceFrequency);
                $('label[for="appointmentRecurrenceUntil"]').text(t.labelRecurrenceUntil);
                $('#appointmentRecurrenceFrequency option[value="none"]').text(t.recurrenceNone);
                $('#appointmentRecurrenceFrequency option[value="daily"]').text(t.recurrenceDaily);
                $('#appointmentRecurrenceFrequency option[value="weekly"]').text(t.recurrenceWeekly);
                $('#appointmentRecurrenceFrequency option[value="monthly"]').text(t.recurrenceMonthly);
                $('#appointmentRecurrenceFrequency option[value="yearly"]').text(t.recurrenceYearly);
                $('label[for="appointmentIsTask"]').text(t.labelIsTask);
                $('label[for="priorityLow"]').text(t.labelPriorityLow);
                $('label[for="priorityMedium"]').text(t.labelPriorityMedium);
                $('label[for="priorityHigh"]').text(t.labelPriorityHigh);
                $('label[for="appointmentDue"]').text(t.labelDue);
                $('label[for="appointmentFromTimePicker"]').text(t.labelFromTime);
                $('label[for="appointmentToTimePicker"]').text(t.labelToTime);
                $('textarea[name="description"]').attr('placeholder', t.inputDescriptionPlaceholder);
                $('label[for="appointmentDescription"]').text(t.labelDescription);
                $('input[name="link"]').attr('placeholder', t.inputLinkPlaceholder);
                $('label[for="appointmentLink"]').text(t.labelLink);
                $('label[for="selectAppointmentRule"]').text(t.labelAppointmentRule);
                $('#selectAppointmentRule option[value="none"]').text(t.appointmentRuleNone);
                $('#selectAppointmentRule option[value="coaching45"]').text(t.appointmentRuleCoaching45);
                $('#selectAppointmentRule option[value="fixed60"]').text(t.appointmentRuleFixed60);
                $('[data-appointment-rule-help]').text(t.appointmentRuleHelp);
                $('[data-bs-dismiss="modal"].btn.btn-secondary').text(t.btnCancel);
                $('[data-save-appointment]').text(t.btnSave);

                if (typeof $.fn.bsDatepicker === 'function') {
                    $dateRange.bsDatepicker('setLocale', t.locale);
                }
                //applyCalendarLabels(t.calendars);
            }
            
            function applyCalendarLabels(calendars) {
                if (!Array.isArray(calendars)) {
                    return;
                }
                const titleById = calendars.reduce(function(acc, calendar) {
                    if (calendar && calendar.id) {
                        acc[calendar.id] = calendar.title;
                    }
                    return acc;
                }, {});

                $calendar.find('[data-calendar-toggle]').each(function() {
                    const id = $(this).data('calendar-toggle');
                    if (!id || !titleById[id]) {
                        return;
                    }
                    $(this).find('span').first().text(titleById[id]);
                });
            }
            */
            /** 依下拉選單代碼回傳對應的排程時長限制規則（如固定 60 分鐘或 45 分鐘倍數） */
            function getDemoAppointmentRules(rule) {
                switch (rule) {
                    case 'coaching45':
                        return {
                            durationStepMinutes: 45,
                                minDurationMinutes: 45
                        };
                    case 'fixed60':
                        return {
                            durationMinutes: 60
                        };
                    case 'none':
                    default:
                        return {
                            durationMinutes: null,
                                durationStepMinutes: null,
                                minDurationMinutes: null,
                                maxDurationMinutes: null
                        };
                }
            }
            /** 初始化新增/編輯排程彈窗內的時間選擇器與「全天 / 任務 / 儲存」表單事件 */
            function initAppointmentModal() {
                initModalTimepickers();

                $modal
                    .on('hide.bs.modal', function() {
                        $calendar.bsCalendar('render');
                    })
                    .on('change', 'input[name="allDay"]', function() {
                        $modal.find('.js-time-fields').toggle(!$(this).prop('checked'));
                    })
                    .on('change', 'input[name="isTask"]', function() {
                        $modal.find('.js-priority-fields').toggle($(this).prop('checked'));
                    })
                    .on('click', '[data-save-appointment]', async function() {
                        const appt = readAppointmentModal();
                        const res = await callSchApi('func_bs_cal_save', appt);
                        if (res.RTN === 1) {
                            $modal.modal('hide');
                            $calendar.bsCalendar('refresh');
                            if ($.bsToast) $.bsToast({
                                title: '成功',
                                message: res.msgStr
                            });
                        } else {
                            alert(res.msgStr || '儲存失敗');
                        }
                    });
            }
            /** 批次初始化彈窗內的開始時間與結束時間選擇器（bsTimepicker） */
            function initModalTimepickers() {
                if (typeof $.fn.bsTimepicker !== 'function') {
                    return;
                }

                $modal.find('#appointmentFromTimePicker, #appointmentToTimePicker').each(function() {
                    ensureTimepickerInitialized($(this));
                });
            }
            /** 確保單一時間選擇器已完成實例化（避免重複初始化） */
            function ensureTimepickerInitialized($input) {
                if (typeof $.fn.bsTimepicker !== 'function' || !$input || !$input.length) {
                    return false;
                }
                if ($input.data('bsTimepicker')) {
                    return true;
                }
                $input.bsTimepicker({
                    format: '24h',
                    minuteInterval: 1,
                    btnClass: 'btn btn-outline-secondary w-100 text-start',
                    nameField: null,
                    btnEmptyText: '--:--'
                });
                return !!$input.data('bsTimepicker');
            }
            /** 開啟新增或編輯排程彈窗，並將排程資料填入表單欄位與設定詳細頁面跳轉連結 */
            function openAppointmentModal(mode, appointment) {
                const t = demoText;
                modalState.mode = mode;
                modalState.appointmentId = appointment.id || null;
                modalState.taskChecked = !!appointment.task?.checked;
                $modal.find('.modal-title').text(mode === 'edit' ? t.modalEditTitle : t.modalAddTitle);
                $modal.find('[data-save-appointment]').text(mode === 'edit' ? t.modalUpdate : t.modalCreate);
                const cleanTitle = String(appointment.title || '').replace(/^🔔\s*/, '');
                $modal.find('input[name="title"]').val(cleanTitle);
                $modal.find('#appointmentCalendarId').val(appointment.calendarId || 'course');
                $modal.find('#appointmentTriggerEnable').prop('checked', Number(appointment.triggerEn) === 1);
                const $goBtn = $('#btnGoDetail');
                const rawIdStr = String(appointment.id || '');
                if (mode === 'edit' && rawIdStr.startsWith('less_')) {
                    const targetHref = (appointment.link && appointment.link.href) ? appointment.link.href : 'cs_list.php';
                    $goBtn.attr('href', targetHref).html('<i class="bi bi-mortarboard"></i> 前往所屬課程管理').removeClass('d-none');
                } else if (mode === 'edit' && rawIdStr.startsWith('sch_')) {
                    const realSchId = rawIdStr.replace('sch_', '');
                    $goBtn.attr('href', `sch_detail.php?currID=${realSchId}&fromCal=1`).html('<i class="bi bi-sliders"></i> 進階通知對象設定').removeClass('d-none');
                } else {
                    $goBtn.addClass('d-none');
                }

                // console.log(mode, appointment);
                if (typeof $.fn.bsDatepicker === 'function') {
                    $dateRange.bsDatepicker('val', [appointment.startDate, appointment.endDate]);
                } else {
                    $modal.find('input[name="from_date"]').val(appointment.startDate);
                    $modal.find('input[name="to_date"]').val(appointment.endDate);
                }

                setModalTimeValue('from_time', appointment.startTime || '');
                setModalTimeValue('to_time', appointment.endTime || '');
                $modal.find('input[name="allDay"]').prop('checked', !!appointment.allDay).trigger('change');
                $modal.find('input[name="overlap"]').prop('checked', !!appointment.overlap);
                $modal.find('select[name="recurrence_frequency"]').val(appointment.recurrence?.frequency || 'none');
                $modal.find('input[name="recurrence_until"]').val(appointment.recurrence?.until || '');

                const isTask = !!appointment.task;
                const priority = isTask ? appointment.task.priority : 'normal';
                const due = isTask ? appointment.task.due : '';

                $modal.find('input[name="isTask"]').prop('checked', isTask).trigger('change');
                $modal.find(`input[name="priority"][value="${priority || 'normal'}"]`).prop('checked', true);
                $modal.find('input[name="due"]').val(due ? due.substring(0, 16).replace(' ', 'T') : '');

                $modal.find('textarea[name="description"]').val(appointment.rawDesc || appointment.description || '');
                $modal.find('textarea[name="location"]').val(formatLocationValue(appointment.location));
                $colorpicker.bsColorPicker('val', appointment.color);
                // $modal.find('input[name="color"]').val(normalizeColorInput(appointment.color));
                $modal.find('input[name="link"]').val(appointment.link || '');
                $modal.modal('show');
            }
            /** 設定彈窗內開始或結束時間選擇器的值（自動標準化為 HH:mm 格式） */
            function setModalTimeValue(fieldName, value) {
                const selector = fieldName === 'from_time' ? '#appointmentFromTimePicker' : '#appointmentToTimePicker';
                const $input = $modal.find(selector);
                const normalized = normalizeTimeInputValue(value);
                if (ensureTimepickerInitialized($input)) {
                    if (normalized) {
                        $input.bsTimepicker('setTime', normalized);
                    } else {
                        $input.bsTimepicker('clear');
                    }
                    return;
                }
                $input.val(normalized);
            }
            /** 讀取彈窗內開始或結束時間選擇器的目前時間字串 */
            function getModalTimeValue(fieldName) {
                const selector = fieldName === 'from_time' ? '#appointmentFromTimePicker' : '#appointmentToTimePicker';
                const $input = $modal.find(selector);
                if (ensureTimepickerInitialized($input)) {
                    return normalizeTimeInputValue($input.bsTimepicker('val'));
                }
                return normalizeTimeInputValue($input.val());
            }
            /** 讀取彈窗內所有表單欄位值，組裝為符合後端與 bs-calendar 格式的排程物件 */
            function readAppointmentModal() {
                const t = demoText;
                const allDay = $modal.find('input[name="allDay"]').prop('checked');
                const isTask = $modal.find('input[name="isTask"]').is(':checked');
                const appointment = {
                    title: $modal.find('input[name="title"]').val() || t.untitled,
                    start: buildDateTime(
                        $modal.find('input[name="from_date"]').val(),
                        getModalTimeValue('from_time'),
                        allDay
                    ),
                    end: buildDateTime(
                        $modal.find('input[name="to_date"]').val(),
                        getModalTimeValue('to_time'),
                        allDay,
                        true
                    ),
                    allDay: allDay,
                    overlap: $modal.find('input[name="overlap"]').is(':checked'),
                    task: isTask ? {
                        checked: modalState.mode === 'edit' ? modalState.taskChecked : false,
                        priority: $modal.find('input[name="priority"]:checked').val(),
                        due: $modal.find('input[name="due"]').val() ? $modal.find('input[name="due"]').val().replace('T', ' ') + ':00' : null
                    } : null,
                    calendarId: $('#appointmentCalendarId').val() || 'course',
                    trigger_enable: $('#appointmentTriggerEnable').is(':checked') ? 1 : 0,
                    description: $modal.find('textarea[name="description"]').val(),
                    location: parseLocationValue($modal.find('textarea[name="location"]').val()),
                    color: $.bsCalendar?.utils?.toHex ? $.bsCalendar.utils.toHex($modal.find('input[name="color"]').val()) : '#3b82f6',
                    link: $modal.find('input[name="link"]').val()
                };

                const recurrenceFrequency = $modal.find('select[name="recurrence_frequency"]').val();
                if (recurrenceFrequency && recurrenceFrequency !== 'none') {
                    const recurrenceStart = parseDateTimeString(appointment.start);
                    appointment.recurrence = {
                        frequency: recurrenceFrequency,
                        interval: 1
                    };
                    if (recurrenceFrequency === 'weekly' && recurrenceStart) {
                        appointment.recurrence.daysOfWeek = [recurrenceStart.getDay()];
                    }
                    const recurrenceUntil = $modal.find('input[name="recurrence_until"]').val();
                    if (recurrenceUntil) {
                        appointment.recurrence.until = recurrenceUntil;
                    }
                }

                if (modalState.appointmentId) {
                    appointment.id = modalState.appointmentId;
                }

                return appointment;
            }
            /** 將地點資料（字串或陣列）轉換為多行換行文字，供 textarea 顯示 */
            function formatLocationValue(location) {
                if (Array.isArray(location)) {
                    return location.join('\n');
                }
                return location || '';
            }
            /** 將 textarea 多行地點文字拆解為單一字串或字串陣列 */
            function parseLocationValue(value) {
                const locations = String(value || '')
                    .split(/\r?\n/)
                    .map(location => location.trim())
                    .filter(Boolean);
                if (locations.length > 1) {
                    return locations;
                }
                return locations[0] || '';
            }
            /** 組合日期與時間為標準資料庫格式（YYYY-MM-DD HH:mm:ss），全天事件自動補 00:00:00 或 23:59:59 */
            function buildDateTime(date, time, allDay, isEnd = false) {
                if (allDay) {
                    return `${date} ${isEnd ? '23:59:59' : '00:00:00'}`;
                }
                return `${date} ${time || (isEnd ? '23:59' : '00:00')}:00`;
            }
            /** 驗證並標準化 Hex 色彩代碼（無效色碼時回傳預設藍色 #0d6efd） */
            function normalizeColorInput(color) {
                return /^#[0-9a-f]{6}$/i.test(color || '') ? color : '#0d6efd';
            }
            /** 將各種時間字串格式正規化為兩位數的 HH:mm 格式 */
            function normalizeTimeInputValue(time) {
                const match = String(time || '').match(/(?:^|\s|T)(\d{1,2}):(\d{2})(?::\d{2})?/);
                if (!match) {
                    return '';
                }
                return `${match[1].padStart(2, '0')}:${match[2]}`;
            }
            /** 將 YYYY-MM-DD HH:mm:ss 字串安全解析為 JavaScript Date 物件 */
            function parseDateTimeString(str) {
                const match = String(str || '').match(
                    /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/
                );
                if (!match) {
                    return null;
                }
                const year = Number(match[1]);
                const month = Number(match[2]) - 1;
                const day = Number(match[3]);
                const hours = Number(match[4] || 0);
                const minutes = Number(match[5] || 0);
                const seconds = Number(match[6] || 0);
                const date = new Date(year, month, day, hours, minutes, seconds, 0);
                return isNaN(date.getTime()) ? null : date;
            }
            /** 從 Date 物件或日期時間字串中提取 HH:mm 時間部分 */
            function extractTimeFromDateTime(value) {
                if (!value) {
                    return '';
                }
                if (value instanceof Date && !isNaN(value.getTime())) {
                    return `${String(value.getHours()).padStart(2, '0')}:${String(value.getMinutes()).padStart(2, '0')}`;
                }
                const str = String(value);
                const direct = normalizeTimeInputValue(str);
                if (direct) {
                    return direct;
                }
                const date = parseDateTimeString(str) || new Date(str);
                if (!isNaN(date.getTime())) {
                    return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
                }
                return '';
            }
            /** 優先採用指定時間，若無則從備援日期時間字串中提取時間 */
            function resolveModalTime(preferredTime, fallbackDateTime) {
                return normalizeTimeInputValue(preferredTime) || extractTimeFromDateTime(fallbackDateTime);
            }
            
            
            function formatDateTime(date) {
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                const hh = String(date.getHours()).padStart(2, '0');
                const ii = String(date.getMinutes()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd} ${hh}:${ii}:00`;
            }

            function formatDate(date) {
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            }
            /** 非同步延遲等待輔助函式（毫秒） */
            function sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            //---------------------------- 頁面必備基本函式 -------------------------------//
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

            $('body').on('keypress click', function() {
                let timerElm = window.parent.$('#timer_on_exhausted');
                if (timerElm.length) timerElm.backward_timer('reset');
            });

            if (window.parent && typeof window.parent.treeviewCheck === 'function') {
                window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
            }
            //---------------------------- //頁面必備基本函式 -------------------------------//

        });
    </script>
</body>

</html>