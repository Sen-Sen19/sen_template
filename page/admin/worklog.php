<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>

<style>
  /* 📅 Calendar Layout */
  .calendar-wrapper {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
  }

  .calendar-header {
    background: #f8f9fa;
    border-radius: 10px;
  }

  .calendar-nav h5 {
    margin: 0;
    cursor: pointer;
  }

  .calendar-controls button {
    min-width: 36px;
  }

  /* Grid for days/dates */
  .calendar-days,
  .calendar-dates {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
  }

  .calendar-days div {
    padding: 6px;
    background: #e9ecef;
    border-radius: 6px;
  }

  .calendar-dates div {
    height: 70px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    border-radius: 10px;
    transition: 0.2s;
  }

  .calendar-dates div:hover {
    background: #f1f3f5;
  }

  /* Highlight Today */
  .calendar-dates div.today {
    background: #007bff;
    color: #fff;
    font-weight: bold;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.6);
  }

  .month-btn.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
  }

  .calendar-dates div:not(.other-month):hover {
    background: #f1f3f5;
  }

  .past-day {
    color: #bbb;
    /* pale gray */
    opacity: 0.5;
  }

  .today {
    font-weight: bold;
    color: #000;
  }

  button.btn-success.red {
    background-color: #dc3545 !important;
    /* Bootstrap red */
    border-color: #dc3545 !important;
    color: #fff !important;
  }
</style>

