<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>

<style>
  .calendar-wrapper {
    display: none;
  }

  /* Layout */
  .card {
    border-radius: 12px;
    overflow: hidden;
  }

  .time-row label {
    font-weight: 600;
    color: #333;
    display: block;
    margin-bottom: 6px;
  }

  .time-row input.form-control {
    height: 42px;
    font-size: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
  }

  .time-row button {
    height: 42px;
    font-weight: 600;
    transition: all 0.2s ease-in-out;
  }

  .time-row button:hover {
    transform: scale(1.03);
  }


  /* Responsive */

  .calendar-wrapper {
    display: block;
    margin-top: 40px;
  }

  #calendarContainer table {
    width: 100%;
    border-collapse: collapse;
    text-align: center;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
  }

  #calendarContainer th {
    background-color: #343a40;
    color: white;
    padding: 10px;
  }

  #calendarContainer td {
    padding: 10px;
    border: 1px solid #ddd;
  }

  .status-present {
    background-color: #28a745;
    color: white;
    font-weight: bold;
  }

  .status-no-timeout {
    background-color: #ffc107;
    color: black;
    font-weight: bold;
  }

  .status-vl {
    background-color: #007bff;
    color: white;
    font-weight: bold;
  }

  .status-sl {
    background-color: #17a2b8;
    color: white;
    font-weight: bold;
  }

  .status-lwop {
    background-color: #dc3545;
    color: white;
    font-weight: bold;
  }

  .status-rdot {
    background-color: #2f00ffff;
    color: white;
    font-weight: bold;
  }

  .status-rd {
    background-color: #b9b9b9ff;
    color: white;
    font-weight: bold;
  }

  .status-no-work {
    background-color: #6c757d;
    color: white;
    font-weight: bold;
  }

  .status-holiday {
    background-color: #00ffbfff;
    color: white;
    font-weight: bold;
  }

  .status-Pending {
    background-color: #f8f9fa;
    color: #333;
  }



  #calendarContainer td {
    border-radius: 8px;
    transition: 0.2s;
  }

  #calendarContainer td:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  }

  #monthYearModal .form-select {
    border-radius: 8px;
  }

  #monthYearModal .modal-content {
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  }

  /* ✨ Glassy fade effect */
  .glassy-bg {
    background: rgba(255, 255, 255, 1);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #000000ff;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
  }

  .year-control {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 8px;
  }

  #monthGrid button {
    border-radius: 10px !important;

    color: #000000ff;
    background: rgba(255, 255, 255, 1);
    border: 1px solid rgba(0, 0, 0, 1);
    transition: all 0.2s ease-in-out;
  }

  #monthGrid button:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
  }

  #monthGrid .btn-dark {
    background: rgba(109, 109, 109, 0.8) !important;
    border-color: transparent !important;
  }
  
.row .form-select,
.row .form-control {
  min-height: calc(1.5em + 1rem + 2px); /* Adjust as needed */
  border-radius: 0.5rem;
  font-weight: 600;
}


.status-dropdown {
  transition: all 0.2s;
}


.status-dropdown option {
  font-weight: 600;
  padding: 0.5rem;
}
  </style>

