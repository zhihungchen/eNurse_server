import json

floors = {
    "5A": [
        "01-0", "02-0", "03-0", "05-0", "06-1", "06-2", "06-3", "06-5",
        "07-1", "07-2", "07-3", "07-5", "08-1", "08-2", "08-3", "08-5",
        "09-1", "09-2", "09-3", "09-5", "10-1", "10-2", "10-3", "10-5",
        "11-1", "11-2", "12-1", "12-2", "13-0", "15-0", "16-0", "17-0"
    ],
    "5B": [
        "01-0", "02-0", "03-0", "05-0", "06-1", "06-2", "06-3", "06-5",
        "07-1", "07-2", "07-3", "07-5", "08-1", "08-2", "08-3", "08-5",
        "09-1", "09-2", "09-3", "09-5", "10-0", "11-0", "12-1", "12-2",
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

with open("../data/beds.json", "w", encoding="utf-8") as f:
    json.dump(bed_data, f, indent=2, ensure_ascii=False)

print("beds.json has been created successfully.")
