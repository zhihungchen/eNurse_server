import json

CONFIG_FILE = "config.json"
ENV_FILE = ".env"

def generate_env():
    """Reads config.json and generates .env file"""
    try:
        with open(CONFIG_FILE, "r") as f:
            config = json.load(f)

        env_data = []
        
        # Convert MYSQL config
        mysql = config.get("MYSQL", {})
        env_data.append(f"MYSQL_ROOT_PASSWORD={mysql.get('ROOT_PASSWORD', '')}")
        env_data.append(f"MYSQL_DATABASE={mysql.get('DATABASE', '')}")
        env_data.append(f"MYSQL_USER={mysql.get('USER', '')}")
        env_data.append(f"MYSQL_PASSWORD={mysql.get('PASSWORD', '')}")

        # Convert NGINX config
        nginx = config.get("NGINX", {})
        env_data.append(f"NGINX_HOST={nginx.get('HOST', '')}")
        env_data.append(f"NGINX_IP={nginx.get('IP', '')}")

        # Write to .env
        with open(ENV_FILE, "w") as f:
            f.write("\n".join(env_data))

        print(f"Successfully generated {ENV_FILE} from {CONFIG_FILE}")

    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    generate_env()