<div class="content-wrapper">
  <div class="content-header"></div>

  <section class="content">
    <div class="container-fluid">
      <div class="card card-gray-dark card-outline">
        <div class="card-header">
          <h3 class="card-title">
            <i class="nav-icon fas fa-business-time"></i> Work Log
            <span id="username" style="display:none;"><?= htmlspecialchars($_SESSION['username']); ?></span>
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                class="fas fa-expand"></i></button>
          </div>
        </div>


        <div class="card-body">
          <!-- Time In / Time Out -->
          <div class="row align-items-end time-row">
            <div class="col-md-5 mb-3">
              <label for="time_in">⏰ Time In</label>
              <input type="time" id="time_in" class="form-control" readonly>
            </div>
            <div class="col-md-5 mb-3">
              <label for="time_out">⏰ Time Out</label>
              <input type="time" id="time_out" class="form-control" readonly>
            </div>
            <div class="col-md-2 mb-3">
              <button id="timeBtn" class="btn btn-success w-100">
                <i class="fas fa-clock"></i> Time In
              </button>
            </div>
          </div>


          <!-- Calendar Section -->
          <div class="calendar-wrapper mt-5" id="calendar-wrapper">
            <h5 class="mb-3"><i class="fas fa-calendar-alt"></i> Monthly Attendance</h5>



          </div>

          <div id="calendarContainer" class="mt-4"></div>
          <!-- Month-Year Modal -->
          <div class="modal fade glass-modal" id="monthYearModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
              <div class="modal-content glassy-bg">
                <div class="modal-header justify-content-center border-0">
                  <h5 class="modal-title fw-bold">Select Month & Year</h5>
                </div>

                <div class="modal-body text-center">
                  <!-- Month Buttons -->
                  <div id="monthGrid" class="row g-2 mb-3"></div>

                  <!-- Year Controls -->
                  <div class="year-control d-flex align-items-center justify-content-center mb-3">
                    <button id="yearDown" class="btn year-btn">
                      <i class="fas fa-chevron-down"></i>
                    </button>
                    <input id="yearDisplay" type="text" class="form-control text-center mx-2" readonly />
                    <button id="yearUp" class="btn year-btn">
                      <i class="fas fa-chevron-up"></i>
                    </button>
                  </div>
                </div>

                <div class="modal-footer border-0">
                  <button id="applyMonthYear" class="btn btn-primary w-100 fw-bold">
                    <i class="fas fa-check"></i> Apply
                  </button>
                </div>
              </div>
            </div>
          </div>






        </div>
      </div>
    </div>
  </section>
