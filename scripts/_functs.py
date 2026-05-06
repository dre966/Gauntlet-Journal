import requests, json, sys, types, inspect, ast
import mysql.connector

CARLISPATH = r"C:\xampp\htdocs\web_prog\db\cars.json"
LOCLISTPATH = r"C:\xampp\htdocs\web_prog\db\locs.json"

def get_carlist() -> bool:
  url = "https://asec.tools/.netlify/functions/carsList"
  headers = {
      "accept": "*/*",
      "accept-language": "en-US,en;q=0.9,es;q=0.8",
      "priority": "u=1, i",
      "sec-ch-ua": "\"Google Chrome\";v=\"147\", \"Not.A/Brand\";v=\"8\", \"Chromium\";v=\"147\"",
      "sec-ch-ua-mobile": "?0",
      "sec-ch-ua-platform": "\"Windows\"",
      "sec-fetch-dest": "empty",
      "sec-fetch-mode": "cors",
      "sec-fetch-site": "same-origin"
    }

  res = requests.get(url=url, headers=headers)
  if res.status_code == 200:
    try:
      with open(CARLISPATH, "w") as f:
          json.dump(res.json(),f, indent=2)
      return True
    except Exception as e:
      print(f"Error saving car list: {e}")
      return False
  else:
    print(f"Failed to fetch car list: {res.status_code}")
    return False

def browser(msg):
  print(f"Got {msg}")

def t():
  print("got")

def get_carimgs() -> bool:  
  for i in range(1, len(json.load(open(CARLISPATH, "r"))) + 1):
    print(f"Fetching image for car ID {i}...")
    url = f"https://img.asec.tools/{i}.webp"
    headers = {
      "accept": "image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8",
      "accept-language": "en-US,en;q=0.9,es;q=0.8",
      "priority": "u=1, i",
      "sec-ch-ua": "\"Google Chrome\";v=\"147\", \"Not.A/Brand\";v=\"8\", \"Chromium\";v=\"147\"",
      "sec-ch-ua-mobile": "?0",
      "sec-ch-ua-platform": "\"Windows\"",
      "sec-fetch-dest": "image",
      "sec-fetch-mode": "no-cors",
      "sec-fetch-site": "same-site"
    }
    res = requests.get(url=url, headers=headers)
    if res.status_code == 200:
      try:
        with open(rf"C:\xampp\htdocs\web_prog\assets\car_imgs\{i}.jpg", "wb") as f:
            f.write(res.content)
      except Exception as e:
        print(f"Error saving car image: {e}")
        return False
    else:
      print(f"Failed to fetch car image for ID {i}: {res.status_code}")
      return False
  return True

def build_db() -> None:
  db = mysql.connector.connect(host="localhost",user="root",password="", database="web_prog")
  cursor = db.cursor()
  with open(CARLISPATH, "r") as f:
    cars = json.load(f)
  query = "INSERT IGNORE INTO cars (id, brand, model, class, max_stars, max_rank) VALUES (%s, %s, %s, %s, %s, %s)"
  for car in cars:
    values = (car["id"], car["brand"], car["model"], car["car_class"], car["max_stars"], car["max_rank"])
    cursor.execute(query, values)
  db.commit()
  cursor.close()
  db.close()

def prepare_tracks(loclist_path:str):
  res = dict()
  res2 = list()
  with open(loclist_path, "r") as f:
    loclist = ast.literal_eval(f.read())
  for i in loclist:
      location = i.split(" - ")[0]
      track = i.split(" - ")[1]
      if location in res:
          res[location] = [res[location],track]
      else:
          res[location] = track
  for i, d in enumerate(res.items()):
    d2 = dict()
    d2["loc"] = d[0]
    d2["tracks"] = d[1]
    res2.append(d2)
  with open(LOCLISTPATH, "w") as f:
    json.dump(res2, f, indent=2)


def add_tracks():
  db = mysql.connector.connect(host="localhost",user="root",password="", database="web_prog")
  cursor = db.cursor()
  with open(LOCLISTPATH, "r") as f:
    locs = json.load(f)
  for item in locs:
    loc_name = item["loc"]
    track_data = item["tracks"]

    loc_query = "INSERT IGNORE INTO locations (name) VALUES(%s)"
    cursor.execute(loc_query, (loc_name,))

    id_query = "SELECT id FROM locations WHERE name = %s"
    cursor.execute(id_query,(loc_name,))
    loc_id = cursor.fetchone()[0]

    if isinstance(track_data, list):
      for track_name in track_data:
        track_query = "INSERT INTO tracks (loc, name) VALUES(%s, %s)"
        cursor.execute(track_query,(loc_id, track_name))
    else:
      track_query = "INSERT INTO tracks (loc, name) VALUES(%s, %s)"
      cursor.execute(track_query,(loc_id, track_data))
  db.commit()
  cursor.close()
  db.close()

  print("Import Complete")

def main():
  if len(sys.argv) < 2:
    print("Usage: python _funts.py <function_name><args>")
    sys.exit(1)
  func_name = sys.argv[1]
  params = sys.argv[2:]
  func = globals().get(func_name)
  if func and callable(func):
    sig = inspect.signature(func)
    req_params = [p for p in sig.parameters.values() if p.default == p.empty]
    param_info = dict()
    for req in req_params:
      param_info[req.name] = [req.annotation.__name__][0] if req.annotation is not req.empty else "any"
    if len(params) != len(req_params):
      print(f"Error: Function '{func_name}' expects {len(req_params)} arguments, but you gave {len(params)}.")
      print(f"Usage: {func_name}({",".join(list(param_info.keys()))}) or {func_name}({",".join(list(param_info.values()))})")
    else:
      func(*params)  
  else:
    print(f"Function {func_name} function not found")

if __name__ == "__main__":
  main()
