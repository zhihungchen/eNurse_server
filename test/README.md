# eNurse Server Test Scripts

This folder contains API test scripts for the eNurse server.

## Structure

```
test/
├── api/         # Scripts (e.g. delete_all_beds.py)
├── config/      # Config settings (e.g. BASE_URL, credentials)
├── utils/       # Shared helpers
├── data/        # Sample test data (optional)
```

## How to Run

1. Enter the test folder:

```bash
cd /media/hippo3233/temi_ws/test
```

2. Run a script:

```bash
python3 -m api.delete_all_beds
```

>  All imports should use:
> `from config import config`

>  Do not use relative imports like:
> `from ..config import config`
