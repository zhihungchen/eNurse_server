
# Insert_all_beds.py
import json
import requests
from config import config 

API_URL = f"{config.BASE_URL}/task_api.php?action={config.CREATE_BED}"

# Load all bed data from the JSON file
with open("data/beds.json", "r", encoding="utf-8") as f:
    beds = json.load(f)["beds"]

success_count = 0
fail_count = 0

# Iterate through each bed and send it to the PHP API
for bed in beds:
    print(API_URL)
    try:
        payload = {
            "username": config.USER,
            "password": config.PASSWORD,
            **bed  # Merge bed data into the request payload
        }

        response = requests.post(API_URL, json=payload)
        res_json = response.json()

        # Check if the request was successful and the response has no error
        if response.status_code == 200 and "error" not in res_json:
            success_count += 1
            print(f"Success | {bed['floor']} - {bed['bed_name']} → {res_json}")
        else:
            fail_count += 1
            print(f"Failed  | {bed['floor']} - {bed['bed_name']} → {res_json}")

    except Exception as e:
        fail_count += 1
        print(f"Error   | {bed['floor']} - {bed['bed_name']} → {e}")

# Print a summary of the operation
print(f"\nSummary: {success_count} succeeded / {fail_count} failed")
