const searchbar = document.querySelector(".search")
let typingtimer;
const donetypingtimer = 300;
var selected = []

async function loadcars(query ="") {
    try{
        const res = await fetch(`fetch_cars.php?search=${query}`);
        const cars = await res.json();
        const carList = document.querySelector(".car-list")
        carList.innerHTML  = "";
        if (cars.length > 0){
            cars.forEach(car => {
        const carBox = document.createElement("div");
        carBox.className = "car-card";
        carBox.style.backgroundImage = `url('/web_prog/assets/car_imgs/${car.id}.jpg')`;
        carBox.innerHTML = `<div class="car-name">
                        <h2 id="brand">${car.brand}</h2>
                        <h2 id="model">${car.model}</h2>
                        </div>`
            
        carList.appendChild(carBox)
        carBox.addEventListener("click",()=>{
            if (selected.includes(car.id)){
                selected = selected.filter(id => id !== car.id)
                carBox.classList.remove("selected")
            }else if (selected.length < 5){
                selected.push(car.id)
                carBox.classList.add("selected")
            }});
        });
        }else{
            console.log("awww")
        }
        
    } catch(err){
        console.error("Error Populating DOM:", err)
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
window.addEventListener("DOMContentLoaded", (e)=>{
    loadcars()
})