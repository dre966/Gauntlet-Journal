const searchbar = document.querySelector(".search")
const donetypingtimer = 300;
const next = document.querySelector("#btn-next")
const view = document.querySelector(".view_changer")
var selected = []
let typingtimer;
let toastTimer;
let oldlist;


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
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.remove("show");
    }, 2800);
}

function updateControls() {
    document.getElementById("sel-count").textContent = selected.length;
    const btn = document.getElementById("btn-next");
    btn.style.display = selected.length > 0 ? "inline-block" : "none";
}

function select(carid){
    console.log("selected ", carid)
    carBox = document.getElementById(carid)
 if (selected.includes(carid)) {
                        selected = selected.filter(id => id !== carid);
                        carBox.classList.remove("selected");
                    } else {
                        selected.push(carid);
                        carBox.classList.add("selected");
                    } 
}

function build_car_cards(carlist){
    const carList = document.querySelector(".car-list")
    carList.innerHTML  = "";
    carlist.forEach(car => {
                const carBox = document.createElement("div");
                const imgDiv = document.createElement("div");
                imgDiv.style.backgroundImage=`url(/web_prog/assets/car_imgs/${car.id}.jpg)`
                imgDiv.id = "img"
                const carName = document.createElement("div");
                const brand = document.createElement("h2")
                const model = document.createElement("h2")
                carName.className = "car-name"
                brand.id = "brand"
                model.id = "model"
                brand.textContent = car.brand;
                model.textContent = car.model
                carName.appendChild(brand)
                carName.appendChild(model)
                carBox.className = "car-card";
                carBox.id = car.id
                carBox.appendChild(imgDiv)
                carBox.appendChild(carName)
                

                // Restore selected state across re-renders
                if (selected.includes(car.id)) carBox.classList.add("selected");

                carList.appendChild(carBox)
                carBox.addEventListener("click", () => {
                    select(car.id)
                   
                    updateControls();
                });
            });
}

async function loadcars(query ="") {
    try{
        var res = await fetch(`config.php`,{method:"POST",body:JSON.stringify(
                {
                    "to_do":"load_cars",
                    "params":query
                }
            )});       
        const res_json = await res.json();
        const cars = res_json.body
       
        if (cars.length > 0){
           build_car_cards(cars)
        } else {
            showToast("No cars found.", "info");
        }
    } catch(err) {
        console.error("Error Populating DOM:", err);
        showToast("Failed to load cars. Please try again.", "error");
    }
}


async function get_cars(){
    const res = await fetch(`config.php`,{method:"POST",body:JSON.stringify({
        'to_do':"get_cars",
        "params":null
    })})
    const res_json = await res.json();
    const status = res_json["status"];
    const msg = res_json["msg"]
    const data = res_json["body"]
    console.log("heyyy")
    data.forEach(d=>{
       select(d.id)
       updateControls();
    })
    showToast(msg, status)
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

searchbar.addEventListener("input",(e)=>{
    clearTimeout(typingtimer);
    typingtimer = setTimeout(() => {
        const term = e.target.value
        console.log(term)
        loadcars(term)
    }, donetypingtimer);
})

view.addEventListener("click",(e)=>{
    const carList = document.querySelectorAll(".car-card")
    const resList = []
    if(view.classList.contains("clicked")){
    
        view.classList.remove("clicked")
    }else{
        view.classList.add("clicked")

    }
    console.log(resList)
})

next.addEventListener("click", async(e)=>{
    const result = await fetch(`config.php`, {method:"POST",body:JSON.stringify({
        "to_do":"save",
        "params":selected.join(",")
    })})
    const res = await result.json()
    const msg = res["msg"]
    const stat = res["status"]
    showToast(msg,stat)
    setTimeout(()=>{
        window.location.href = `tables.php`
    }, 300)
})

window.addEventListener("DOMContentLoaded", async (e)=>{
    check_user();
    const res = await connect("config.php", "user",null)
    const [stat, msg, body] = res
    document.querySelector("#acc").textContent = body
    showToast(msg, stat)
    loadcars();
    get_cars();
})
