function updateDateTime(){
  const optionsDate={ year:"numeric", month:"long", day:"numeric", timeZone:"Asia/Manila" };
  const optionsTime={ hour:"2-digit", minute:"2-digit", second:"2-digit", hour12:true, timeZone:"Asia/Manila" };
  document.getElementById("dateToday").innerText = new Date().toLocaleDateString("en-US", optionsDate);
  document.getElementById("timeNow").innerText = new Date().toLocaleTimeString("en-US", optionsTime);
}
setInterval(updateDateTime,1000);
updateDateTime();
