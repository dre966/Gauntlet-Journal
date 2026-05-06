const car_drop = document.querySelector("#car_drop");
const loc_drop = document.querySelector("#loc_drop");
const track_drop = document.querySelector("#track_drop");
const add_btn = document.querySelector("#add");
const reset_btn = document.querySelector("#reset");
const input = document.querySelector("#input");
const view = document.getElementById("view_drop");

const toastQueue = [];
let toastRunning = false;

async function connect(dest){
    const response = await fetch(dest);
    const res = await response.json();
    const status = res["status"]
    const msg = res["msg"]
    const data = res["body"]
    return [status, msg, data]
}

function showToast(message, type = "info") {
    toastQueue.push({ message, type });
    if (!toastRunning) runToastQueue();
}

function runToastQueue() {
    if (toastQueue.length === 0) {
        toastRunning = false;
        return;
    }

    toastRunning = true;
    const { message, type } = toastQueue.shift();

    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = `toast ${type} show`;

    setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(runToastQueue, 20); // small gap between toasts
    }, 2800);
}
function buildPivotTable(records) {
    const cols = [...new Set(records.map(r => r.Vehicle))];
    const rows = [...new Set(records.map(r => `${r.City} - ${r.Track}`))];
    
    const lookup = {};
    records.forEach(r => {
        const key = `${r.City} - ${r.Track}|${r.Vehicle}`;
        const val = parseFloat(r.Time);
        if (!lookup[key] || val < lookup[key]) {
            lookup[key] = val;
        }
    });

    const table = document.getElementById('table');
    table.innerHTML = '';

    // Corner + car headers
    const headRow = document.createElement('tr');
    headRow.id = 'head';

    const corner = document.createElement('td');
    corner.id = 'start';
    corner.innerHTML = 'Car →<br><hr>Location ↓';
    headRow.appendChild(corner);

    cols.forEach(col => {
        const th = document.createElement('th');
        th.textContent = col;
        headRow.appendChild(th);
    });
    table.appendChild(headRow);

    // One row per city-track
    rows.forEach(row => {
        const tr = document.createElement('tr');

        // grab city name (before the " - ") and take first 3 chars to match your CSS classes
        const city = row.split(' - ')[0];
        
        const cityClass = city.substring(0, 3);
        tr.classList.add(cityClass);

        const rowCell = document.createElement('td');
        rowCell.textContent = row;
        tr.appendChild(rowCell);

        cols.forEach(col => {
            const td = document.createElement('td');
            const key = `${row}|${col}`;
            td.textContent = lookup[key] !== undefined ? lookup[key].toFixed(3) : '-';
            tr.appendChild(td);
        });

        table.appendChild(tr);
    });
}

function buildCardView(records) {
    const container = document.getElementById("card_view");
    container.innerHTML = "";

    records.forEach(car => {
        const card = document.createElement("div");
        card.className = "car-card";

        // background image
        card.style.backgroundImage = `url('/web_prog/assets/car_imgs/${car["Vehicle ID"]}.jpg')`;

        // split track nicely
        const trackParts = car.Track.split(" ");

        card.innerHTML = `
        <div class="top">
            <span class="top-left-label">
                ${trackParts[0]}<br>${trackParts.slice(1).join(" ")}
            </span>
            <img src="./assets/loc_logos/${car.City}.png" alt="logo">
        </div>

        <div class="bottom">
            <div class="car-name">
                <h2>${car.Brand}</h2>
                <h2 id="model">${car.Vehicle}</h2>
            </div>
            <div class="time-stamp">${parseFloat(car.Time).toFixed(3)}s</div>
        </div>
        `;

        container.appendChild(card);
    });
}

async function check_user() {
    const res_arr = await connect("config.php?funct=check_user")
    const status = res_arr[0]
    const msg = res_arr[1]
    const body = res_arr[2]
    if(status == "error"){
        showToast(msg, status)
        setTimeout(()=>{
            window.location.href = "login.php"
        },6000)
    }else{
        showToast(msg,status)
    }

}

async function fillloc(){
    const response = await fetch("config.php?funct=get_locs");
    const res = await response.json();
    const status = res["status"]
    const msg = res["msg"]
    const data = res["body"]
    if(status =="success"){
        loc_drop.innerHTML = ""
        data.forEach(loc => {
            const option = document.createElement("option");
            option.value = loc.id;
            option.textContent = loc.name;
            loc_drop.appendChild(option);
        });
        filltracks(loc_drop.value)
    }
    showToast(msg, status)
}

async function filltracks(loc){
    const response = await fetch(`config.php?funct=get_tracks&args=${loc}`);
    const res = await response.json();
    const status = res["status"]
    const msg = res["msg"]
    const data = res["body"]
    if(status =="success"){
        track_drop.innerHTML = ""
        data.forEach(loc => {
            const option = document.createElement("option");
            option.value = loc.id;
            option.textContent = loc.name;
            track_drop.appendChild(option);
        });
    }
    showToast(msg, status)
}
async function get_cars() {
    const response = await fetch(`config.php?funct=get_cars`);
    const res = await response.json();
    const status = res["status"]
    const msg = res["msg"]
    const data = res["body"]
    if(status =="success"){
        data.forEach(car => {
        const option = document.createElement("option");
        option.value = car.id;
        option.textContent = car.brand + " " + car.model;
        car_drop.appendChild(option);
        });
    }
    update_table()
    showToast(msg, status)
}

async function add_data(track_id, car_id, lap_time) {
    const response = await fetch(`config.php?funct=add_time&args=${track_id},${car_id},${lap_time}`);
    const res = await response.json();
    const status = res["status"]
    const msg = res["msg"]
    const data = res["body"]
    update_table()
    showToast(msg, status)
}

async function update_table() {
    const response = await fetch(`config.php?funct=get_times`);
    const res = await response.json();
    const data = res["body"];
    console.log(data)
    const view = document.getElementById("view_drop").value;

    if (view === "table") {
        document.getElementById("table").style.display = "block";
        document.getElementById("card_view").style.display = "none";
        buildPivotTable(data);
    } else {
        document.getElementById("table").style.display = "none";
        document.getElementById("card_view").style.display = "flex";
        buildCardView(data);
    }

}

async function reset(){
    const res = await fetch(`config.php?funct=reset`)
    const res_json = await res.json()
    const status = res_json["status"]
    const msg = res_json["msg"]
    const data = res_json["body"]
    showToast(msg, status)
    update_table()
}


add_btn.addEventListener("click",(e)=>{
    add_data(track_drop.value,car_drop.value,input.value)
})

loc_drop.addEventListener("change", (e)=>{
    filltracks(e.target.value)
})

view.addEventListener("change", (e) => {
    update_table();
    console.log(e.target.value)
});

reset_btn.addEventListener("click",()=>{
    reset()
})

window.addEventListener("DOMContentLoaded", (e)=>{
    check_user();
    fillloc();
    get_cars();
    update_table()
    showToast("Welcome", "info")

})