<div class="content-wrapper">
  <div class="content-header"></div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12">
          <div class="card card-gray-dark card-outline">
            <div class="card-header">
              <h3 class="card-title">
                <i class="nav-icon fas fa-business-time"></i> Work Log <span id="username"
                  style="display:none;"><?= htmlspecialchars($_SESSION['username']); ?></span>


              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                  <i class="fas fa-expand"></i>
                </button>
              </div>
            </div>

            <div class="card-body">

              <!-- 📅 Professional Calendar Layout -->
              <div class="calendar-wrapper mb-4">
                <!-- Calendar Header -->
                <div class="calendar-header d-flex justify-content-between align-items-center mb-3 p-2">

                  <!-- Title in the Center -->
                  <div class="calendar-nav text-center flex-grow-1">
                    <h5 id="calendar-title" class="mb-0 fw-bold cursor-pointer" onclick="openCalendarModal()"></h5>
                  </div>

                  <!-- Controls -->
                  <div class="calendar-controls d-flex align-items-center gap-2">
                    <button class="btn btn-light btn-sm rounded-circle shadow-sm" onclick="prevMonth()">
                      <i class="fas fa-chevron-left"></i>
                    </button>

                    <button class="btn btn-primary btn-sm px-3 mx-1" onclick="goToday()">
                      <i class="fas fa-calendar-day"></i> Today
                    </button>

                    <button class="btn btn-light btn-sm rounded-circle shadow-sm" onclick="nextMonth()">
                      <i class="fas fa-chevron-right"></i>
                    </button>
                  </div>
                </div>

                <!-- Calendar Grid -->
                <div class="calendar-grid">
                  <div class="calendar-days d-grid text-center fw-bold">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                  </div>
                  <div id="calendar-body" class="calendar-dates d-grid text-center"></div>
                </div>`
              </div>

              <!-- 🕒 Time In / Time Out -->
              <div class="row align-items-end">
                <div class="col-md-5">
                  <label for="time_in">⏰ Time In</label>
                  <input type="time" id="time_in" class="form-control" value="<?php echo date('H:i'); ?>">
                </div>

                <div class="col-md-5">
                  <label for="time_out">🚪 Time Out</label>
                  <input type="time" id="time_out" class="form-control" value="<?php echo date('H:i'); ?>">
                </div>

                <div class="col-md-2">
                  <button class="btn btn-success btn-block">
                    <i class="fas fa-clock"></i> Time In
                  </button>

                </div>
              </div>

            </div><!-- card-body -->
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- 📌 Modal for selecting Month & Year -->
<div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Month & Year</h5>

      </div>
      <div class="modal-body text-center">
        <div class="row g-2 mb-3">
          <?php
          $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
          foreach ($months as $i => $m) {
            echo "<div class='col-3'><button class='btn btn-outline-primary btn-sm w-100 month-btn' data-month='$i' onclick='setMonthAndHighlight(this, $i)'>$m</button></div>";
          }
          ?>
        </div>
        <input type="number" id="year-input" class="form-control text-center" value="<?php echo date('Y'); ?>"
          min="1900" max="2100">
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary w-100" onclick="applyCalendarSelection()">Apply</button>
      </div>
    </div>
  </div>
</div>
<script>
  // ===== Philippine Time =====
  function getPhilippineTime() {
    const now = new Date();
    const utc = now.getTime() + now.getTimezoneOffset() * 60000;
    return new Date(utc + 8 * 3600000); // UTC+8
  }

  // Update time inputs every second
  function updateTimeInputs() {
    const phTime = getPhilippineTime();
    let hh = String(phTime.getHours()).padStart(2, '0');
    let mm = String(phTime.getMinutes()).padStart(2, '0');

    document.getElementById("time_in").value = `${hh}:${mm}`;
    document.getElementById("time_out").value = `${hh}:${mm}`;
  }

  // Run immediately and keep updating
  updateTimeInputs();
  setInterval(updateTimeInputs, 1000);

  // ===== Variables =====
  let today = getPhilippineTime();   // ✅ real Date object
  let currentMonth = today.getMonth();
  let currentYear = today.getFullYear();
  let selectedDate = today;

  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  const calendarTitle = document.getElementById("calendar-title");
  let calendarModal;

  // ===== Generate Calendar =====
  function generateCalendar(month, year) {
    const calendarBody = document.getElementById("calendar-body");
    calendarBody.innerHTML = "";

    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const firstDay = new Date(year, month, 1).getDay();

    // Add empty slots before 1st
    for (let i = 0; i < firstDay; i++) {
      const emptyDiv = document.createElement("div");
      emptyDiv.classList.add("empty-day");
      calendarBody.appendChild(emptyDiv);
    }

    // Fill current month
    for (let i = 1; i <= daysInMonth; i++) {
      const div = document.createElement("div");
      div.textContent = i;

      const thisDate = new Date(year, month, i);

      if (thisDate.toDateString() === today.toDateString()) {
        div.classList.add("today");
      } else if (thisDate < today) {
        div.classList.add("past-day");
      }

      calendarBody.appendChild(div);
    }

    calendarTitle.textContent = `${months[month]} ${year}`;
  }
  generateCalendar(currentMonth, currentYear);

  // ===== Navigation =====
  function prevMonth() {
    if (currentMonth === 0) {
      currentMonth = 11; currentYear--;
    } else currentMonth--;
    generateCalendar(currentMonth, currentYear);
  }

  function nextMonth() {
    if (currentMonth === 11) {
      currentMonth = 0; currentYear++;
    } else currentMonth++;
    generateCalendar(currentMonth, currentYear);
  }

  function goToday() {
    today = getPhilippineTime();
    currentMonth = today.getMonth();
    currentYear = today.getFullYear();
    generateCalendar(currentMonth, currentYear);
  }

  // ===== Modal Functions =====
  function openCalendarModal() {
    if (!calendarModal) {
      calendarModal = new bootstrap.Modal(document.getElementById("calendarModal"));
    }
    document.getElementById("year-input").value = currentYear;
    document.querySelectorAll(".month-btn").forEach(btn => {
      btn.classList.remove("active");
      if (parseInt(btn.dataset.month) === currentMonth) {
        btn.classList.add("active");
      }
    });
    calendarModal.show();
  }

  function setMonthAndHighlight(button, m) {
    currentMonth = m;
    document.querySelectorAll(".month-btn").forEach(btn => btn.classList.remove("active"));
    button.classList.add("active");
  }

  function applyCalendarSelection() {
    const year = parseInt(document.getElementById("year-input").value);
    if (!isNaN(year)) {
      currentYear = year;
      generateCalendar(currentMonth, currentYear);
    }
    calendarModal.hide();
  }

  document.getElementById("year-input").addEventListener("keydown", function (e) {
    if (e.key === "Enter") applyCalendarSelection();
  });
</script>


<?php
include 'plugins/footer.php';
?>