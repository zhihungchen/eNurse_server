import json

floors = {
    "5A": [
        "01-0", "02-0", "03-0", "05-0", "6-1", "6-2", "6-3", "6-5",
        "7-1", "7-2", "7-3", "7-5", "8-1", "8-2", "8-3", "8-5",
        "9-1", "9-2", "9-3", "9-5", "10-1", "10-2", "10-3", "10-5",
        "11-1", "11-2", "12-1", "12-2", "13-0", "15-0", "16-0", "17-0"
    ],
    "5B": [
        "01-0", "02-0", "03-0", "05-0", "6-1", "6-2", "6-3", "6-5",
        "7-1", "7-2", "7-3", "7-5", "8-1", "8-2", "8-3", "8-5",
        "9-1", "9-2", "9-3", "9-5", "10-0", "11-0", "12-1", "12-2",
        "13-1", "13-2", "15-0", "16-0", "17-0", "18-0"
    ]
}

bed_data = {"beds": []}
for floor, bed_list in floors.items():
    for bed_name in bed_list:
        room_number = bed_name.split("-")[0].zfill(2)
        bed_data["beds"].append({
            "bed_name": bed_name,
            "room_number": room_number,
            "floor": floor
        })

with open("data/beds.json", "w", encoding="utf-8") as f:
    json.dump(bed_data, f, indent=2, ensure_ascii=False)

print("beds.json has been created successfully.")
