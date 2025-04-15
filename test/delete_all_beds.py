# delete_all_beds.py
import requests
import config

API_URL = f"{config.BASE_URL}/task_api.php?action={config.DELETE_ALL_BEDS}"

# Construct the JSON payload
payload = {
    "username": config.USER,
    "password": config.PASSWORD
}

print(f"Sending DELETE request to: {API_URL}")

try:
    response = requests.post(API_URL, json=payload)
    content_type = response.headers.get("Content-Type", "")

    if "application/json" in content_type:
        res_json = response.json()
    else:
        raise ValueError(f"Invalid response format:\n{response.text}")

    if response.status_code == 200 and "error" not in res_json:
        print(f"Success → {res_json}")
    else:
        print(f"Failed  → {res_json}")

except Exception as e:
    print(f"Error   → {e}")
