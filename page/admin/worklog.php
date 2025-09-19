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
  box-shadow: 0 3px 8px rgba(0,0,0,0.1);
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
  box-shadow: 0 0 8px rgba(0,123,255,0.6);
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
    color: #bbb; /* pale gray */
    opacity: 0.5;
}
.today {
    font-weight: bold;
    color: #000;    
}
button.btn-success.red {
    background-color: #dc3545 !important; /* Bootstrap red */
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
                <i class="nav-icon fas fa-business-time"></i> Work Log  <span id="username" style="display:none;"><?= htmlspecialchars($_SESSION['username']); ?></span>


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
      $months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
      foreach($months as $i => $m){
        echo "<div class='col-3'><button class='btn btn-outline-primary btn-sm w-100 month-btn' data-month='$i' onclick='setMonthAndHighlight(this, $i)'>$m</button></div>";
      }
    ?>
  </div>
  <input type="number" id="year-input" class="form-control text-center" value="<?php echo date('Y'); ?>" min="1900" max="2100">
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
  const phOffset = 8 * 60; // UTC+8 in minutes
  const localOffset = now.getTimezoneOffset(); // in minutes
  return new Date(now.getTime() + (phOffset + localOffset) * 60 * 1000);
}

function padZero(n) { return n < 10 ? '0' + n : n; }

// ===== Variables =====
let today = getPhilippineTime();
let currentMonth = today.getMonth();
let currentYear = today.getFullYear();
let selectedDate = today;

const months = ["January","February","March","April","May","June","July",
                "August","September","October","November","December"];

const calendarTitle = document.getElementById("calendar-title");
let calendarModal;

// ===== Generate Calendar =====
function generateCalendar(month, year) {
  const calendarBody = document.getElementById("calendar-body");
  calendarBody.innerHTML = "";

  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const firstDay = new Date(year, month, 1).getDay(); // 0=Sun, 1=Mon...

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
    if(parseInt(btn.dataset.month) === currentMonth){
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
document.getElementById("year-input").addEventListener("keydown", function(e) {
  if (e.key === "Enter") applyCalendarSelection();
});

// ===== Time In/Out Logic =====
let isTimeIn = true;
const btn = document.querySelector('button.btn-success');
const timeInInput = document.getElementById('time_in');
const timeOutInput = document.getElementById('time_out');
const username = document.getElementById('username').textContent.trim();
const formatDate = d => d.toISOString().split('T')[0];
let timeOutInterval;

// Highlight selected date
document.getElementById('calendar-body').addEventListener('click', e => {
  if(e.target && e.target.textContent && !e.target.classList.contains('empty-day')){
    const day = parseInt(e.target.textContent);
    selectedDate = new Date(currentYear, currentMonth, day);
    document.querySelectorAll('#calendar-body div').forEach(div => div.classList.remove('selected-day'));
    e.target.classList.add('selected-day');

    // ✅ check log whenever you change date
    checkLogStatus();
  }
});

// Set default Time In/Out
const phNow = getPhilippineTime();
timeInInput.value = padZero(phNow.getHours()) + ':' + padZero(phNow.getMinutes());
timeOutInput.value = padZero(phNow.getHours()) + ':' + padZero(phNow.getMinutes());

// Auto-update Time Out
function startTimeOutClock() {
  if(timeOutInterval) clearInterval(timeOutInterval);
  timeOutInterval = setInterval(() => {
    const now = getPhilippineTime();
    timeOutInput.value = padZero(now.getHours()) + ':' + padZero(now.getMinutes());
  }, 1000);
}
startTimeOutClock();

// === Check existing log (from timelog.php) ===
function checkLogStatus() {
  fetch('../../process/timelog.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ 
      action: 'get_log',
      username: username,
      date: formatDate(selectedDate)
    })
  })
  .then(res => res.json())
  .then(data => {
    if(data.status === 'success'){
      if(data.time_in){
        timeInInput.value = data.time_in;
        timeInInput.readOnly = true;
      }
      if(data.time_out){
        timeOutInput.value = data.time_out;
        timeOutInput.readOnly = true;

        btn.classList.remove('red');
        btn.innerHTML = '<i class="fas fa-clock"></i> Time In';
        isTimeIn = true;
      } else {
        // has time_in but no time_out yet
        btn.classList.add('red');
        btn.innerHTML = '<i class="fas fa-clock"></i> Time Out';
        isTimeIn = false;
      }
    } else {
      // no log yet
      btn.classList.remove('red');
      btn.innerHTML = '<i class="fas fa-clock"></i> Time In';
      isTimeIn = true;
      timeInInput.readOnly = false;
      timeOutInput.readOnly = false;
    }
  })
  .catch(err => console.error("Log status check failed", err));
}

// Initial button state
btn.classList.remove('red');
btn.innerHTML = '<i class="fas fa-clock"></i> Time In';
checkLogStatus(); // ✅ run on page load

// === Click handler ===
btn.addEventListener('click', function(){
  btn.disabled = true;
  const action = isTimeIn ? 'time_in' : 'time_out';
  const timeValue = isTimeIn ? timeInInput.value : timeOutInput.value;

  fetch('../../process/timelog.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ 
      action: action,
      username: username,
      time: timeValue,
      date: formatDate(selectedDate)
    })
  })
  .then(res => res.json())
  .then(data => {
    if(data.status === 'success'){
      if(isTimeIn){
        timeInInput.value = data.time;
        timeInInput.readOnly = true; 
        isTimeIn = false;
        btn.classList.add('red');
        btn.innerHTML = '<i class="fas fa-clock"></i> Time Out';
        Swal.fire({ icon: 'success', title: 'Time In Saved!', timer: 1000, showConfirmButton: false });
      } else {
        timeOutInput.value = data.time;
        timeOutInput.readOnly = true;
        isTimeIn = true;
        btn.classList.remove('red');
        btn.innerHTML = '<i class="fas fa-clock"></i> Time In';
        Swal.fire({ icon: 'success', title: 'Time Out Saved!', timer: 1000, showConfirmButton: false });
      }
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: data.msg });
    }
    btn.disabled = false;
  })
  .catch(err => {
    Swal.fire({ icon: 'error', title: 'Request Failed', text: err });
    btn.disabled = false;
  });
});

</script>



<?php
include 'plugins/footer.php';
?>
