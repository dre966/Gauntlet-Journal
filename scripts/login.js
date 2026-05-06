const login = document.querySelector("#Login");
const register = document.querySelector("#Register");
const form = document.querySelector("form")
let toastTimer;

function showinfo(message, type = "info") {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.remove("show");
    }, 2800);
}

async function get_user(action) {
    const uname = document.querySelector("#uname").value
    const pass = document.querySelector("#pass").value
    if(uname.trim() == ""|pass.trim() == ""){
        showinfo("Please make sure username or password field is not empty", "error")
        return;
    }
    const formData = new FormData();
    formData.append('uname',uname)
    formData.append('pass',pass)
    formData.append('action', action)
    const res = await fetch("insert.php", {method:"POST",body:formData})
    const r = await res.json()
    return r
}

function redir(id, uname){
    let time = 300;
    setTimeout(()=>{
        window.location.href = `index.php`
    }, time)
}

login.addEventListener("click", async (e)=>{
    ret = await get_user("login")
    showinfo(ret["msg"], ret["status"])
    if(ret["status"] == "success"){
        redir(ret["id"], ret["uname"])

    }
})

register.addEventListener("click", async (e)=>{
    ret = await get_user("register")
    showinfo(ret["msg"], ret["status"])
})

window.addEventListener("DOMContentLoaded", ()=>{
    showinfo("Welcome", "info")
})

