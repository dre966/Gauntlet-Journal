Building out a project like **Gauntlet Journal** is a great way to bridge the gap between "coding for fun" and "coding for production." 

Based on your current files, here is a checklist of tasks organized by priority. I recommend tackling them in this order to ensure your foundation is solid before adding the "shiny" features.

---

### Phase 1: The "Must-Have" Security
Right now, your login system is vulnerable to basic attacks. You should fix these before publishing anywhere.

 - [x] **Implement Password Hashing:** * Update `insert.php` to use `password_hash($pass, PASSWORD_DEFAULT)` during registration.
    * Update `insert.php` (login section) to use `password_verify($pass, $row['pass'])`.
* [ ] **Switch to PHP Sessions:** * In your login logic, use `$_SESSION['user_id'] = $uid;` instead of passing the ID in the URL (`?u=...`).
    * Add `session_start();` at the top of every PHP file to verify the user is actually logged in.
* [ ] **Create a `.env` or Secure Config:** * Move your database host, user, and password out of `config.php` and into a separate file that is excluded from your version control (like `.gitignore`).

---

### Phase 2: Structural Refinement
Your code currently repeats the database connection logic many times. This makes it hard to maintain.

* [ ] **Centralize DB Connections:** * Refactor `config.php` to create one single `$conn` object that is shared, rather than calling `connect()` inside every function.
* [ ] **Input Sanitization:** * Even with prepared statements, use `filter_var()` or `htmlspecialchars()` on the data you display back to the user in `tables.php` to prevent Cross-Site Scripting (XSS).
* [ ] **Error Handling:** * Instead of `die()`, create a standard JSON error response format so your JavaScript can catch errors and show a "Toast" message instead of the app just stopping.

---

### Phase 3: UI/UX & Quality of Life
Make the app feel more professional and "snappy."

* [ ] **Front-end Validation:** * In your JS, check if the "Time" input matches a pattern (e.g., `00.000`) before sending the AJAX request.
* [ ] **"Live" Updates:** * When a user adds a time, use JavaScript to insert a new row or update the cell in the table immediately without requiring a page refresh.
* [ ] **Mobile Responsiveness:** * Update `tables.css` to use `rem` or `%` instead of fixed `px` values for positioning so the table looks good on tablets and phones.

---

### Phase 4: Advanced Features
Once the basics are bulletproof, add these to make the project stand out.

* [ ] **Global Leaderboard:** * Create a new page/query that shows the top 10 fastest times across *all* users for a specific track.
* [ ] **Personal Stats Dashboard:** * Add a section showing "Most Used Car," "Fastest Lap Ever," and "Total Laps Logged."
* [ ] **Data Export:** * Add a button to export the current table view to a `.csv` file so users can save their times in Excel.

---

### Phase 5: Deployment Prep
* [ ] **Database Migration Script:** * Keep a clean version of your `export.sql` (without your personal test data) so you can easily set it up on a live server like Heroku, DigitalOcean, or HostGator.
* [ ] **SSL/HTTPS:** * Ensure that once you publish, you use an SSL certificate (standard practice now) to protect those login credentials.

**Which one of these would you like to start with? I can provide a code snippet for the Password Hashing or the Session Management first if you're ready.**

### EXTRA - Add card view to table page
```html
`url('/web_prog/assets/car_imgs/${car.id}.jpg')`;
                carBox.innerHTML = `<div class="top">
        <span class="top-left-label">{track[0]}<br> track[1:] </span> <img src="./assets/loc_logos/{location}" alt="logo">
    </div>

    <div class="bottom">
        <div class="car-name">
            <h2 id="brand">${car.brand}</h2>
            <h2 id="model">${car.model}</h2>
        </div>
        <div class="time-stamp">{time}s</div> </div>`
        ```
    ```css /* ── Car list & cards (unchanged) ── */
.car-list{
    margin-top: 50px;
    width: 100%;
    height: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
}
.car-card{
    background-color: #868ff1;
    margin: 10px;
    border: 1.6px;
    width: 400px;
    position: relative;
    z-index: 1;
    height: 200px;
    border-radius: 20px;
    overflow: hidden;
    background-position: center;
    background-size: cover;
    border: 1px solid rgb(255, 255, 255,0.1);
    display: flex;
    
    
    background: rgb(255, 255, 255,0.1);
    background-size: 700px;
    cursor: pointer;
    flex-direction: column;
}

.car-name{
    margin: 0 0 0 0;
    padding: 30% 0px 0 10px;
    width: 100%;
    padding: 15px;
    background: rgb(255, 255, 255,0.1);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    border-top: 1px solid rgba(255, 255, 255, 0.3);
}
.car-card.selected{
    border: 2px solid rgba(0, 255, 4, 0.904);
    background: rgb(60, 255, 0);
}
.car-name h2{
    margin: 0 0 0 0;
    color: rgb(0, 0, 0);
    font-size: 35px;
    font-family: "RadjM";
}

.checks{
    color: white;
    font-family: "RadjM";
    font-size: 30px;
}
.checks label input{
    cursor: default;
    size: 20px;
    background-color: #ff5555;
}
.car-name #model{
    font-size: 25px;
}
.top {
    flex: 1; /* Takes 50% */
    display: flex;
    justify-content: space-between; /* Puts text left and image right */
    align-items: flex-start;
    padding: 15px;
    position: relative;
}

.bottom {
    flex: 1; /* Takes 50% */
    display: flex;
    align-items: flex-end; /* Keeps car-name at the bottom of this half */
    position: relative;
}

/* Specific styling for the Top Right Image */
.top img {
    position: absolute;
    top: 5%;
    right: 1%;
    width: 17%;
    height: auto;
}

/* Styling for Top Left Text */
.top-left-label {
    color: rgb(0, 0, 0);
    font-family: "RadjM";
    font-size: 20px;
    font-weight: bold;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
}

/* Styling for Bottom Right 30s Text */
.time-stamp {
    position: absolute;
    bottom: 10px;
    right: 15px;
    font-family: "RadjM";
    font-size: 350%; /* Big */
    font-style: italic; /* Italic */
    color: rgb(0, 0, 0);
    text-shadow: 2px 2px 4px rgba(0,0,0,0.9);
    z-index: 10;
}

/* Adjusting car-name to sit perfectly in the bottom half */
.car-name {
    margin: 0;
    width: 100%;
    padding: 10px 15px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    border-top: 1px solid rgba(255, 255, 255, 0.3);
}```