import requests
import config

API_URL = f"{config.BASE_URL}/task_api.php?action={config.DELETE_ALL_BEDS}"

payload = {
    "username": config.USER,
    "password": config.PASSWORD
}

try:
    response = requests.post(API_URL, json=payload)
    res_json = response.json()
    print("Response:", res_json)
except Exception as e:
    print("Error:", e)
