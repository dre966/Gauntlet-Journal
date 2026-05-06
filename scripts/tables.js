const car_drop = document.querySelector("#car_drop");
const loc_drop = document.querySelector("#loc_drop");
const track_drop = document.querySelector("#track_drop");
const add_btn = document.querySelector("#add");
const reset_btn = document.querySelector("#reset");
const input = document.querySelector("#input");
const view = document.getElementById("view_drop");

const toastQueue = [];
let toastRunning = false;

async function connect(dest,td,params){
    const response = await fetch(dest,{method:td,body:JSON.stringify({
        "to_do":td,
        "params":params
    })});
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
        
        // split track nicely
        const trackParts = car.Track.split(" ");
        const top = document.createElement("div")
        top.className = "top"
        const bottom = document.createElement("div")
        bottom.className = "bottom"
        const top_left = document.createElement("span")
        top_left.className = "top-left-label"
        if(trackParts > 2 ){top_left.innerHTML = `${trackParts[0] + " " + trackParts[1]}<br>${trackParts.slice(2).join(" ")}`}else{top_left.innerHTML = `${trackParts[0]}<br>${trackParts.slice(1).join(" ")}`}
        const logo = document.createElement("img")
        const brand = document.createElement("h2")
        brand.id = "brand"
        brand.textContent = car.Brand;
        const model = document.createElement("h2")
        model.textContent = car.Vehicle;
        model.id = "model"
        const carName = document.createElement("h2")
        const front = document.createElement("div")
        front.id = "front"
        const back = document.createElement("div")
        back.id = "back"
        back.style.backgroundImage =`url('/web_prog/assets/car_imgs/${car["Vehicle ID"]}.jpg'`;
        carName.className ="car-name"
        const timestamp = document.createElement("div")
        timestamp.className = "time-stamp"
        timestamp.textContent = `${parseFloat(car.Time).toFixed(3)}s`
        logo.src = `./assets/loc_logos/${car.City}.png`
        logo.alt = "logo"

        top.appendChild(top_left)
        top.appendChild(logo)
        carName.appendChild(brand)
        carName.appendChild(model)
        bottom.appendChild(carName)
        bottom.appendChild(timestamp)
        front.appendChild(top)
        front.appendChild(bottom)
        card.appendChild(front)
        card.appendChild(back)
        
        // card.innerHTML = `
        // <div class="top">
        //     <span class="top-left-label">
        //         ${trackParts[0]}<br>${trackParts.slice(1).join(" ")}
        //     </span>
        //     <img src="./assets/loc_logos/${car.City}.png" alt="logo">
        // </div>

        // <div class="bottom">
        //     <div class="car-name">
        //         <h2 id="brand">${car.Brand}</h2>
        //         <h2 id="model">${car.Vehicle}</h2>
        //     </div>
        //     <div class="time-stamp">${parseFloat(car.Time).toFixed(3)}s</div>
        // </div>
        // `

        container.appendChild(card);
    });
}

async function check_user() {
    const res_arr = await connect("config.php","check_user",null)
    const [status, msg, body] = res_arr
    if(status == "error"){
        showToast(msg, status)
        setTimeout(()=>{
            window.location.href = "login.php"
        },3000)
    }else{
        showToast(msg,status)
    }

}

async function fillloc(){
    const res = await connect("config.php","get_locs",null)
    const [status, msg, data] = res
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
    const res = await connect(`config.php`,"get_tracks",loc)
    const [status,msg,data] = res
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
    const res = await connect(`config.php`,"get_cars",null)
    const [status,msg,data] = res
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
    const res = await connect(`config.php`,"add_time",`${track_id},${car_id},${lap_time}`);
    const [status,msg,body] = res
    update_table()
    showToast(msg, status)
}

async function update_table() {
    const res = await connect(`config.php`,"get_times",null);
    const [status, msg,body] = res;
    const view = document.getElementById("view_drop").value;

    if (view === "table") {
        document.getElementById("table").style.display = "block";
        document.getElementById("card_view").style.display = "none";
        buildPivotTable(body);
    } else {
        document.getElementById("table").style.display = "none";
        document.getElementById("card_view").style.display = "flex";
        buildCardView(body);
    }

}

async function reset(){
    const res = await connect(`config.php`,"reset",null)
    const [status,msg,body] = res
    showToast(msg, status)
    update_table()
}


add_btn.addEventListener("click",(e)=>{
    if(input.value.split(".").length < 2){
        input.value = `${input.value}.000`
    }
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
    update_table();
})