</div>
<div class="modal fade" id="dateRemarksModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">
          <i class="fas fa-calendar-day"></i> Date Details
        </h5>
        <button type="button" class="btn btn-dark fw-bold" style="font-size: 1.2rem;" data-bs-dismiss="modal" aria-label="Close">
          ×
        </button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="fw-bold">📅 Date:</label>
          <input type="text" id="modalDate" class="form-control" readonly>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="fw-bold">⏰ Time In:</label>
            <input type="time" id="modalTimeIn" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="fw-bold">🕒 Time Out:</label>
            <input type="time" id="modalTimeOut" class="form-control">
          </div>
        </div>

        <!-- Two-column row for Status and Remarks with same height -->
        <div class="row g-2 mb-3 align-items-end">
          <div class="col-md-4 d-flex flex-column">
            <label class="fw-bold">📌 Status:</label>
            <select id="modalStatus" class="form-select status-dropdown flex-grow-1">
              <option value="" selected disabled>-- Select Status --</option>
              <option value="LWOP">LWOP</option>
              <option value="RD">RD</option>
              <option value="VL">VL</option>
              <option value="SL">SL</option>
              <option value="HOLIDAY">HOLIDAY</option>
              <option value="NO WORK">NO WORK</option>
              <option value="RDOT">RDOT</option>
            </select>
          </div>

          <div class="col-md-8 d-flex flex-column">
            <label class="fw-bold">💬 Remarks:</label>
            <input type="text" id="modalRemarks" class="form-control flex-grow-1" placeholder="Enter notes or comments...">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary w-100 fw-bold" id="updateRemarksBtn">
          <i class="fas fa-save"></i> Save Changes
        </button>
        
      </div>
    </div>
  </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ===== Philippine Time =====
  function getPhilippineTime() {
    const now = new Date();
    const utc = now.getTime() + now.getTimezoneOffset() * 60000;
    return new Date(utc + 8 * 3600000); // UTC+8
  }

  let liveTimeInterval = null;
  let isFrozen = false;

  const timeInInput = document.getElementById("time_in");
  const timeOutInput = document.getElementById("time_out");
  const punchBtn = document.getElementById("timeBtn");
  const username = document.getElementById("username").textContent.trim();

  function formatTime(input) {
    if (!input) return "--:--:--";
    const d = new Date(input);
    if (!isNaN(d)) return `${String(d.getHours()).padStart(2,"0")}:${String(d.getMinutes()).padStart(2,"0")}:${String(d.getSeconds()).padStart(2,"0")}`;
    if (/^\d{2}:\d{2}:\d{2}$/.test(input)) return input; // fallback
    return "--:--:--";
  }

  function updateTimeInputs() {
    const phTime = getPhilippineTime();
    const hh = String(phTime.getHours()).padStart(2, "0");
    const mm = String(phTime.getMinutes()).padStart(2, "0");
    const ss = String(phTime.getSeconds()).padStart(2, "0");

    if (!timeOutInput.disabled) timeOutInput.value = `${hh}:${mm}:${ss}`;
    if (!isFrozen && !timeInInput.disabled) timeInInput.value = `${hh}:${mm}:${ss}`;
  }

  updateTimeInputs();
  liveTimeInterval = setInterval(updateTimeInputs, 1000);

  // ===== Check existing log =====
  async function checkUserLogStatus() {
    try {
      const res = await fetch("../../process/check_log.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username })
      });
      const data = await res.json();

      if (data.time_in) {
        timeInInput.value = formatTime(data.time_in);
        isFrozen = true;
      }
      if (data.time_out) {
        timeOutInput.value = formatTime(data.time_out);
        timeInInput.disabled = true;
        timeOutInput.disabled = true;
      }

      if (data.status === "timed_in") {
        punchBtn.classList.replace("btn-success","btn-danger");
        punchBtn.innerHTML = `<i class="fas fa-sign-out-alt"></i> Time Out`;
      } else if (data.status === "timed_out") {
        punchBtn.className = "btn btn-secondary";
        punchBtn.disabled = true;
        punchBtn.innerHTML = `<i class="fas fa-check-circle"></i> Already Logged`;
        clearInterval(liveTimeInterval);
      }
    } catch(err) {
      console.error("Check log failed:", err);
    }
  }

  checkUserLogStatus();

  // ===== Punch Button =====
  punchBtn.addEventListener("click", async () => {
    const now = getPhilippineTime();
    const date_time = now.toISOString().slice(0,19); // YYYY-MM-DDTHH:MM:SS
    const action = punchBtn.classList.contains("btn-success") ? "time_in" : "time_out";

    try {
      const res = await fetch("../../process/save_log.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, date_time, action })
      });
      const data = await res.json();

      if (data.status === "inserted" || data.status === "updated") {
        if (action === "time_in") {
          isFrozen = true;
          timeInInput.value = formatTime(date_time);
          punchBtn.classList.replace("btn-success","btn-danger");
          punchBtn.innerHTML = `<i class="fas fa-sign-out-alt"></i> Time Out`;
        } else {
          timeOutInput.value = formatTime(date_time);
          timeInInput.disabled = true;
          timeOutInput.disabled = true;
          punchBtn.className = "btn btn-secondary";
          punchBtn.disabled = true;
          punchBtn.innerHTML = `<i class="fas fa-check-circle"></i> Already Logged`;
          clearInterval(liveTimeInterval);
        }
      }
    } catch(err) {
      console.error("Save log failed:", err);
    }
  });

  // ===== Calendar =====
  const calendarContainer = document.getElementById("calendarContainer");
  const monthGrid = document.getElementById("monthGrid");
  const yearDisplay = document.getElementById("yearDisplay");
  const yearUp = document.getElementById("yearUp");
  const yearDown = document.getElementById("yearDown");
  let currentYear = new Date().getFullYear();
  yearDisplay.value = currentYear;

  function updateYearControls() {
    yearUp.disabled = currentYear >= 2050;
    yearDown.disabled = currentYear <= 2025;
  }

  yearUp.addEventListener("click", () => { currentYear++; yearDisplay.value = currentYear; updateYearControls(); });
  yearDown.addEventListener("click", () => { currentYear--; yearDisplay.value = currentYear; updateYearControls(); });
  updateYearControls();

  const months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
  let selectedMonth = null;

  months.forEach((m,i)=>{
    const btn = document.createElement("button");
    btn.className = "btn btn-outline-dark col-3 mb-2";
    btn.textContent = m.substring(0,3);
    btn.dataset.value = i+1;
    btn.addEventListener("click", ()=> {
      monthGrid.querySelectorAll("button").forEach(b=>b.classList.remove("btn-dark"));
      btn.classList.add("btn-dark");
      selectedMonth = i+1;
    });
    monthGrid.appendChild(btn);
  });

  document.getElementById("applyMonthYear").addEventListener("click", ()=>{
    if(!selectedMonth) return alert("Please select a month.");
    const chosenMonth = `${currentYear}-${String(selectedMonth).padStart(2,"0")}`;
    $("#monthYearModal").modal("hide");
    loadCalendarFor(chosenMonth);
  });

 function renderCalendar(data, selectedMonthStr) {
  const statusMap = {};
  data.forEach(row => statusMap[row.date] = row.status || "Pending");
  const [year, month] = selectedMonthStr.split("-").map(Number);
  const firstDay = new Date(year, month-1,1);
  const lastDay = new Date(year, month,0);
  const totalDays = lastDay.getDate();
  const startDay = firstDay.getDay();
  const monthName = firstDay.toLocaleString("default",{month:"long"});

  let html = `<table class="table table-bordered text-center">
    <thead>
      <tr>
        <th colspan="7" class="bg-dark text-white calendar-header">
          <button id="prevMonth" class="btn btn-sm btn-dark me-2">
            <i class="fas fa-chevron-left"></i>
          </button>
          <span id="monthYearLabel" style="cursor:pointer;">${monthName} ${year}</span>
          <button id="nextMonth" class="btn btn-sm btn-dark ms-2">
            <i class="fas fa-chevron-right"></i>
          </button>
        </th>
      </tr>
      <tr class="bg-secondary text-white"><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>
    </thead>
    <tbody>`;

  let day=1;
  for(let w=0;w<6&&day<=totalDays;w++){
    html+="<tr>";
    for(let d=0;d<7;d++){
      if((w===0 && d<startDay) || day>totalDays){
        html+=`<td class="bg-light"></td>`;
      } else {
        const dateStr = `${year}-${String(month).padStart(2,"0")}-${String(day).padStart(2,"0")}`;
        const status=statusMap[dateStr]||"Pending";
        const cssClass={
          "PRESENT":"status-present","NO TIME OUT":"status-no-timeout",
          "VL":"status-vl","SL":"status-sl","LWOP":"status-lwop",
          "RDOT":"status-rdot","RD":"status-rd","NO WORK":"status-no-work",
          "HOLIDAY":"status-holiday","PENDING":"status-pending"
        }[status.trim().toUpperCase()]||"status-pending";

        html+=`<td class="${cssClass}" style="height:80px;vertical-align:top;">
                <div><strong>${day}</strong></div>
                <div style="font-size:13px;">${status}</div>
              </td>`;
        day++;
      }
    }
    html+="</tr>";
  }
  html+="</tbody></table>";
   calendarContainer.innerHTML = html;

  // ===== Attach prev/next/month listeners =====
  const prevBtn = document.getElementById("prevMonth");
  const nextBtn = document.getElementById("nextMonth");
  const monthLabel = document.getElementById("monthYearLabel");

  prevBtn.addEventListener("click", () => {
    let [y, m] = selectedMonthStr.split("-").map(Number);
    m--;
    if (m < 1) { m = 12; y--; }
    selectedMonthStr = `${y}-${String(m).padStart(2, "0")}`;
    loadCalendarFor(selectedMonthStr);
  });

  nextBtn.addEventListener("click", () => {
    let [y, m] = selectedMonthStr.split("-").map(Number);
    m++;
    if (m > 12) { m = 1; y++; }
    selectedMonthStr = `${y}-${String(m).padStart(2, "0")}`;
    loadCalendarFor(selectedMonthStr);
  });

  monthLabel.addEventListener("click", () => {
    $("#monthYearModal").modal("show");
  });
}

  
  async function loadCalendarFor(monthYearStr){
    try{
      const res=await fetch("../../process/fetch_calendar_data.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({username, month:monthYearStr})
      });
      const data=await res.json();
      if(!Array.isArray(data)) throw new Error("Invalid data");
      renderCalendar(data, monthYearStr);
    }catch(err){
      console.error("Failed to load calendar:",err);
      alert("⚠️ Failed to load calendar data.");
    }
  }

  // auto load current month
  (async ()=>{
    const now=getPhilippineTime();
    const curMonthStr = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,"0")}`;
    await loadCalendarFor(curMonthStr);
  })();

calendarContainer.addEventListener("click", (e) => {
  // Previous month
  if (e.target.id === "prevMonth" || e.target.closest("#prevMonth")) {
    let [year, month] = selectedMonthStr.split("-").map(Number);
    month--;
    if (month < 1) {
      month = 12;
      year--;
    }
    selectedMonthStr = `${year}-${String(month).padStart(2, "0")}`;
    loadCalendarFor(selectedMonthStr);
  }

  // Next month
  if (e.target.id === "nextMonth" || e.target.closest("#nextMonth")) {
    let [year, month] = selectedMonthStr.split("-").map(Number);
    month++;
    if (month > 12) {
      month = 1;
      year++;
    }
    selectedMonthStr = `${year}-${String(month).padStart(2, "0")}`;
    loadCalendarFor(selectedMonthStr);
  }

  // Month-Year label opens modal
  if (e.target.id === "monthYearLabel" || e.target.closest("#monthYearLabel")) {
    $("#monthYearModal").modal("show");
  }
});


  // ===== Remarks Modal =====
  const dateRemarksModalEl = document.getElementById("dateRemarksModal");
  const dateRemarksModal = new bootstrap.Modal(dateRemarksModalEl,{backdrop:'static',keyboard:true});

  // ===== Remarks/Status Modal =====
  calendarContainer.addEventListener("click", async e => {
    const td = e.target.closest("td");
    if (!td || !td.querySelector("strong")) return;

    const day = parseInt(td.querySelector("strong").textContent, 10);
    const [monthName, year] = document.querySelector(".calendar-header").textContent.trim().split(" ");
    const monthIndex = new Date(`${monthName} 1, ${year}`).getMonth() + 1;
    const selectedDate = `${year}-${String(monthIndex).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

    const modalDate = document.getElementById("modalDate");
    const modalRemarks = document.getElementById("modalRemarks");
    const modalTimeIn = document.getElementById("modalTimeIn");
    const modalTimeOut = document.getElementById("modalTimeOut");
    const modalStatus = document.getElementById("modalStatus");

    modalDate.value = selectedDate;
    const jsDate = new Date(selectedDate);
    const isSunday = jsDate.getDay() === 0;
    modalRemarks.readOnly = isSunday;
    modalRemarks.value = isSunday ? "RD" : "";

    try {
      const res = await fetch("../../process/get_remarks.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, date: selectedDate })
      });
      const data = await res.json();

      modalRemarks.value = isSunday ? "RD" : (data.remarks || "");
      modalTimeIn.value = data.time_in || "";
      modalTimeOut.value = data.time_out || "";
      modalStatus.value = data.status_value || (isSunday ? "RD" : "");
    } catch(err) {
      console.error("Failed to load status/remarks:", err);
    }

    dateRemarksModal.show();
  });

  // ===== Save button =====
  document.addEventListener("DOMContentLoaded", () => {
  const updateBtn = document.getElementById("updateRemarksBtn");
  if(updateBtn){
    updateBtn.addEventListener("click", async () => {
      const date = document.getElementById("modalDate").value;
      const remarks = document.getElementById("modalRemarks").value.trim();
      const status = document.getElementById("modalStatus").value;

      if (!status) return alert("Please select a status before saving.");

      try {
        const res = await fetch("../../process/save_remarks.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username, date, remarks, status })
        });
        const data = await res.json();

        if (data.status === "updated" || data.status === "inserted") {
          alert("✅ Saved successfully!");
          dateRemarksModal.hide();
          const monthToLoad = selectedMonth
            ? `${currentYear}-${String(selectedMonth).padStart(2, "0")}`
            : `${getPhilippineTime().getFullYear()}-${String(getPhilippineTime().getMonth() + 1).padStart(2, "0")}`;
          loadCalendarFor(monthToLoad);
        } else {
          alert("⚠️ Failed to save data.");
        }
      } catch (err) {
        console.error("Error saving data:", err);
        alert("⚠️ Error occurred while saving.");
      }
    });
  }
});

</script>


<?php include 'plugins/footer.php'; ?>