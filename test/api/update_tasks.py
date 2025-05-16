# update_tasks.py
import json
import requests
from config import config 
from datetime import datetime

API_URL = f"{config.BASE_URL}/task_api.php?action={config.UPDATE_TASK}"


# Load all bed data from the JSON file
with open("data/beds.json", "r", encoding="utf-8") as f:
    beds = json.load(f)["beds"]

success_count = 0
fail_count = 0

# TODO: Update the tasks generator as randomly
def generate_tasks(bed):
    now = datetime.now().isoformat()
    return [
        {
            "type": "video_01",
            "status": "pending",
            "created_at": now,
            "start_time": None,
            "finish_time": None
        },
        {
            "type": "video_02",
            "status": "pending",
            "created_at": now,
            "start_time": None,
            "finish_time": None
        }
    ]

# Iterate through each bed and send update request
for bed in beds:
    print(API_URL)
    try:
        tasks = generate_tasks(bed)

        payload = {
            "username": config.USER,
            "password": config.PASSWORD,
            "bed_name": bed["bed_name"],
            "room_number": bed["room_number"],
            "floor": bed["floor"],
            "tasks": tasks
        }

        response = requests.post(API_URL, json=payload)
        res_json = response.json()

        if response.status_code == 200 and "error" not in res_json:
            success_count += 1
            print(f"Success | {bed['floor']} - {bed['bed_name']} → {res_json}")
        else:
            fail_count += 1
            print(f"Failed  | {bed['floor']} - {bed['bed_name']} → {res_json}")

    except Exception as e:
        fail_count += 1
        print(f"Error   | {bed['floor']} - {bed['bed_name']} → {e}")

# Print a summary
print(f"\nSummary: {success_count} succeeded / {fail_count} failed")
