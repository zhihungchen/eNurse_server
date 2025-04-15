# eNurse Server System

eNurse is a practical robotics project that integrates Natural Language Processing (NLP) and Human-Robot Interaction (HRI) technology. The goal of this project is to build an intelligent nurse assistant capable of assisting healthcare professionals in hospitals by providing patient interaction, data management, and task automation.

This system enables seamless communication between **Temi robots**, **mobile applications**, and **hospital office desktops** through a secure **Docker-based server infrastructure**. By leveraging **NGINX reverse proxy, Apache server, MySQL database, and expandable Python applications**, eNurse ensures efficient and secure interaction within hospital environments.

---

## Clone the Repository

```bash
git clone https://github.com/XXX
cd temi_ws
```

---

## Configuration and Settings

To ensure secure and flexible deployment, this project requires a custom `config.json` file containing your own credentials and environment settings. This file should not be committed to GitHub.

Example `config.json`:
```json
{
  "MYSQL": {
    "ROOT_PASSWORD": "your_root_password",
    "DATABASE": "enurse",
    "USER": "enurse_user",
    "PASSWORD": "user_password"
  },
  "NGINX": {
    "HOST": "host.docker.internal",
    "IP": "192.168.1.85"
  },
  "database": {
    "host": "mysql",
    "username": "enurse_user",
    "password": "user_password",
    "dbname": "enurse"
  }
}
```

After preparing your `config.json`, run the following script to generate a `.env` file for Docker Compose:

```bash
python3 generate_env.py
```

---

## Build and Run Containers

```bash
sudo docker-compose build --no-cache
sudo docker-compose up -d
```

---

## Stopping the Containers

```bash
sudo docker-compose down
```

## Deployment Notes

- Ensure the `config.json` file is properly configured before running the system.
- Use scripts under `test` to test APIs


### Using `git_add_safe.sh`

The `git_add_safe.sh` script is a utility designed to help you safely stage changes to your Git repository. It ensures that sensitive files, such as `config.json`, `./db` or `./env`, are not accidentally added to version control.

#### Steps to Use:

1. Place the `git_add_safe.sh` script in the root directory of your repository.
2. Make the script executable:
  ```bash
  chmod +x git_add_safe.sh
  ```
3. Run the script to stage your changes:
  ```bash
  ./git_add_safe.sh
  ```
4. The script will automatically exclude sensitive files like `config.json`, `./db` or `./env` based on predefined rules.

This ensures that your repository remains secure and free from accidental commits of sensitive information.
