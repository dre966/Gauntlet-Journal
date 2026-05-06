const searchbar = document.querySelector(".search")
const donetypingtimer = 300;
const next = document.querySelector("#btn-next")
const choices = document.querySelectorAll(".checks label input")
var selected = []
let typingtimer;
let toastTimer;

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

async function loadcars(query ="", isclass = false, clas ="") {
    try{
        var res = null
        if(isclass){
            res = await fetch(`fetch_cars.php?search=${query}&class=${clas}`);
        }else{
            res = await fetch(`fetch_cars.php?search=${query}`);
            
        }       
        if (!res.ok) throw new Error(`Server error: ${res.status}`);
        const cars = await res.json();
        const carList = document.querySelector(".car-list")
        carList.innerHTML  = "";
        if (cars.length > 0){
            cars.forEach(car => {
                const carBox = document.createElement("div");
                carBox.className = "car-card";
                carBox.id = car.id
                carBox.style.backgroundImage = `url('/web_prog/assets/car_imgs/${car.id}.jpg')`;
                carBox.innerHTML = `<div class="car-name"">
                                <h2 id="brand">${car.brand}</h2>
                                <h2 id="model">${car.model}</h2>
                                </div>`

                // Restore selected state across re-renders
                if (selected.includes(car.id)) carBox.classList.add("selected");

                carList.appendChild(carBox)
                carBox.addEventListener("click", () => {
                    select(car.id)
                   
                    updateControls();
                });
            });
        } else {
            showToast("No cars found.", "info");
        }
    } catch(err) {
        console.error("Error Populating DOM:", err);
        showToast("Failed to load cars. Please try again.", "error");
    }
}

async function get_cars(){
    const res = await fetch(`config.php?funct=get_cars&args=${sessionData.uid}`)
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


searchbar.addEventListener("input",(e)=>{
    clearTimeout(typingtimer);
    typingtimer = setTimeout(() => {
        const term = e.target.value
        console.log(term)
        loadcars(term)
    }, donetypingtimer);
})

next.addEventListener("click", async(e)=>{
    const result = await fetch(`save_cars.php?cars=${selected}`, {method:"POST"})
    const res = await result.json()
    const msg = res["msg"]
    const stat = res["status"]
    showToast(msg,stat)
    setTimeout(()=>{
        window.location.href = `tables.php`
    }, 300)
})

choices.forEach(choice => {
    choice.addEventListener("click", (e)=>{
        if (choice.checked){
            loadcars(searchbar.value,true,choice.value)
        }
    })
    
});
window.addEventListener("DOMContentLoaded", (e)=>{
    loadcars();
    get_cars();
})
