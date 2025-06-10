# eNurse Server System

## What is eNurse?
eNurse is a robotics project integrating Natural Language Processing (NLP) and Human-Robot Interaction (HRI) technologies. Its goal is to build an intelligent nurse assistant that supports healthcare professionals by enabling patient interaction, data management, and task automation through robots like Temi.

## Why we build this repo?
To enable various services in hospital environments using the Temi robot, a structured and scalable server architecture is essential. This repository provides an extensible foundation for deploying and managing those services.

---

## Clone the Repository

```bash
git clone https://github.com/XXX
cd temi_ws
```

---

## Configuration and Settings

To ensure secure and flexible deployment, this project requires a custom `.env` file containing your own credentials and environment settings. This file could be used for the host and the docker containers.

Example `.env`:

```bash
DB_HOST=your_host_name
DB_PORT=3306
DB_NAME=your_db_name
DB_USER=user
DB_PASS=pass
DB_CHARSET=utf8mb4
```

---

## Build and Run Containers

```bash
sudo docker-compose build --no-cache
sudo docker-compose up 
```

---

## Stopping the Containers

```bash
sudo docker-compose down
```

## Deployment Notes

- Ensure the `.env` file is properly configured before running the system.
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
