# P1ckl3d Web Shell

### An Advanced Webshell for Pentesting

## Overview

This advanced webshell is designed for pentesters to leverage during engagements where an arbitrary file upload vulnerability is discovered. It offers several advanced features that go beyond typical webshells, enabling comprehensive recon and interaction with the target server without needing to reload the page.

## Key Features

### 1. Dynamic Command Execution
- **Execute Shell Commands**: Run shell commands on the target server dynamically without reloading the page.
- **Command History**: Maintains a log of executed commands and their outputs for easy reference.

### 2. File Management
- **File Upload**: Upload files to the current working directory on the target server.
- **File Download**: List and download files from the current working directory, including hidden files.

### 3. Script Execution
- **Run Scripts**: Execute scripts in various languages (bash, python, perl) directly from the local filesystem without uploading them to the server.

### 4. Comprehensive Reconnaissance
- **Run Discovery**: Gather extensive information about the target server, including:
  - Hostname
  - OS Information
  - Kernel Version
  - PHP Version
  - Server Software
  - Current User
  - Detailed User Info
  - User Accounts
  - Sudo Privileges
  - Environment Variables
  - Network Configuration
  - Open Ports
  - Active Connections
  - Listening Services
  - SSH Connections
  - Firewall Status
  - SELinux Status
  - Running Processes
  - Installed Packages
  - Installed Services
  - Docker Containers
  - CPU Info
  - Memory Usage
  - Disk Usage
  - System Uptime
  - Scheduled Cron Jobs
  - Root Directory Listing
  - Recursive Directory Listing of /var/www
  - Recursive Directory Listing of Home
  - World-Writable Files
  - Core Dumps
  - Open Files
  - Last Logged Users

### 5. Reverse Shell Options
- **Standard Bash Shell**: Spawn a reverse shell using bash.
- **Netcat Shell**: Spawn a reverse shell using netcat.

### 6. Directory Location Maintenance
- **Persistent Directory**: The webshell maintains the current working directory across different commands and sessions, allowing for efficient navigation and interaction with the file system.

## Usage

### Uploading the Webshell
1. Upload the `webshell.php` file to the target server via an arbitrary file upload vulnerability.

### Interacting with the Webshell
1. Access the webshell via the URL where it was uploaded.
2. Use the various features via the intuitive interface:
   - **Execute Commands**: Enter commands in the provided input field and view the output dynamically.
   - **Upload Files**: Select and upload files using the upload option.
   - **Download Files**: List and download files from the target server.
   - **Run Scripts**: Select a script engine, choose a script from the local filesystem, and run it on the target server.
   - **Run Discovery**: Click the discovery button to gather detailed information about the target server.
   - **Spawn Reverse Shell**: Use the reverse shell options to open a reverse shell back to your machine.

### Security Considerations
- **Usage Restrictions**: Ensure the webshell is used only in environments where you have explicit permission to perform penetration testing.
- **Clean Up**: Remove the webshell from the target server after the engagement to avoid leaving backdoors.

## Conclusion

This advanced webshell provides pentesters with powerful capabilities to interact with target servers dynamically and gather comprehensive recon data, all within a single file. Its advanced features differentiate it from regular webshells, making it an invaluable tool for professional penetration testing engagements.

