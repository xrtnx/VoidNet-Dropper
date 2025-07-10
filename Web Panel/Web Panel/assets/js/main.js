// --- AJAX Refresh for Clients Page ---
const refreshButton = document.getElementById('refresh-clients-btn');
const clientsTableBody = document.getElementById('clientsTableBody');
const spinner = document.getElementById('refresh-spinner');

if (refreshButton && clientsTableBody && spinner) {
    refreshButton.addEventListener('click', () => {
        spinner.style.display = 'block';
        refreshButton.disabled = true;

        fetch('../api/get_clients.php')
            .then(response => response.json())
            .then(clients => {
                // Clear the existing table
                clientsTableBody.innerHTML = '';

                if (clients.length === 0) {
                    clientsTableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No clients have checked in yet.</td></tr>';
                } else {
                    clients.forEach(client => {
                        const isDisconnected = client.is_disconnected;
                        const row = document.createElement('tr');
                        row.className = isDisconnected ? 'disconnected' : '';

                        const statusHTML = `<span class="status-indicator ${isDisconnected ? 'status-offline' : 'status-online'}"></span> ${isDisconnected ? 'Offline' : 'Online'}`;
                        const actionHTML = isDisconnected ? `<a href="clients.php?action=remove&id=${client.id}" class="remove-btn" onclick="return confirm('Are you sure?');">Remove</a>` : '';

                        row.innerHTML = `
                            <td>${statusHTML}</td>
                            <td>${escapeHTML(client.machine_name)}</td>
                            <td>${escapeHTML(client.last_ip)}</td>
                            <td>${escapeHTML(client.last_seen)}</td>
                            <td>${actionHTML}</td>
                        `;
                        clientsTableBody.appendChild(row);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching client list:', error);
                clientsTableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color: #d32f2f;">Failed to load client list.</td></tr>';
            })
            .finally(() => {
                spinner.style.display = 'none';
                refreshButton.disabled = false;
            });
    });
}

// Helper function to prevent XSS attacks when creating HTML from JS
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return str.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}