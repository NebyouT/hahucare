/**
 * Video Meeting Helper Functions
 * Handles opening video meetings in popup windows
 */

/**
 * Open video meeting in a popup window
 * @param {string} meetingUrl - The Google Meet or Zoom URL
 * @param {string} title - Window title
 */
function openVideoMeeting(meetingUrl, title = 'Video Meeting') {
    if (!meetingUrl) {
        alert('Meeting link not available');
        return;
    }

    // Calculate center position
    const width = 1200;
    const height = 800;
    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);

    // Window features
    const features = `
        width=${width},
        height=${height},
        left=${left},
        top=${top},
        toolbar=no,
        menubar=no,
        location=no,
        status=no,
        scrollbars=yes,
        resizable=yes
    `.replace(/\s+/g, '');

    // Open popup window
    const popup = window.open(meetingUrl, title, features);

    if (!popup) {
        // Popup blocked - fallback to new tab
        alert('Popup blocked! Opening in new tab instead.');
        window.open(meetingUrl, '_blank');
    } else {
        popup.focus();
    }
}

/**
 * Open video meeting in modal with iframe (for compatible platforms)
 * Note: Google Meet blocks iframe embedding, so this is mainly for Zoom
 * @param {string} meetingUrl - The meeting URL
 * @param {string} title - Modal title
 */
function openVideoMeetingModal(meetingUrl, title = 'Video Meeting') {
    if (!meetingUrl) {
        alert('Meeting link not available');
        return;
    }

    // Check if modal already exists
    let modal = document.getElementById('videoMeetingModal');
    
    if (!modal) {
        // Create modal
        const modalHTML = `
            <div class="modal fade" id="videoMeetingModal" tabindex="-1" aria-labelledby="videoMeetingModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="videoMeetingModalLabel">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <iframe id="videoMeetingIframe" src="" frameborder="0" width="100%" height="100%" allow="camera; microphone; fullscreen; display-capture"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="openInNewWindow()">Open in New Window</button>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        modal = document.getElementById('videoMeetingModal');
    }

    // Update iframe src and modal title
    const iframe = document.getElementById('videoMeetingIframe');
    const modalTitle = document.getElementById('videoMeetingModalLabel');
    
    iframe.src = meetingUrl;
    modalTitle.textContent = title;

    // Store URL for "Open in New Window" button
    window.currentMeetingUrl = meetingUrl;

    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    // Clear iframe when modal closes
    modal.addEventListener('hidden.bs.modal', function () {
        iframe.src = '';
    });
}

/**
 * Open current meeting in new window (from modal)
 */
function openInNewWindow() {
    if (window.currentMeetingUrl) {
        window.open(window.currentMeetingUrl, '_blank', 'width=1200,height=800');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('videoMeetingModal'));
        if (modal) {
            modal.hide();
        }
    }
}

/**
 * Check if video meeting is available for appointment
 * @param {number} appointmentId - Appointment ID
 * @returns {Promise<object>} Meeting details
 */
async function checkVideoMeetingAvailability(appointmentId) {
    try {
        const response = await fetch(`/api/appointment/${appointmentId}/video-link`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error checking video meeting availability:', error);
        return { available: false };
    }
}
